<?php

namespace Tests\Feature;

use App\Services\Biblioteca\Assignments;
use App\Services\Biblioteca\BaselineImport;
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaBaselineImportTest extends TestCase
{
    private string $fixtureRoot;

    private const TABLES = ['bib_documents', 'bib_versions', 'bib_sources', 'bib_findings', 'bib_events', 'bib_requests', 'bib_uploads', 'bib_visibility', 'bib_policy_approvals', 'bib_category_documents', 'bib_employee_documents', 'bib_acceptances', 'bib_baseline_batches'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureRoot = storage_path('framework/testing/biblioteca-baseline-'.Str::uuid());
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array', 'biblioteca.storage' => $this->fixtureRoot.'/storage']);
        DB::purge();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        foreach (['2026_09_18_150000_create_biblioteca_tables.php', '2026_09_20_120000_add_biblioteca_assignments_and_acceptances.php', '2026_09_21_010000_add_biblioteca_policy_governance.php', '2026_10_29_180000_add_biblioteca_baseline_batches.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        File::ensureDirectoryExists($this->fixtureRoot.'/source');
    }

    protected function tearDown(): void
    {
        if (isset($this->fixtureRoot)) {
            $root = realpath($this->fixtureRoot);
            $parent = realpath(storage_path('framework/testing'));
            if ($root && $parent && str_starts_with($root, $parent.DIRECTORY_SEPARATOR.'biblioteca-baseline-')) {
                File::deleteDirectory($root);
            }
        }
        parent::tearDown();
    }

    private function job(string $name): array
    {
        $c = Content::institutional(Content::emptyJob());
        $c['name'] = $name;
        $c['area'] = 'Calidad';
        $c['groups']['purpose'] = [Content::paragraph('Acompañar a las personas.')];
        $c['groups']['tasks'] = [Content::paragraph('Revisar la documentación.', true)];
        return $c;
    }

    private function plan(): array
    {
        $items = [];
        foreach ([1, 2] as $id) {
            $name = sprintf('%02d - Puesto de prueba.docx', $id);
            $path = $this->fixtureRoot.'/source/'.$name;
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
            $zip->addFromString('word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Puesto '.$id.'</w:t></w:r></w:p></w:body></w:document>');
            $zip->close();
            $content = $this->job('Puesto base '.$id);
            $parsed = Content::toParsed($content);
            $parsed['warnings'] = $id === 1 ? [['code' => 'fixture_review', 'title' => 'Revisar fuente', 'detail' => 'Hallazgo ficticio conservado.']] : [];
            $items[] = ['source_id' => $id, 'relative' => $name, 'sha256' => hash_file('sha256', $path), 'parsed' => $parsed, 'content' => $content];
        }
        $attachment = 'matriz.xlsx';
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($this->fixtureRoot.'/source/'.$attachment, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets/></workbook>');
        $zip->close();
        $attachments = [['relative' => $attachment, 'sha256' => hash_file('sha256', $this->fixtureRoot.'/source/'.$attachment)]];
        return ['id' => hash('sha256', Content::json([$items, $attachments])), 'root' => $this->fixtureRoot.'/source', 'items' => $items, 'attachments' => $attachments];
    }

    private function priorState(): array
    {
        $library = new Library;
        $v = $library->create('descriptivos', $this->job('Puesto anterior'), 'fixture', (string) Str::uuid());
        $published = $library->publish($v['id'], 0, 'Versión anterior aprobada', true, 'fixture');
        $draft = $library->draft($v['id'], 'fixture');
        foreach (['bib_category_documents' => ['category_id' => 7], 'bib_employee_documents' => ['legajo' => 123]] as $table => $identity) {
            DB::table($table)->insert($identity + ['document_id' => $v['document_id'], 'revision' => 3, 'updated_at' => Library::now(), 'updated_by' => 'fixture']);
        }
        $snapshot = Content::json(Assignments::snapshot($library->entry($v['document_id'], $v['id'])));
        DB::table('bib_acceptances')->insert(['id' => (string) Str::uuid(), 'user_id' => 321, 'legajo' => 123, 'category_id' => 7, 'category_name' => 'Categoría ficticia', 'employee_name' => 'Persona ficticia', 'user_name' => 'persona-prueba', 'document_id' => $v['document_id'], 'version_id' => $v['id'], 'version_number' => 1, 'assignment_token' => str_repeat('a', 64), 'assignment_origin' => 'individual', 'content_hash' => hash('sha256', $snapshot), 'content_snapshot' => $snapshot, 'statement' => 'Aceptación ficticia previa', 'accepted_at' => Library::now()]);
        $manual = ['id' => '', 'sourceId' => '', 'collection' => 'politicas', 'code' => 'POL-PREVIA', 'title' => 'Política anterior', 'sourceHeading' => '', 'summary' => '', 'version' => '1.0', 'declaredState' => 'borrador', 'validFrom' => null, 'lastReview' => null, 'reviewMonths' => 12, 'approver' => '', 'approvalRecord' => null, 'responsible' => 'Calidad', 'history' => [], 'sections' => [['id' => 's1', 'title' => 'Objeto', 'html' => '<p>Contenido previo.</p>', 'text' => '']]];
        $policy = $library->create('politicas', $manual, 'fixture', (string) Str::uuid());
        DB::table('bib_versions')->where('id', $policy['id'])->update(['state' => 'Publicado', 'current_slot' => 1, 'published_at' => Library::now(), 'published_by' => 'fixture']);
        DB::table('bib_policy_approvals')->insert(['version_id' => $policy['id'], 'user_id' => 9, 'approver' => 'Gerente ficticio', 'approved_at' => Library::now(), 'content_hash' => hash('sha256', $policy['payload_json'])]);
        return ['published' => $published, 'draft' => $draft, 'policy_document' => $library->document($policy['document_id']), 'policy_version' => $library->version($policy['id'])];
    }

    private function databaseSnapshot(): array
    {
        $snapshot = [];
        foreach (self::TABLES as $table) $snapshot[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        return $snapshot;
    }

    public function test_baseline_archives_prior_jobs_preserves_evidence_and_creates_version_zero_with_verified_sources(): void
    {
        $old = $this->priorState();
        $before = $this->databaseSnapshot();
        $plan = $this->plan();
        $report = (new BaselineImport)->apply($plan, 'importador-ficticio');
        $this->assertSame(2, $report['imported']);
        $this->assertSame(1, $report['archived_documents']);
        $this->assertSame(2, $report['archived_versions']);
        $this->assertFalse($report['alreadyImported']);
        $this->assertFileExists($report['backup']);
        $backup = Content::decode(file_get_contents($report['backup']));
        $this->assertSame($before, $backup['tables']);
        $this->assertSame($plan['id'], $backup['batch']);
        foreach (['bib_category_documents', 'bib_employee_documents', 'bib_acceptances', 'bib_policy_approvals'] as $table) {
            $this->assertSame($before[$table], DB::table($table)->get()->map(fn ($row) => (array) $row)->all());
        }
        $library = new Library;
        $this->assertSame($old['policy_document'], $library->document($old['policy_document']['id']));
        $this->assertSame($old['policy_version'], $library->version($old['policy_version']['id']));
        foreach ([$old['published'], $old['draft']] as $prior) {
            $after = $library->version($prior['id']);
            foreach (['number', 'payload_json', 'parsed_json', 'published_at', 'published_by'] as $field) $this->assertSame($prior[$field], $after[$field]);
            $this->assertSame('Histórico / retirado', $after['state']);
            $this->assertNull($after['current_slot']);
            $this->assertSame($prior['revision'] + 1, $after['revision']);
        }
        $this->assertSame('legacy', $library->document($old['published']['document_id'])['baseline_role']);
        $new = DB::table('bib_documents')->where('baseline_role', 'current')->orderBy('baseline_source_id')->get();
        $this->assertCount(2, $new);
        foreach ($new as $index => $doc) {
            $v = DB::table('bib_versions')->where('document_id', $doc->id)->sole();
            $this->assertSame(0, $v->number);
            $this->assertSame('Borrador', $v->state);
            $this->assertSame('Pendiente', $v->review_state);
            $this->assertNull($v->published_at);
            $this->assertNull($v->current_slot);
            $this->assertSame($plan['items'][$index]['content']['name'], Content::decode($v->payload_json)['name']);
            $source = DB::table('bib_sources')->where('document_id', $doc->id)->sole();
            $this->assertSame($plan['items'][$index]['sha256'], $source->sha256);
            $this->assertSame($source->sha256, hash_file('sha256', config('biblioteca.storage').'/'.$source->snapshot));
        }
        $this->assertSame(1, DB::table('bib_findings')->where('status', 'Pendiente')->count());
        foreach (array_merge($plan['items'], $plan['attachments']) as $source) $this->assertSame($source['sha256'], hash_file('sha256', $plan['root'].'/'.$source['relative']));
        $attachment = $plan['attachments'][0];
        $this->assertSame($attachment['sha256'], hash_file('sha256', config('biblioteca.storage').'/originales/'.$attachment['sha256'].'.xlsx'));
    }

    public function test_repeating_the_identical_plan_is_idempotent(): void
    {
        $this->priorState(); $plan = $this->plan(); $service = new BaselineImport;
        $first = $service->apply($plan, 'fixture'); $before = $this->databaseSnapshot();
        $second = $service->apply($plan, 'fixture');
        $this->assertTrue($second['alreadyImported']);
        $this->assertSame($first['backup'], $second['backup']);
        $this->assertSame($before, $this->databaseSnapshot());
        $this->assertCount(1, glob(dirname($first['backup']).'/antes-*.json'));
    }

    public function test_changed_source_aborts_before_any_database_change(): void
    {
        $this->priorState(); $plan = $this->plan(); $before = $this->databaseSnapshot();
        file_put_contents($plan['root'].'/'.$plan['items'][0]['relative'], 'changed fixture');
        try { (new BaselineImport)->apply($plan, 'fixture'); $this->fail('Debe rechazar una fuente modificada.'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('Cambió el origen', $e->getMessage()); }
        $this->assertSame($before, $this->databaseSnapshot());
        $this->assertDirectoryDoesNotExist(config('biblioteca.storage').'/originales');
    }

    public function test_failure_during_second_insert_rolls_back_archival_and_all_database_writes(): void
    {
        $this->priorState(); $plan = $this->plan(); $before = $this->databaseSnapshot();
        DB::unprepared("CREATE TRIGGER fail_second_baseline BEFORE INSERT ON bib_documents WHEN NEW.baseline_source_id = 2 BEGIN SELECT RAISE(ABORT, 'injected failure'); END");
        try { (new BaselineImport)->apply($plan, 'fixture'); $this->fail('Debe propagar el fallo de inserción.'); }
        catch (\Illuminate\Database\QueryException $e) { $this->assertStringContainsString('injected failure', $e->getMessage()); }
        $this->assertSame($before, $this->databaseSnapshot());
        $backups = glob(config('biblioteca.storage').'/bases/'.$plan['id'].'/antes-*.json');
        $this->assertCount(1, $backups);
        $this->assertSame($before, Content::decode(file_get_contents($backups[0]))['tables']);
        DB::unprepared('DROP TRIGGER fail_second_baseline');
        $this->assertFalse((new BaselineImport)->apply($plan, 'fixture')['alreadyImported']);
    }

    public function test_next_version_after_approved_base_zero_is_one(): void
    {
        (new BaselineImport)->apply($this->plan(), 'fixture');
        $doc = DB::table('bib_documents')->where('baseline_role', 'current')->first();
        $v = DB::table('bib_versions')->where('document_id', $doc->id)->sole();
        $library = new Library;
        $published = $library->publish($v->id, 0, 'Validación ficticia', true, 'fixture');
        $this->assertSame(0, $published['number']);
        $next = $library->draft($published['id'], 'fixture');
        $this->assertSame(1, $next['number']);
        $this->assertSame('Borrador', $next['state']);
        $this->assertSame($published['id'], $next['based_on']);
    }

    public function test_importing_a_different_batch_after_baseline_is_rejected(): void
    {
        $plan = $this->plan(); $service = new BaselineImport; $service->apply($plan, 'fixture');
        $before = $this->databaseSnapshot(); $plan['id'] = hash('sha256', 'different baseline');
        try { $service->apply($plan, 'fixture'); $this->fail('Debe rechazar una segunda base.'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('Ya hay una base importada', $e->getMessage()); }
        $this->assertSame($before, $this->databaseSnapshot());
    }

    public function test_legacy_document_cannot_receive_a_new_version(): void
    {
        $old = $this->priorState(); (new BaselineImport)->apply($this->plan(), 'fixture');
        $before = $this->databaseSnapshot();
        try { (new Library)->insertVersion($old['published']['document_id'], $this->job('Intento sobre antecedente'), 'fixture'); $this->fail('No se debe crear una versión sobre un antecedente.'); }
        catch (\Illuminate\Validation\ValidationException | \Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertNotEmpty($e->getMessage()); }
        $this->assertSame($before, $this->databaseSnapshot());
    }
    public function test_legacy_documents_are_excluded_from_new_assignments_without_resetting_existing_links(): void
    {
        \Illuminate\Support\Facades\Schema::create('categ_empleados', function (\Illuminate\Database\Schema\Blueprint $t) { $t->integer('ID_CATEG')->primary(); $t->string('NOMBRE'); $t->integer('estado'); });
        \Illuminate\Support\Facades\Schema::create('servicios', function (\Illuminate\Database\Schema\Blueprint $t) { $t->integer('ID_SERVICIOS')->primary(); $t->string('NOMBRE'); });
        \Illuminate\Support\Facades\Schema::create('rol_empleados', function (\Illuminate\Database\Schema\Blueprint $t) { $t->integer('ID_ROL')->primary(); $t->string('NOMBRE'); });
        \Illuminate\Support\Facades\Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $t) { $t->integer('id')->primary(); $t->integer('legajo'); $t->string('estado'); });
        \Illuminate\Support\Facades\Schema::create('empleados', function (\Illuminate\Database\Schema\Blueprint $t) { $t->integer('LEGAJO')->primary(); $t->string('COLABORADOR'); $t->integer('ID_CATEG'); $t->integer('ID_SERVICIOS')->nullable(); $t->integer('ID_ROL')->nullable(); $t->string('CONVENIO')->nullable(); $t->string('ESTADO'); });
        DB::table('categ_empleados')->insert([['ID_CATEG' => 7, 'NOMBRE' => 'Categoría anterior', 'estado' => 1], ['ID_CATEG' => 8, 'NOMBRE' => 'Otra categoría', 'estado' => 1]]);
        DB::table('empleados')->insert([['LEGAJO' => 123, 'COLABORADOR' => 'Persona con vínculo', 'ID_CATEG' => 7, 'ESTADO' => 'ACTIVO'], ['LEGAJO' => 124, 'COLABORADOR' => 'Persona sin vínculo', 'ID_CATEG' => 8, 'ESTADO' => 'ACTIVO']]);
        $old = $this->priorState();
        (new BaselineImport)->apply($this->plan(), 'fixture');
        $service = new Assignments;
        $legacy = $old['published']['document_id'];
        $before = $this->databaseSnapshot();
        $dashboard = $service->dashboard();
        $this->assertCount(2, $dashboard['documents']);
        foreach ($dashboard['documents'] as $doc) $this->assertSame('current', $doc->baseline_role);
        $employee = collect($dashboard['employees'])->first(fn ($r) => $r['employee']->LEGAJO === 123);
        $this->assertSame($legacy, $employee['document']->id);
        $service->save('employee', 123, $legacy, 3, 'fixture');
        $service->save('category', 7, $legacy, 3, 'fixture');
        $this->assertSame($before, $this->databaseSnapshot());
        foreach (['employee' => 124, 'category' => 8] as $scope => $id) {
            try { $service->save($scope, $id, $legacy, 0, 'fixture'); $this->fail('No debe permitir nuevas asignaciones a antecedentes.'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertStringContainsString('antecedente', $e->getMessage()); }
        }
        $this->assertSame($before, $this->databaseSnapshot());
        $current = $dashboard['documents'][0]->id;
        $service->save('employee', 123, $current, 3, 'fixture');
        $this->assertSame($current, DB::table('bib_employee_documents')->where('legajo', 123)->value('document_id'));
        try { $service->save('employee', 123, $legacy, 4, 'fixture'); $this->fail('No debe volver a un antecedente.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertStringContainsString('antecedente', $e->getMessage()); }
        $this->assertSame($current, DB::table('bib_employee_documents')->where('legajo', 123)->value('document_id'));
    }
}