<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Edd\ConfiguracionEdd;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EddConfigurationTest extends TestCase
{
    private User $admin;

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
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'RRHH de prueba', 'username' => 'edd-admin', 'rol' => 'Administrador/a', 'estado' => 'ACTIVO'],
            ['id' => 2, 'name' => 'Evaluador de prueba', 'username' => 'edd-evaluador', 'rol' => 'Coordinador/a', 'estado' => 'ACTIVO'],
            ['id' => 3, 'name' => 'RRHH inactivo', 'username' => 'edd-inactivo', 'rol' => 'Administrador/a', 'estado' => 'BAJA'],
        ]);
        (require database_path('migrations/2026_09_25_170000_create_edd_configuration_tables.php'))->up();
        $this->admin = User::findOrFail(1);
        $this->actingAs($this->admin);
    }

    private function datosPeriodo(string $codigo = 'EDD-2026'): array
    {
        return [
            'codigo' => $codigo, 'nombre' => 'EDD 2026 de prueba', 'anio' => 2026,
            'fecha_corte' => '2026-09-01', 'inicio_evaluacion' => '2026-09-10', 'fin_evaluacion' => '2026-10-10',
            'limite_devolucion' => '2026-11-01', 'autoevaluacion' => 'obligatoria',
            'inicio_autoevaluacion' => '2026-09-01', 'fin_autoevaluacion' => '2026-09-09',
            'visibilidad_autoevaluacion' => 'al_completar_evaluador',
            'condiciones_cierre' => 'Entrevista y acuerdos registrados.', 'excepciones' => 'Motivo documentado por RRHH.',
        ];
    }

    private function periodo(string $codigo = 'EDD-2026'): int
    {
        return $this->postJson(route('rrhh.edd.periodos.store'), $this->datosPeriodo($codigo))->assertCreated()->json('id');
    }

    private function instrumento(int $periodo, string $codigo = 'ADMIN'): int
    {
        return $this->postJson(route('rrhh.edd.instrumentos.store', $periodo), ['codigo' => $codigo, 'nombre' => 'Administración'])
            ->assertCreated()->json('id');
    }

    private function contenido(int $revision = 0): array
    {
        $bloques = [];
        foreach (array_keys(config('edd.bloques')) as $codigo) {
            $bloques[$codigo] = [
                'activo' => true, 'peso_porcentaje' => '20.00',
                'items' => [
                    ['titulo' => 'Criterio de '.$codigo, 'descripcion' => 'Comportamiento observable.', 'peso_relativo' => '1.00', 'obligatorio' => true],
                    ['titulo' => 'Segundo criterio de '.$codigo, 'descripcion' => null, 'peso_relativo' => '2.50', 'obligatorio' => false],
                ],
            ];
        }

        return ['nombre' => 'Instrumento completo', 'revision' => $revision, 'formulario_completo' => 1, 'bloques' => $bloques];
    }

    private function guardar(int $periodo, int $instrumento, ?array $datos = null)
    {
        return $this->patchJson(route('rrhh.edd.instrumentos.update', [$periodo, $instrumento]), $datos ?? $this->contenido());
    }

    public function test_period_is_persisted_reloaded_and_audited_with_server_owned_identity(): void
    {
        $datos = array_merge($this->datosPeriodo('edd-2026'), ['estado' => 'habilitado', 'created_by' => 2, 'updated_by' => 2]);
        $respuesta = $this->postJson(route('rrhh.edd.periodos.store'), $datos)->assertCreated()->assertJsonPath('revision', 0);
        $id = $respuesta->json('id');
        $this->assertDatabaseHas('edd_periodos', ['id' => $id, 'codigo' => 'EDD-2026', 'estado' => 'borrador', 'created_by' => 1, 'updated_by' => 1]);
        $periodo = app(ConfiguracionEdd::class)->periodo($id);
        $this->assertSame('al_completar_evaluador', $periodo['reglas']['visibilidad_autoevaluacion']);
        $this->get($respuesta->json('redirect'))->assertOk()->assertSee('EDD 2026 de prueba')->assertSee('Entrevista y acuerdos registrados.');
        $this->assertDatabaseHas('edd_eventos', ['periodo_id' => $id, 'accion' => 'crear_periodo', 'actor_user_id' => 1]);
        $datos['revision'] = 0;
        $datos['nombre'] = 'Nombre actualizado';
        $this->patchJson(route('rrhh.edd.periodos.update', $id), $datos)->assertOk()->assertJsonPath('revision', 1);
        $this->assertDatabaseHas('edd_periodos', ['id' => $id, 'nombre' => 'Nombre actualizado']);
        $this->assertDatabaseCount('edd_eventos', 2);
    }

    public function test_incomplete_draft_can_be_saved_but_invalid_dates_and_duplicate_codes_cannot(): void
    {
        $this->postJson(route('rrhh.edd.periodos.store'), ['codigo' => 'PARCIAL', 'nombre' => 'Período parcial', 'anio' => 2026])->assertCreated();
        $id = $this->periodo();
        $this->postJson(route('rrhh.edd.periodos.store'), $this->datosPeriodo('edd-2026'))->assertUnprocessable()->assertJsonValidationErrors('codigo');
        $datos = array_merge($this->datosPeriodo('INVALIDO'), ['fin_evaluacion' => '2026-08-01']);
        $this->postJson(route('rrhh.edd.periodos.store'), $datos)->assertUnprocessable()->assertJsonValidationErrors('fin_evaluacion');
        $datos = array_merge($this->datosPeriodo('INVALIDO'), ['autoevaluacion' => 'deshabilitada']);
        $this->postJson(route('rrhh.edd.periodos.store'), $datos)->assertUnprocessable()->assertJsonValidationErrors('autoevaluacion');
        $this->assertDatabaseCount('edd_periodos', 2);
        $this->assertSame('borrador', app(ConfiguracionEdd::class)->periodo($id)['estado']);
    }

    public function test_stale_period_save_is_rejected_without_overwriting_or_auditing_it(): void
    {
        $id = $this->periodo();
        $datos = array_merge($this->datosPeriodo(), ['revision' => 0, 'nombre' => 'Primera sesión']);
        $this->patchJson(route('rrhh.edd.periodos.update', $id), $datos)->assertOk();
        $datos['nombre'] = 'Segunda sesión';
        $this->patchJson(route('rrhh.edd.periodos.update', $id), $datos)->assertConflict();
        $this->assertDatabaseHas('edd_periodos', ['id' => $id, 'nombre' => 'Primera sesión', 'revision' => 1]);
        $this->assertDatabaseCount('edd_eventos', 2);
    }

    public function test_instrument_criteria_weights_and_scale_survive_reload(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $this->assertDatabaseCount('edd_items', 0);
        $this->guardar($periodo, $id)->assertOk()->assertJsonPath('revision', 1);
        $guardado = app(ConfiguracionEdd::class)->instrumento($periodo, $id);
        $this->assertSame([1, 2, 3, 4], array_keys($guardado['escala']));
        $this->assertCount(5, $guardado['bloques']);
        $this->assertDatabaseCount('edd_items', 10);
        $this->assertSame('Segundo criterio de generales', $guardado['bloques']['generales']['items'][1]['titulo']);
        $this->get(route('rrhh.edd.configuracion.instrumento', ['periodo' => $periodo, 'instrumento' => $id]))
            ->assertOk()->assertSee('Segundo criterio de generales')->assertSee('Publicar versión 1');
    }

    public function test_publication_requires_criteria_valid_weights_and_explicit_confirmation(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $url = route('rrhh.edd.instrumentos.publicar', [$periodo, $id]);
        $this->postJson($url, ['revision' => 0, 'confirmar' => true])->assertUnprocessable()->assertJsonValidationErrors('bloques');
        $datos = $this->contenido();
        $datos['bloques']['generales']['peso_porcentaje'] = 19;
        $this->guardar($periodo, $id, $datos)->assertOk();
        $this->postJson($url, ['revision' => 1, 'confirmar' => true])->assertUnprocessable()->assertJsonValidationErrors('bloques');
        $this->guardar($periodo, $id, $this->contenido(1))->assertOk();
        $this->postJson($url, ['revision' => 2])->assertUnprocessable()->assertJsonValidationErrors('confirmar');
        $this->postJson($url, ['revision' => 2, 'confirmar' => true])->assertOk()->assertJsonPath('estado', 'publicado');
        $this->assertDatabaseHas('edd_periodos', ['id' => $periodo, 'estado' => 'borrador']);
        $this->assertDatabaseHas('edd_instrumentos', ['id' => $id, 'estado' => 'publicado', 'publicado_by' => 1, 'revision' => 3]);
        $this->postJson($url, ['revision' => 2, 'confirmar' => true])->assertConflict();
        $this->assertSame(1, DB::table('edd_eventos')->where('accion', 'publicar_instrumento')->count());
    }

    public function test_decimal_weights_are_validated_exactly_and_inactive_blocks_do_not_contribute(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $datos = $this->contenido();
        $pesos = ['generales' => '33.33', 'especificas' => '33.33', 'desempeno' => '33.34'];
        foreach ($datos['bloques'] as $codigo => &$bloque) {
            $bloque['activo'] = isset($pesos[$codigo]);
            $bloque['peso_porcentaje'] = $pesos[$codigo] ?? 99;
        }
        unset($bloque);
        $this->guardar($periodo, $id, $datos)->assertOk();
        $this->postJson(route('rrhh.edd.instrumentos.publicar', [$periodo, $id]), ['revision' => 1, 'confirmar' => true])->assertOk();
        $this->assertDatabaseCount('edd_items', 10);
    }

    public function test_published_instrument_is_immutable_and_new_version_preserves_original(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $this->guardar($periodo, $id)->assertOk();
        $this->postJson(route('rrhh.edd.instrumentos.publicar', [$periodo, $id]), ['revision' => 1, 'confirmar' => true])->assertOk();
        $original = app(ConfiguracionEdd::class)->instrumento($periodo, $id);
        $this->guardar($periodo, $id, $this->contenido(2))->assertConflict();
        $versionUrl = route('rrhh.edd.instrumentos.versiones', [$periodo, $id]);
        $nuevo = $this->postJson($versionUrl, ['revision' => 2])->assertCreated()->json('id');
        $copia = app(ConfiguracionEdd::class)->instrumento($periodo, $nuevo);
        $this->assertSame(2, $copia['version']);
        $this->assertSame('borrador', $copia['estado']);
        $this->assertSame($original['escala'], $copia['escala']);
        $this->assertNotSame($original['bloques']['generales']['items'][0]['id'], $copia['bloques']['generales']['items'][0]['id']);
        $this->guardar($periodo, $nuevo)->assertOk();
        $this->assertSame($original, app(ConfiguracionEdd::class)->instrumento($periodo, $id));
        $this->postJson($versionUrl, ['revision' => 2])->assertUnprocessable();
        $this->assertDatabaseCount('edd_instrumentos', 2);
    }

    public function test_cross_period_and_foreign_criteria_cannot_be_written(): void
    {
        $periodo = $this->periodo();
        $otroPeriodo = $this->periodo('EDD-OTRO');
        $id = $this->instrumento($periodo);
        $otro = $this->instrumento($otroPeriodo, 'OTRO');
        $this->guardar($periodo, $id)->assertOk();
        $this->guardar($otroPeriodo, $otro)->assertOk();
        $this->guardar($otroPeriodo, $id, $this->contenido(1))->assertNotFound();
        $this->getJson(route('rrhh.edd.configuracion.instrumento', ['periodo' => $otroPeriodo, 'instrumento' => $id]))->assertNotFound();
        $antes = app(ConfiguracionEdd::class)->instrumento($periodo, $id);
        $ajeno = app(ConfiguracionEdd::class)->instrumento($otroPeriodo, $otro)['bloques']['generales']['items'][0]['id'];
        $datos = $this->contenido(1);
        $datos['bloques']['generales']['items'][0]['id'] = $ajeno;
        $this->guardar($periodo, $id, $datos)->assertUnprocessable();
        $this->assertSame($antes, app(ConfiguracionEdd::class)->instrumento($periodo, $id));
    }

    public function test_removed_draft_criteria_are_deleted_and_stale_edits_do_not_restore_them(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $this->guardar($periodo, $id)->assertOk();
        $actual = app(ConfiguracionEdd::class)->instrumento($periodo, $id);
        $datos = $this->contenido(1);
        foreach ($datos['bloques'] as $codigo => &$bloque) {
            $bloque['items'] = [array_intersect_key($actual['bloques'][$codigo]['items'][0], array_flip(['id', 'titulo', 'descripcion', 'competencia_codigo', 'peso_relativo', 'obligatorio']))];
        }
        unset($bloque);
        $this->guardar($periodo, $id, $datos)->assertOk();
        $this->assertDatabaseCount('edd_items', 5);
        $this->guardar($periodo, $id, $datos)->assertConflict();
        $this->assertDatabaseCount('edd_items', 5);
    }

    public function test_truncated_or_malformed_forms_do_not_replace_the_saved_instrument(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $datos = $this->contenido();
        unset($datos['formulario_completo']);
        $this->guardar($periodo, $id, $datos)->assertUnprocessable()->assertJsonValidationErrors('formulario_completo');
        $datos = $this->contenido();
        $datos['bloques']['generales']['peso_porcentaje'] = '20.123';
        $this->guardar($periodo, $id, $datos)->assertUnprocessable();
        $datos = $this->contenido();
        unset($datos['bloques']['objetivos']);
        $this->guardar($periodo, $id, $datos)->assertUnprocessable();
        $this->assertDatabaseCount('edd_items', 0);
        $this->assertDatabaseHas('edd_instrumentos', ['id' => $id, 'revision' => 0]);
    }

    public function test_every_write_requires_active_rrhh_and_period_must_remain_a_draft(): void
    {
        $periodo = $this->periodo();
        $id = $this->instrumento($periodo);
        $writes = [
            ['postJson', route('rrhh.edd.periodos.store')],
            ['patchJson', route('rrhh.edd.periodos.update', $periodo)],
            ['postJson', route('rrhh.edd.instrumentos.store', $periodo)],
            ['patchJson', route('rrhh.edd.instrumentos.update', [$periodo, $id])],
            ['postJson', route('rrhh.edd.instrumentos.publicar', [$periodo, $id])],
            ['postJson', route('rrhh.edd.instrumentos.versiones', [$periodo, $id])],
        ];
        foreach ([2, 3] as $user) {
            $this->actingAs(User::findOrFail($user));
            foreach ($writes as [$method, $url]) {
                $this->$method($url, [])->assertForbidden();
            }
        }
        auth()->logout();
        foreach ($writes as [$method, $url]) {
            $this->$method($url, [])->assertUnauthorized();
        }
        $this->actingAs($this->admin);
        DB::table('edd_periodos')->where('id', $periodo)->update(['estado' => 'habilitado']);
        $this->guardar($periodo, $id)->assertConflict();
        $this->patchJson(route('rrhh.edd.periodos.update', $periodo), array_merge($this->datosPeriodo(), ['revision' => 0]))->assertConflict();
        $this->assertDatabaseCount('edd_eventos', 2);
    }

    public function test_audit_failure_rolls_back_the_entire_operation(): void
    {
        $fail = true;
        DB::connection()->beforeExecuting(function ($sql) use (&$fail): void {
            if ($fail && str_contains($sql, 'insert into "edd_eventos"')) {
                $fail = false;
                throw new \RuntimeException('Fallo de auditoría simulado.');
            }
        });
        $this->postJson(route('rrhh.edd.periodos.store'), $this->datosPeriodo())->assertStatus(500);
        $this->assertDatabaseCount('edd_periodos', 0);
        $this->assertDatabaseCount('edd_eventos', 0);
    }

    public function test_migration_rollback_only_removes_new_edd_tables(): void
    {
        (require database_path('migrations/2026_09_25_170000_create_edd_configuration_tables.php'))->down();
        $this->assertFalse(Schema::hasTable('edd_periodos'));
        $this->assertFalse(Schema::hasTable('edd_eventos'));
        $this->assertDatabaseCount('users', 3);
    }
}
