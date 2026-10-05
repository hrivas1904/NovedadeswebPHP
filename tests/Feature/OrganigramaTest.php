<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Organigrama\Estructura;
use App\Services\Organigrama\Importador;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\OrganigramaFixture;
use Tests\TestCase;

class OrganigramaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge();
        OrganigramaFixture::crear();
        $this->actingAs(User::findOrFail(1));
    }

    private function importar(): array
    {
        return app(Importador::class)->importar(OrganigramaFixture::origen());
    }

    private function payload(string $id = 'cadete'): array
    {
        $d = app(Estructura::class)->datos();
        $n = collect($d['nodes'])->firstWhere('id', $id);

        return $n + ['revision' => $d['revision'], 'parents' => array_values(array_filter($d['edges'], fn ($e) => $e['childId'] === $id))];
    }

    public function test_import_preserves_all_positions_stable_ids_relations_and_is_idempotent(): void
    {
        $report = $this->importar();
        $before = app(Estructura::class)->datos();
        $again = $this->importar();
        $this->assertTrue($again['sin_cambios']);
        $this->assertSame($before, app(Estructura::class)->datos());
        $this->assertDatabaseCount('organigrama_posiciones', 65);
        $this->assertDatabaseCount('organigrama_dependencias', count(OrganigramaFixture::origen()['edges']));
        $this->assertDatabaseCount('organigrama_eventos', 1);
        foreach (['cadete', 'cadete-2', 'enfermero', 'enfermero-2'] as $id) {
            $this->assertDatabaseHas('organigrama_posiciones', ['id' => $id]);
        }
        $this->assertSame(2, DB::table('organigrama_dependencias')->where('child_id', 'secretaria-de-gerencia-y-direccion')->count());
        $this->assertSame(1, DB::table('organigrama_posiciones')->where('id', 'secretaria-de-gerencia-y-direccion')->count());
        $this->assertEqualsCanonicalizing(['direct', 'support', 'shared'], DB::table('organigrama_dependencias')->distinct()->pluck('tipo')->all());
        $this->assertSame(65, $report['total_posiciones']);
    }

    public function test_matching_is_conservative_reuses_existing_entities_and_reports_pending_names(): void
    {
        $report = $this->importar();
        $this->assertDatabaseHas('organigrama_responsables', ['posicion_id' => 'director-medico', 'legajo' => 100]);
        $this->assertDatabaseHas('organigrama_posiciones', ['id' => 'director-medico', 'rol_id' => 1]);
        $this->assertDatabaseHas('organigrama_posiciones', ['id' => 'cadete', 'rol_id' => null]);
        $this->assertContains('nombre_ambiguo', array_column($report['pendientes'], 'motivo'));
        $this->assertContains('Juan Soni', array_column($report['pendientes'], 'nombre'));
        $this->assertDatabaseCount('empleados', 4);
        $this->assertDatabaseCount('rol_empleados', 3);
        $this->assertDatabaseCount('areas', 1);
    }

    public function test_explicit_mapping_and_dry_run_do_not_create_fake_people(): void
    {
        $map = ['cadete-2' => ['rol_id' => 2, 'area_id' => 1, 'legajos' => [100]]];
        $report = app(Importador::class)->importar(OrganigramaFixture::origen(), $map, true);
        $this->assertTrue($report['simulacion']);
        $this->assertDatabaseCount('organigrama_posiciones', 0);
        app(Importador::class)->importar(OrganigramaFixture::origen(), $map);
        $this->assertDatabaseHas('organigrama_posiciones', ['id' => 'cadete-2', 'rol_id' => 2, 'area_id' => 1]);
        $this->assertDatabaseHas('organigrama_responsables', ['posicion_id' => 'cadete-2', 'legajo' => 100]);
    }

    public function test_read_and_write_permissions_are_separate_for_every_endpoint(): void
    {
        $this->importar();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('rrhh.organigrama.index'))->assertOk()->assertSee('Vista horizontal')->assertSee('Vista vertical')->assertSee('Vista árbol')->assertDontSee('id="tabConfig"', false)->assertDontSee('id="org-catalogos"', false);
        $this->getJson(route('rrhh.organigrama.datos'))->assertOk()->assertJsonCount(65, 'nodes');
        $this->postJson(route('rrhh.organigrama.store'), [])->assertForbidden();
        $this->putJson(route('rrhh.organigrama.update', 'cadete'), [])->assertForbidden();
        $this->deleteJson(route('rrhh.organigrama.destroy', 'cadete'), [])->assertForbidden();
        $this->actingAs(User::findOrFail(3));
        $this->getJson(route('rrhh.organigrama.datos'))->assertForbidden();
        $this->get(route('rrhh.organigrama.index'))->assertForbidden();
        $this->postJson(route('rrhh.organigrama.store'), [])->assertForbidden();
        auth()->forgetGuards();
        $this->getJson(route('rrhh.organigrama.datos'))->assertUnauthorized();
    }

    public function test_create_edit_multiple_dependencies_order_and_persistence_then_controlled_delete(): void
    {
        $this->importar();
        $payload = ['revision' => 1, 'title' => 'Nueva prueba', 'person' => 'Visible', 'rol_id' => 2, 'area_id' => 1, 'legajos' => [100], 'parents' => [
            ['parentId' => 'cadete', 'relation' => 'direct', 'order' => 7],
            ['parentId' => 'gerente-general', 'relation' => 'shared', 'order' => 20],
        ]];
        $response = $this->postJson(route('rrhh.organigrama.store'), $payload)->assertCreated()->assertJsonPath('data.revision', 2);
        $id = $response->json('selectedId');
        $this->deleteJson(route('rrhh.organigrama.destroy', 'cadete'), ['revision' => 2])->assertUnprocessable();
        $edit = $this->payload($id);
        $edit['title'] = 'Título actualizado';
        $edit['parents'] = [['parentId' => 'gerente-general', 'relation' => 'support', 'order' => 12]];
        $this->putJson(route('rrhh.organigrama.update', $id), $edit)->assertOk();
        $this->getJson(route('rrhh.organigrama.datos'))->assertOk()->assertJsonFragment(['title' => 'Título actualizado'])->assertJsonFragment(['childId' => $id, 'parentId' => 'gerente-general', 'relation' => 'support', 'order' => 12]);
        $this->get(route('rrhh.organigrama.index'))->assertOk()->assertViewHas('datos', fn ($d) => collect($d['nodes'])->firstWhere('id', $id)['title'] === 'Título actualizado');
        $this->assertDatabaseHas('organigrama_eventos', ['accion' => 'editar', 'actor_user_id' => 1]);
        $this->deleteJson(route('rrhh.organigrama.destroy', $id), ['revision' => 3])->assertOk();
        $this->assertDatabaseCount('organigrama_posiciones', 65);
        $this->assertDatabaseMissing('organigrama_responsables', ['posicion_id' => $id]);
    }

    public function test_cycles_self_links_duplicate_links_orphans_and_root_edits_are_rejected_atomically(): void
    {
        $this->importar();
        $before = app(Estructura::class)->datos();
        foreach ([
            ['director-medico', [['parentId' => 'enfermero', 'relation' => 'shared', 'order' => 0]]],
            ['cadete', [['parentId' => 'cadete', 'relation' => 'direct', 'order' => 0]]],
            ['cadete', [['parentId' => 'tesoreria', 'relation' => 'direct', 'order' => 0], ['parentId' => 'tesoreria', 'relation' => 'shared', 'order' => 1]]],
            ['cadete', []],
            ['directorio', [['parentId' => 'cadete', 'relation' => 'direct', 'order' => 0]]],
            ['cadete', [['parentId' => 'no-existe', 'relation' => 'direct', 'order' => 0]]],
        ] as [$id, $parents]) {
            $p = $this->payload($id);
            $p['parents'] = $parents;
            $p['title'] = 'No debe guardarse';
            $this->putJson(route('rrhh.organigrama.update', $id), $p)->assertUnprocessable();
            $this->assertSame($before, app(Estructura::class)->datos());
        }
        $this->deleteJson(route('rrhh.organigrama.destroy', 'directorio'), ['revision' => 1])->assertUnprocessable();
        $this->assertDatabaseCount('organigrama_eventos', 1);
    }

    public function test_stale_saves_and_deletes_do_not_overwrite_and_reimport_preserves_edits(): void
    {
        $this->importar();
        $p = $this->payload();
        $p['title'] = 'Modificado';
        $this->putJson(route('rrhh.organigrama.update', 'cadete'), $p)->assertOk();
        $p['title'] = 'Obsoleto';
        $this->putJson(route('rrhh.organigrama.update', 'cadete'), $p)->assertConflict();
        $this->deleteJson(route('rrhh.organigrama.destroy', 'cadete'), ['revision' => 1])->assertConflict();
        $this->importar();
        $this->assertDatabaseHas('organigrama_posiciones', ['id' => 'cadete', 'titulo' => 'Modificado']);
        $this->assertDatabaseCount('organigrama_eventos', 2);
    }

    public function test_invalid_types_missing_catalog_links_and_invalid_order_are_rejected(): void
    {
        $this->importar();
        foreach (['rol_id' => 999, 'area_id' => 999, 'legajos' => [103], 'title' => '   '] as $key => $value) {
            $p = $this->payload();
            $p[$key] = $value;
            $this->putJson(route('rrhh.organigrama.update', 'cadete'), $p)->assertUnprocessable();
        }
        foreach ([['relation' => 'invalid', 'order' => 0], ['relation' => 'direct', 'order' => -1]] as $edge) {
            $p = $this->payload();
            $p['parents'] = [['parentId' => 'tesoreria'] + $edge];
            $this->putJson(route('rrhh.organigrama.update', 'cadete'), $p)->assertUnprocessable();
        }
    }

    public function test_invalid_import_rolls_back_and_second_different_import_cannot_replace_data(): void
    {
        $data = OrganigramaFixture::origen();
        $data['edges'][] = $data['edges'][0];
        try {
            app(Importador::class)->importar($data);
            $this->fail('Una dependencia duplicada debe fallar.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('organigrama_posiciones', 0);
            $this->assertDatabaseCount('organigrama_eventos', 0);
        }
        $this->importar();
        $data = OrganigramaFixture::origen();
        $data['nodes'][0]['title'] = 'Otro archivo';
        $this->expectException(ValidationException::class);
        app(Importador::class)->importar($data);
    }

    public function test_command_exports_a_report_and_can_be_repeated(): void
    {
        $path = storage_path('framework/testing/organigrama-command-report.json');
        try {
            $this->artisan('organigrama:importar', ['--dry-run' => true, '--reporte' => $path])->assertSuccessful();
            $this->assertDatabaseCount('organigrama_posiciones', 0);
            $this->artisan('organigrama:importar', ['--reporte' => $path])->assertSuccessful();
            $this->artisan('organigrama:importar', ['--reporte' => $path])->assertSuccessful();
            $report = json_decode(file_get_contents($path), true);
            $this->assertTrue($report['sin_cambios']);
            $this->assertSame(65, $report['total_posiciones']);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
