<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Edd\PoblacionEdd;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EddPopulationTest extends TestCase
{
    private int $periodo;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('rol');
            $table->string('estado');
            $table->integer('legajo')->nullable();
        });
        Schema::create('areas', function (Blueprint $table) {
            $table->integer('ID_AREA')->primary();
            $table->string('NOMBRE');
        });
        Schema::create('empleados', function (Blueprint $table) {
            $table->integer('LEGAJO');
            $table->string('COLABORADOR');
            $table->string('ESTADO');
            $table->integer('ID_AREA');
            $table->integer('ID_SERVICIOS')->nullable();
        });
        DB::table('areas')->insert([['ID_AREA' => 10, 'NOMBRE' => 'Administración QA'], ['ID_AREA' => 20, 'NOMBRE' => 'Recepción QA']]);
        foreach ([1 => [900, 'Administrador/a', 'ACTIVO'], 2 => [200, 'Colaborador/a', 'ACTIVO'],
            3 => [101, 'Colaborador/a', 'ACTIVO'], 4 => [201, 'Coordinador/a', 'BAJA'],
            5 => [202, 'Coordinador/a', 'ACTIVO'], 6 => [103, 'Colaborador/a', 'ACTIVO'], 7 => [103, 'Colaborador/a', 'ACTIVO']] as $id => [$legajo, $rol, $estado]) {
            DB::table('users')->insert(compact('id', 'legajo', 'rol', 'estado') + ['name' => 'Cuenta QA '.$id, 'username' => 'qa-'.$id]);
        }
        foreach ([101 => ['ACTIVO', 10], 102 => ['ACTIVO', 10], 103 => ['ACTIVO', 20], 201 => ['BAJA', 20]] as $legajo => [$estado, $area]) {
            DB::table('empleados')->insert(['LEGAJO' => $legajo, 'COLABORADOR' => 'Persona QA '.$legajo, 'ESTADO' => $estado, 'ID_AREA' => $area, 'ID_SERVICIOS' => null]);
        }
        (require database_path('migrations/2026_09_25_170000_create_edd_configuration_tables.php'))->up();
        (require database_path('migrations/2026_09_26_100000_create_edd_population_tables.php'))->up();
        $this->actingAs(User::findOrFail(1));
        $this->periodo = $this->postJson(route('rrhh.edd.periodos.store'), ['codigo' => 'QA', 'nombre' => 'Período QA', 'anio' => 2026])->assertCreated()->json('id');
    }

    private function agregar(array $legajos = [101, 102]): array
    {
        $this->postJson(route('rrhh.edd.poblacion.store', $this->periodo), compact('legajos'))->assertOk();

        return DB::table('edd_participantes')->where('periodo_id', $this->periodo)->orderBy('legajo')->pluck('id')->all();
    }

    private function guardarArea(string $texto = 'Trabajo técnico: Descripción completa.', int $revision = 0, int $area = 10)
    {
        return $this->patchJson(route('rrhh.edd.competencias.update', [$this->periodo, $area]), ['revision' => $revision, 'competencias' => $texto]);
    }

    private function datosPersona(int $revision = 0): array
    {
        return ['revision' => $revision, 'area_id' => 10, 'incluido' => 1, 'modo_competencias' => 'area', 'funcion' => 'coordinador', 'evaluador_user_id' => 2];
    }

    private function guardarPersona(int $id, array $datos)
    {
        return $this->patchJson(route('rrhh.edd.participantes.update', [$this->periodo, $id]), $datos);
    }

    private function asignar(array $participantes, int $evaluador = 2)
    {
        return $this->postJson(route('rrhh.edd.evaluadores.update', $this->periodo), ['participantes' => $participantes, 'evaluador_user_id' => $evaluador, 'funcion' => 'jefe']);
    }

    public function test_population_selection_is_persisted_idempotent_and_does_not_modify_the_payroll(): void
    {
        $nomina = DB::table('empleados')->get()->toJson();
        $ids = $this->agregar();
        $this->agregar();
        $this->assertDatabaseCount('edd_participantes', 2);
        $this->assertSame($nomina, DB::table('empleados')->get()->toJson());
        $this->assertDatabaseHas('edd_participantes', ['id' => $ids[0], 'legajo' => 101, 'area_id' => 10, 'created_by' => 1]);
        $this->assertSame(2, DB::table('edd_eventos')->where('accion', 'incluir_participante')->count());
        foreach (['poblacion', 'evaluadores', 'competencias'] as $pagina) {
            $this->get(route('rrhh.edd.configuracion.'.$pagina, ['periodo' => $this->periodo]))->assertOk();
        }
        $this->get(route('rrhh.edd.configuracion.participante', [$this->periodo, $ids[0]]))->assertOk()->assertSee('Persona QA 101')->assertSee('Cuenta vinculada');
        $this->get(route('rrhh.edd.configuracion.participante', [$this->periodo, $ids[1]]))->assertOk()->assertSee('Sin cuenta activa');
    }

    public function test_invalid_or_inactive_or_ambiguous_legajos_reject_the_whole_selection(): void
    {
        foreach ([[101, 201], [101, 999]] as $legajos) {
            $this->postJson(route('rrhh.edd.poblacion.store', $this->periodo), compact('legajos'))->assertUnprocessable();
            $this->assertDatabaseCount('edd_participantes', 0);
        }
        DB::table('empleados')->insert(['LEGAJO' => 101, 'COLABORADOR' => 'Duplicado QA', 'ESTADO' => 'ACTIVO', 'ID_AREA' => 10]);
        $this->postJson(route('rrhh.edd.poblacion.store', $this->periodo), ['legajos' => [101]])->assertUnprocessable();
        $this->assertDatabaseCount('edd_participantes', 0);
    }

    public function test_area_inheritance_and_individual_override_remain_independent(): void
    {
        [$a, $b] = $this->agregar();
        $this->guardarArea()->assertOk();
        $service = app(PoblacionEdd::class);
        $this->assertSame('Trabajo técnico', $service->competenciasEfectivas($service->participante($this->periodo, $a))[0]['titulo']);
        $datos = $this->datosPersona();
        $datos['modo_competencias'] = 'personal';
        $datos['competencias'] = "Gestión de pagos: Valida comprobantes.\nConciliación";
        $this->guardarPersona($a, $datos)->assertOk();
        $this->guardarArea('Nueva base de área', 1)->assertOk();
        $this->assertSame('Gestión de pagos', $service->competenciasEfectivas($service->participante($this->periodo, $a))[0]['titulo']);
        $this->assertSame('Nueva base de área', $service->competenciasEfectivas($service->participante($this->periodo, $b))[0]['titulo']);
        $this->guardarPersona($a, $this->datosPersona(1))->assertOk();
        $this->assertSame('Nueva base de área', $service->competenciasEfectivas($service->participante($this->periodo, $a))[0]['titulo']);
        $this->assertDatabaseHas('edd_participantes', ['id' => $a, 'competencias_json' => null]);
    }

    public function test_all_eight_supplied_catalogues_keep_their_five_titles_and_full_descriptions(): void
    {
        $revision = 0;
        foreach (config('edd_competencias') as $catalogo) {
            $this->guardarArea(implode("\n", $catalogo['competencias']), $revision++)->assertOk();
            $base = app(PoblacionEdd::class)->competenciasArea($this->periodo, 10);
            $this->assertCount(5, $base['items']);
            $this->assertSame(implode("\n", $catalogo['competencias']), app(PoblacionEdd::class)->textoCompetencias($base['items']));
        }
        $this->assertSame(8, $revision);
    }

    public function test_invalid_competencies_and_stale_area_revision_do_not_overwrite(): void
    {
        $this->guardarArea()->assertOk();
        $this->guardarArea('Cambio obsoleto', 0)->assertConflict();
        foreach (["Duplicada\nDuplicada", str_repeat('a', 201), ': Sin título'] as $texto) {
            $this->guardarArea($texto, 1)->assertUnprocessable()->assertJsonValidationErrors('competencias');
        }
        $base = app(PoblacionEdd::class)->competenciasArea($this->periodo, 10);
        $this->assertSame(1, $base['revision']);
        $this->assertSame('Trabajo técnico', $base['items'][0]['titulo']);
    }

    public function test_bulk_assignment_is_atomic_and_reassignment_preserves_history(): void
    {
        [$a, $b] = $this->agregar();
        $this->asignar([$a => 0, $b => 0])->assertOk();
        $this->asignar([$a => 1, $b => 0], 5)->assertConflict();
        $this->assertSame(2, DB::table('edd_asignaciones')->where('current_slot', 1)->where('evaluador_user_id', 2)->count());
        $this->assertDatabaseCount('edd_asignaciones', 2);
        $this->asignar([$a => 1, $b => 1], 5)->assertOk();
        $this->assertSame(2, DB::table('edd_asignaciones')->whereNull('current_slot')->whereNotNull('vigente_hasta')->count());
        $this->assertSame(2, DB::table('edd_asignaciones')->where('current_slot', 1)->where('evaluador_user_id', 5)->count());
        $this->assertDatabaseHas('edd_participantes', ['id' => $a, 'revision' => 2]);
    }

    public function test_self_assignment_inactive_account_and_cross_period_resources_are_rejected(): void
    {
        [$a] = $this->agregar([101]);
        foreach ([3, 4, 999] as $evaluador) {
            $this->asignar([$a => 0], $evaluador)->assertUnprocessable();
        }
        $otro = $this->postJson(route('rrhh.edd.periodos.store'), ['codigo' => 'OTRO', 'nombre' => 'Otro período', 'anio' => 2026])->assertCreated()->json('id');
        $this->get(route('rrhh.edd.configuracion.participante', [$otro, $a]))->assertNotFound();
        $this->patchJson(route('rrhh.edd.participantes.update', [$otro, $a]), $this->datosPersona())->assertNotFound();
        $this->postJson(route('rrhh.edd.evaluadores.update', $otro), ['participantes' => [$a => 0], 'evaluador_user_id' => 2, 'funcion' => 'jefe'])->assertNotFound();
        $this->assertDatabaseCount('edd_asignaciones', 0);
    }

    public function test_exclusion_closes_assignment_without_removing_person_and_retry_does_not_reinclude(): void
    {
        [$a] = $this->agregar([101]);
        $this->asignar([$a => 0])->assertOk();
        $datos = $this->datosPersona(1);
        $datos['incluido'] = 0;
        $this->guardarPersona($a, $datos)->assertUnprocessable();
        $datos['motivo_exclusion'] = 'Exclusión de prueba';
        $this->guardarPersona($a, $datos)->assertOk();
        $this->agregar([101]);
        $this->assertDatabaseHas('edd_participantes', ['id' => $a, 'incluido' => 0, 'motivo_exclusion' => 'Exclusión de prueba']);
        $this->assertSame(0, DB::table('edd_asignaciones')->where('current_slot', 1)->count());
        $this->asignar([$a => 2])->assertUnprocessable();
        $this->guardarPersona($a, $this->datosPersona(2))->assertOk();
        $this->assertDatabaseHas('edd_participantes', ['id' => $a, 'incluido' => 1, 'motivo_exclusion' => null]);
    }

    public function test_queries_filter_areas_and_show_ambiguous_accounts_without_duplicating_people(): void
    {
        $ids = $this->agregar([101, 102, 103]);
        $service = app(PoblacionEdd::class);
        $this->assertSame(2, $service->participantes($this->periodo, ['area' => 10])->total());
        $this->assertSame(0, $service->candidatos($this->periodo, [])->total());
        $this->get(route('rrhh.edd.configuracion.participante', [$this->periodo, $ids[2]]))->assertOk()->assertSee('Más de una cuenta activa');
        $this->assertSame(3, $service->participantes($this->periodo)->total());
    }

    public function test_all_writes_require_active_rrhh_and_draft_period(): void
    {
        [$a] = $this->agregar([101]);
        $writes = [
            ['postJson', route('rrhh.edd.poblacion.store', $this->periodo), ['legajos' => [102]]],
            ['patchJson', route('rrhh.edd.competencias.update', [$this->periodo, 10]), ['revision' => 0, 'competencias' => 'Competencia']],
            ['patchJson', route('rrhh.edd.participantes.update', [$this->periodo, $a]), $this->datosPersona()],
            ['postJson', route('rrhh.edd.evaluadores.update', $this->periodo), ['participantes' => [$a => 0], 'evaluador_user_id' => 2, 'funcion' => 'jefe']],
        ];
        foreach ([2, 4] as $user) {
            $this->actingAs(User::findOrFail($user));
            foreach ($writes as [$method, $url, $datos]) {
                $this->$method($url, $datos)->assertForbidden();
            }
            $this->get(route('rrhh.edd.configuracion.participante', [$this->periodo, $a]))->assertForbidden();
        }
        auth()->logout();
        foreach ($writes as [$method, $url, $datos]) {
            $this->$method($url, $datos)->assertUnauthorized();
        }
        $this->actingAs(User::findOrFail(1));
        DB::table('edd_periodos')->where('id', $this->periodo)->update(['estado' => 'habilitado']);
        foreach ($writes as [$method, $url, $datos]) {
            $this->$method($url, $datos)->assertConflict();
        }
    }

    public function test_individual_stale_write_and_audit_failure_leave_everything_unchanged(): void
    {
        [$a] = $this->agregar([101]);
        $this->guardarPersona($a, $this->datosPersona())->assertOk();
        $this->guardarPersona($a, $this->datosPersona())->assertConflict();
        $fail = true;
        DB::connection()->beforeExecuting(function ($sql) use (&$fail): void {
            if ($fail && str_contains($sql, 'insert into "edd_eventos"')) {
                $fail = false;
                throw new \RuntimeException('Auditoría no disponible en la prueba.');
            }
        });
        $this->asignar([$a => 1], 5)->assertStatus(500);
        $this->assertDatabaseCount('edd_asignaciones', 1);
        $this->assertDatabaseHas('edd_asignaciones', ['participante_id' => $a, 'evaluador_user_id' => 2, 'current_slot' => 1]);
        $this->assertDatabaseHas('edd_participantes', ['id' => $a, 'revision' => 1]);
    }

    public function test_population_migration_rollback_preserves_existing_configuration_and_payroll(): void
    {
        (require database_path('migrations/2026_09_26_100000_create_edd_population_tables.php'))->down();
        $this->assertFalse(Schema::hasTable('edd_participantes'));
        $this->assertDatabaseCount('edd_periodos', 1);
        $this->assertDatabaseCount('empleados', 4);
    }

    public function test_assigned_responsible_sees_only_their_team_and_loses_access_on_reassignment(): void
    {
        [$a, $b] = $this->agregar();
        $this->asignar([$a => 0], 2)->assertOk();
        $this->asignar([$b => 0], 5)->assertOk();
        $this->actingAs(User::findOrFail(2)); // Perfil Colaborador/a: acceso por asignación.
        $this->get(route('rrhh.edd.index'))->assertRedirect(route('rrhh.edd.equipo'));
        $this->get(route('rrhh.edd.equipo'))->assertOk()->assertSee('Persona QA 101')->assertDontSee('Persona QA 102');
        $this->get(route('rrhh.edd.configuracion.poblacion'))->assertForbidden();
        $this->actingAs(User::findOrFail(1));
        $this->asignar([$a => 1], 5)->assertOk();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('rrhh.edd.equipo'))->assertForbidden();
        $this->get(route('rrhh.edd.index'))->assertRedirect(route('rrhh.edd.autoevaluacion'));
        $this->actingAs(User::findOrFail(5));
        $this->get(route('rrhh.edd.equipo'))->assertOk()->assertSee('Persona QA 101')->assertSee('Persona QA 102');
    }
}
