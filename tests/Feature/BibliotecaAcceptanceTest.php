<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Biblioteca\Acceptances;
use App\Services\Biblioteca\Assignments;
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge();
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('username');
            $t->string('rol');
            $t->integer('legajo')->nullable();
            $t->string('estado');
        });
        Schema::create('empleados', function (Blueprint $t) {
            $t->integer('LEGAJO')->primary();
            $t->string('COLABORADOR');
            $t->integer('ID_CATEG');
            $t->integer('ID_SERVICIOS')->nullable();
            $t->integer('ID_ROL')->nullable();
            $t->string('CONVENIO')->nullable();
            $t->string('ESTADO');
        });
        Schema::create('servicios', function (Blueprint $t) {
            $t->integer('ID_SERVICIOS')->primary();
            $t->string('NOMBRE');
        });
        Schema::create('rol_empleados', function (Blueprint $t) {
            $t->integer('ID_ROL')->primary();
            $t->string('NOMBRE');
        });
        Schema::create('categ_empleados', function (Blueprint $t) {
            $t->integer('ID_CATEG')->primary();
            $t->string('NOMBRE');
            $t->integer('estado');
        });
        foreach (['2026_09_18_150000_create_biblioteca_tables.php', '2026_09_20_120000_add_biblioteca_assignments_and_acceptances.php', '2026_09_21_010000_add_biblioteca_policy_governance.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('categ_empleados')->insert([['ID_CATEG' => 1, 'NOMBRE' => 'Administrativo', 'estado' => 1], ['ID_CATEG' => 2, 'NOMBRE' => 'Analista', 'estado' => 1], ['ID_CATEG' => 3, 'NOMBRE' => 'Inactiva', 'estado' => 0]]);
        DB::table('empleados')->insert([['LEGAJO' => 101, 'COLABORADOR' => 'Persona Uno', 'ID_CATEG' => 1, 'ESTADO' => 'ACTIVO'], ['LEGAJO' => 102, 'COLABORADOR' => 'Persona Dos', 'ID_CATEG' => 2, 'ESTADO' => 'ACTIVO']]);
        DB::table('users')->insert([['id' => 99, 'name' => 'Administrador', 'username' => 'admin', 'rol' => 'Administrador/a', 'legajo' => 900, 'estado' => 'ACTIVO'], ['id' => 100, 'name' => 'Persona Uno', 'username' => 'persona1', 'rol' => 'Colaborador/a', 'legajo' => 101, 'estado' => 'ACTIVO'], ['id' => 101, 'name' => 'Persona Dos', 'username' => 'persona2', 'rol' => 'Colaborador/a', 'legajo' => 102, 'estado' => 'ACTIVO']]);
        $this->actingAs(User::findOrFail(100));
    }

    private function job(bool $publish = true): array
    {
        $c = Content::institutional(Content::emptyJob());
        $c['name'] = 'Puesto de prueba';
        $c['area'] = 'Calidad';
        $c['groups']['purpose'] = [Content::paragraph('Acompañar a las personas.')];
        $c['groups']['tasks'] = [Content::paragraph('Atender consultas.')];
        $lib = new Library;
        $v = $lib->create('descriptivos', $c, 'test', (string) Str::uuid());

        return $publish ? $lib->publish($v['id'], 0, 'Versión verificada', true, 'test') : $v;
    }

    private function assign(array $v): void
    {
        (new Assignments)->save('category', 1, $v['document_id'], 0, 'test');
    }

    private function payload(): array
    {
        $c = (new Acceptances)->context(auth()->user());

        return ['version_id' => $c['entry']['version']['id'], 'assignment_token' => $c['token'], 'content_hash' => $c['hash'], 'confirmed' => '1'];
    }

    public function test_admin_routes_are_denied_to_every_non_admin_role(): void
    {
        foreach (['Colaborador/a', 'Coordinador/a', 'Coordinador/a L2', 'Supervisor/a Calidad'] as $role) {
            $user = User::findOrFail(100);
            $user->rol = $role;
            $this->actingAs($user);
            foreach (['control', 'manage', 'new', 'upload', 'sync', 'coverage', 'acceptance.register'] as $name) {
                $this->get(route('biblioteca.'.$name))->assertForbidden();
            }
            $this->post(route('biblioteca.assign', ['scope' => 'category', 'id' => 1]), [])->assertForbidden();
            $this->postJson(route('biblioteca.store'), [])->assertForbidden();
            $this->postJson(route('biblioteca.action', 'unavailable'), ['action' => 'draft'])->assertForbidden();
            $this->get(route('biblioteca.source', 'unavailable'))->assertForbidden();
            $this->get(route('biblioteca.index'))->assertOk()->assertDontSee(route('biblioteca.control'), false)->assertDontSee(route('biblioteca.manage'), false)->assertSee('Mi descriptivo');
        }
        $this->actingAs(User::findOrFail(99));
        $this->get(route('biblioteca.control'))->assertOk();
        $this->get(route('biblioteca.coverage'))->assertOk()->assertSee('Administrativo')->assertDontSee('Cobertura de todas las categorías');
    }

    public function test_explicit_consent_and_authenticated_identity_are_required(): void
    {
        $v = $this->job();
        $this->assign($v);
        $payload = $this->payload();
        $payload['confirmed'] = '0';
        $this->postJson(route('biblioteca.sign'), $payload)->assertUnprocessable();
        $this->assertSame(0, DB::table('bib_acceptances')->count());
        $payload['confirmed'] = '1';
        $payload['user_id'] = 101;
        $payload['legajo'] = 102;
        $this->post(route('biblioteca.sign'), $payload)->assertRedirect();
        $record = DB::table('bib_acceptances')->first();
        $this->assertSame(100, $record->user_id);
        $this->assertSame(101, $record->legajo);
        $this->assertSame(Acceptances::STATEMENT, $record->statement);
        $this->assertSame(hash('sha256', $record->content_snapshot), $record->content_hash);
        $this->post(route('biblioteca.sign'), $payload)->assertRedirect(route('biblioteca.receipt', $record->id));
        $this->assertSame(1, DB::table('bib_acceptances')->count());
        $this->get(route('biblioteca.mine'))->assertOk()->assertSee('Ya firmaste esta versión')->assertDontSee('id="bib-sign-form"', false);
    }

    public function test_new_publication_requires_new_acceptance_and_preserves_old_receipt(): void
    {
        $lib = new Library;
        $v = $this->job();
        $this->assign($v);
        $oldPayload = $this->payload();
        $first = (new Acceptances)->accept(auth()->user(), $oldPayload);
        $snapshot = $first->content_snapshot;
        $draft = $lib->draft($v['id'], 'test');
        $content = Content::decode($draft['payload_json']);
        $content['groups']['tasks'][] = Content::paragraph('Nueva tarea publicada.');
        $draft = $lib->save($draft['id'], 0, $content, 'test');
        $this->assertFalse((new Acceptances)->context(auth()->user())['canSign']);
        $lib->publish($draft['id'], $draft['revision'], 'Actualización', true, 'test');
        $this->assertTrue((new Acceptances)->context(auth()->user())['canSign']);
        $this->postJson(route('biblioteca.sign'), $oldPayload)->assertConflict();
        $this->post(route('biblioteca.sign'), $oldPayload)->assertRedirect(route('biblioteca.mine'))->assertSessionHasErrors('acceptance');
        $this->get(route('biblioteca.receipt', $first->id))->assertOk()->assertDontSee('Nueva tarea publicada.');
        $this->post(route('biblioteca.sign'), $this->payload())->assertRedirect();
        $this->assertSame(2, DB::table('bib_acceptances')->count());
        $this->assertSame($snapshot, DB::table('bib_acceptances')->where('id', $first->id)->value('content_snapshot'));
        $this->actingAs(User::findOrFail(99));
        $this->get(route('biblioteca.coverage'))->assertOk()->assertSee('Firmado');
    }

    public function test_receipts_are_visible_only_to_the_signer_and_administrators(): void
    {
        $v = $this->job();
        $this->assign($v);
        $a = (new Acceptances)->accept(auth()->user(), $this->payload());
        $this->get(route('biblioteca.receipt', $a->id))->assertOk()->assertSee('Persona Uno');
        $this->actingAs(User::findOrFail(101));
        $this->get(route('biblioteca.receipt', $a->id))->assertNotFound();
        $this->get(route('biblioteca.mine'))->assertOk()->assertDontSee($a->id);
        $this->actingAs(User::findOrFail(99));
        $this->get(route('biblioteca.receipt', $a->id))->assertOk();
        $this->get(route('biblioteca.acceptance.register'))->assertOk()->assertSee('Persona Uno');
        $this->postJson(route('biblioteca.sign'), ['version_id' => $v['id'], 'assignment_token' => $a->assignment_token, 'content_hash' => $a->content_hash, 'confirmed' => true, 'user_id' => 100])->assertConflict();
    }

    public function test_unassigned_draft_and_retired_documents_cannot_be_signed(): void
    {
        $this->get(route('biblioteca.mine'))->assertOk()->assertSee('Todavía no tenés un descriptivo asignado');
        $v = $this->job(false);
        $this->assign($v);
        $this->get(route('biblioteca.mine'))->assertOk()->assertSee('todavía no tiene una versión publicada');
        $v = (new Library)->publish($v['id'], 0, 'Prueba', true, 'test');
        $payload = $this->payload();
        (new Library)->retire($v['id'], $v['revision'], 'Retiro de prueba', 'test');
        $this->postJson(route('biblioteca.sign'), $payload)->assertConflict();
        $this->assertSame(0, DB::table('bib_acceptances')->count());
    }

    public function test_individual_assignment_overrides_category_and_invalidates_stale_forms(): void
    {
        $first = $this->job();
        $this->assign($first);
        $old = $this->payload();
        $second = $this->job();
        (new Assignments)->save('employee', 101, $second['document_id'], 0, 'test');
        $context = (new Acceptances)->context(auth()->user());
        $this->assertSame('individual', $context['origin']);
        $this->assertSame($second['id'], $context['entry']['version']['id']);
        $this->postJson(route('biblioteca.sign'), $old)->assertConflict();
        (new Acceptances)->accept(auth()->user(), $this->payload());
        (new Assignments)->save('employee', 101, null, 1, 'test');
        $context = (new Acceptances)->context(auth()->user());
        $this->assertSame('category', $context['origin']);
        $this->assertTrue($context['canSign']);
        $this->postJson(route('biblioteca.sign'), $old)->assertConflict();
        $this->actingAs(User::findOrFail(99));
        $this->postJson(route('biblioteca.assign', ['scope' => 'employee', 'id' => 101]), ['revision' => 0, 'document_id' => $second['document_id']])->assertConflict();
    }

    public function test_tampered_hash_token_or_other_version_is_rejected(): void
    {
        $v = $this->job();
        $this->assign($v);
        $payload = $this->payload();
        foreach (['content_hash', 'assignment_token', 'version_id'] as $key) {
            $bad = $payload;
            $bad[$key] = $key === 'version_id' ? (string) Str::uuid() : str_repeat('0', 64);
            $this->postJson(route('biblioteca.sign'), $bad)->assertConflict();
        }
        $this->assertSame(0, DB::table('bib_acceptances')->count());
    }

    public function test_missing_legajo_duplicate_account_and_inactive_identity_are_blocked(): void
    {
        $v = $this->job();
        $this->assign($v);
        $payload = $this->payload();
        DB::table('users')->where('id', 101)->update(['legajo' => 101]);
        $this->postJson(route('biblioteca.sign'), $payload)->assertConflict();
        $this->get(route('biblioteca.mine'))->assertSee('varias cuentas activas');
        DB::table('users')->where('id', 101)->update(['legajo' => 102]);
        DB::table('users')->where('id', 100)->update(['estado' => 'DE BAJA']);
        $this->postJson(route('biblioteca.sign'), $payload)->assertConflict();
        DB::table('users')->where('id', 100)->update(['estado' => 'ACTIVO', 'legajo' => null]);
        $this->get(route('biblioteca.mine'))->assertSee('no tiene un legajo vinculado');
        DB::table('users')->where('id', 100)->update(['legajo' => 101]);
        DB::table('empleados')->where('LEGAJO', 101)->update(['ESTADO' => 'DE BAJA']);
        $this->postJson(route('biblioteca.sign'), $payload)->assertConflict();
        $this->assertSame(0, DB::table('bib_acceptances')->count());
    }

    public function test_category_change_during_reading_requires_reloading(): void
    {
        $v = $this->job();
        $this->assign($v);
        $payload = $this->payload();
        (new Assignments)->save('category', 2, $v['document_id'], 0, 'test');
        DB::table('empleados')->where('LEGAJO', 101)->update(['ID_CATEG' => 2]);
        $this->postJson(route('biblioteca.sign'), $payload)->assertConflict();
        $this->post(route('biblioteca.sign'), $this->payload())->assertRedirect();
        $this->assertSame(2, DB::table('bib_acceptances')->value('category_id'));
    }

    public function test_coverage_reports_employees_instead_of_category_coverage(): void
    {
        $this->actingAs(User::findOrFail(99));
        $response = $this->get(route('biblioteca.coverage'))->assertOk();
        $response->assertViewHas('counts', fn ($c) => $c['missing'] === 2 && $c['employees'] === 2);
        $response->assertSee('Sin descriptivo asignado')->assertSee('Persona Uno')
            ->assertDontSee('Cobertura de todas las categorías')->assertDontSee('Guardar asignación');
        $v = $this->job(false);
        $this->post(route('biblioteca.assign', ['scope' => 'category', 'id' => 1]), ['document_id' => $v['document_id'], 'revision' => 0])->assertRedirect();
        $this->get(route('biblioteca.coverage'))->assertViewHas('counts', fn ($c) => $c['missing'] === 1 && $c['unpublished'] === 1);
        $this->postJson(route('biblioteca.assign', ['scope' => 'category', 'id' => 3]), ['document_id' => $v['document_id'], 'revision' => 0])->assertUnprocessable();
    }
    public function test_live_coverage_filters_all_employees_and_keeps_pagination_and_permissions(): void
    {
        $url = route('biblioteca.coverage');
        $this->getJson($url)->assertForbidden();
        $this->actingAs(User::findOrFail(99));
        for ($i = 200; $i < 225; $i++) {
            DB::table('empleados')->insert(['LEGAJO' => $i, 'COLABORADOR' => 'Álvarez '.$i, 'ID_CATEG' => 2, 'ESTADO' => 'ACTIVO']);
        }
        $html = $this->getJson($url.'?q=alvarez')->assertOk()->json('html');
        $this->assertStringContainsString('25 colaboradores encontrados', $html);
        $this->assertStringContainsString('Página 1 de 2', $html);
        $this->assertStringNotContainsString('Persona Uno', $html);
        $this->assertStringNotContainsString('<html', $html);
        $html = $this->getJson($url.'?q=alvarez&page=2')->assertOk()->json('html');
        $this->assertStringContainsString('Álvarez 224', $html);
        $html = $this->getJson($url.'?q=224')->assertOk()->json('html');
        $this->assertStringContainsString('1 colaboradores encontrados', $html);
        $html = $this->getJson($url.'?q=Administrativo')->assertOk()->json('html');
        $this->assertStringContainsString('Persona Uno', $html);
        $this->assertStringNotContainsString('Persona Dos', $html);
        $html = $this->getJson($url.'?q=alvarez&status=Firmado')->assertOk()->json('html');
        $this->assertStringContainsString('No hay colaboradores con esos filtros', $html);
        $html = $this->getJson($url.'?q=')->assertOk()->json('html');
        $this->assertStringContainsString('27 colaboradores encontrados', $html);
    }

    public function test_individual_autosave_returns_json_and_preserves_receipts_and_conflict_protection(): void
    {
        $first = $this->job();
        (new Assignments)->save('employee', 101, $first['document_id'], 0, 'test');
        $receipt = (new Acceptances)->accept(auth()->user(), $this->payload());
        $second = $this->job();
        $url = route('biblioteca.assign', ['scope' => 'employee', 'id' => 101]);
        $this->postJson($url, ['document_id' => $second['document_id'], 'revision' => 1])->assertForbidden();
        $this->actingAs(User::findOrFail(99));
        $this->postJson($url, ['document_id' => $second['document_id'], 'revision' => 1])
            ->assertOk()->assertJsonPath('legajo', 101)->assertJsonPath('message', 'Asignación guardada.');
        $this->assertSame(2, DB::table('bib_employee_documents')->where('legajo', 101)->value('revision'));
        $data = $this->getJson(route('biblioteca.coverage').'?q=101')->assertOk()->json();
        $this->assertSame(1, $data['counts']['pending']);
        $this->assertSame(0, $data['counts']['signed']);
        $this->assertStringContainsString('value="2"', $data['html']);
        $this->assertStringContainsString('Pendiente de firma', $data['html']);
        $this->postJson($url, ['document_id' => $first['document_id'], 'revision' => 1])->assertConflict();
        $this->postJson($url, ['document_id' => 'missing', 'revision' => 2])->assertUnprocessable();
        $this->assertSame($second['document_id'], DB::table('bib_employee_documents')->where('legajo', 101)->value('document_id'));
        $this->postJson($url, ['document_id' => $second['document_id'], 'revision' => 2])->assertOk();
        $this->assertSame(2, DB::table('bib_employee_documents')->where('legajo', 101)->value('revision'));
        $this->postJson($url, ['document_id' => '', 'revision' => 2])->assertOk();
        $this->getJson(route('biblioteca.coverage'))->assertJsonPath('counts.missing', 2)->assertJsonPath('counts.pending', 0);
        $this->assertSame($receipt->content_snapshot, DB::table('bib_acceptances')->where('id', $receipt->id)->value('content_snapshot'));
        $this->assertSame(1, DB::table('bib_acceptances')->count());
    }

    public function test_coverage_searches_service_role_and_convention_without_changing_categories(): void
    {
        DB::table('servicios')->insert(['ID_SERVICIOS' => 1, 'NOMBRE' => 'Facturación']);
        DB::table('rol_empleados')->insert(['ID_ROL' => 1, 'NOMBRE' => 'Telefonista']);
        DB::table('empleados')->where('LEGAJO', 101)->update(['ID_SERVICIOS' => 1, 'ID_ROL' => 1, 'CONVENIO' => 'SANIDAD']);
        DB::table('empleados')->where('LEGAJO', 102)->update(['CONVENIO' => 'FUERA DE CONVENIO']);
        $this->actingAs(User::findOrFail(99));
        foreach (['facturacion', 'telefonista', 'sanidad'] as $q) {
            $html = $this->getJson(route('biblioteca.coverage', ['q' => $q, 'page' => 9]))->assertOk()->json('html');
            $this->assertStringContainsString('Persona Uno', $html);
            $this->assertStringNotContainsString('Persona Dos', $html);
        }
        $html = $this->getJson(route('biblioteca.coverage', ['q' => 'fuera de convenio']))->assertOk()->json('html');
        $this->assertStringContainsString('Persona Dos', $html);
        $this->assertStringContainsString('Servicio sin informar', $html);
        $this->assertSame(1, DB::table('empleados')->where('LEGAJO', 101)->value('ID_CATEG'));
    }

    private function policy(): array
    {
        return (new Library)->create('politicas', ['id'=>'','sourceId'=>'','collection'=>'politicas','code'=>'POL-'.Str::random(8),'title'=>'Política de prueba','sourceHeading'=>'','summary'=>'Resumen','version'=>'1.0','declaredState'=>'borrador','validFrom'=>null,'lastReview'=>null,'reviewMonths'=>12,'approver'=>'','approvalRecord'=>null,'responsible'=>'Gerencia','history'=>[],'sections'=>[['id'=>'s1','title'=>'Objetivo','html'=>'<p>Contenido aprobado.</p>','text'=>'']]], 'test', (string)Str::uuid());
    }

    private function managerApprover(): void
    {
        DB::table('users')->where('id',99)->update(['username'=>'mcardoner','name'=>'Mariano Cardoner']);
        $this->actingAs(User::findOrFail(99));
    }

    public function test_policies_require_personal_approval_and_visibility_applies_to_search_and_exports(): void
    {
        $v=$this->policy();
        $this->get(route('biblioteca.show',$v['document_id']))->assertNotFound();
        $this->get(route('biblioteca.export',[$v['id'],'pdf']))->assertNotFound();
        $this->get(route('biblioteca.search'))->assertDontSee('Política de prueba');
        $this->post(route('biblioteca.policy.approve',$v['id']),['revision'=>0,'confirmed'=>1])->assertForbidden();
        $this->actingAs(User::findOrFail(99));
        $this->post(route('biblioteca.policy.approve',$v['id']),['revision'=>0,'confirmed'=>1])->assertForbidden();
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'publish','revision'=>0,'confirmed'=>true])->assertForbidden();
        $this->managerApprover();
        $this->postJson(route('biblioteca.policy.approve',$v['id']),['revision'=>0])->assertUnprocessable();
        $this->post(route('biblioteca.policy.approve',$v['id']),['revision'=>0,'confirmed'=>1])->assertRedirect();
        $approval=DB::table('bib_policy_approvals')->first();
        $this->assertSame(99,$approval->user_id);
        $this->assertSame(hash('sha256',(new Library)->version($v['id'])['payload_json']),$approval->content_hash);
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk()->assertSee('Aprobación registrada');
        $this->get(route('biblioteca.search'))->assertSee('Política de prueba');
        $this->get(route('biblioteca.export',[$v['id'],'pdf']))->assertOk();
        $this->post(route('biblioteca.visibility.save'),['key'=>'document:'.$v['document_id'],'visible'=>0,'revision'=>0])->assertForbidden();
        $this->managerApprover();
        $this->post(route('biblioteca.visibility.save'),['key'=>'document:'.$v['document_id'],'visible'=>0,'revision'=>0])->assertRedirect();
        $this->post(route('biblioteca.visibility.save'),['key'=>'document:'.$v['document_id'],'visible'=>1,'revision'=>0])->assertConflict();
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.show',$v['document_id']))->assertNotFound();
        $this->get(route('biblioteca.export',[$v['id'],'docx']))->assertNotFound();
        $this->get(route('biblioteca.search'))->assertDontSee('Política de prueba');
        $this->managerApprover();
        $this->post(route('biblioteca.visibility.save'),['key'=>'document:'.$v['document_id'],'visible'=>1,'revision'=>1])->assertRedirect();
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk();
    }

    public function test_each_policy_version_needs_new_approval_and_stale_approvals_fail(): void
    {
        $this->managerApprover();$v=$this->policy();$lib=new Library;
        $this->post(route('biblioteca.policy.approve',$v['id']),['revision'=>0,'confirmed'=>1])->assertRedirect();
        $draft=$lib->draft($v['id'],'test');
        $this->assertNull(Content::decode($draft['payload_json'])['approvalRecord']);
        $c=Content::decode($draft['payload_json']);$c['sections'][0]['html']='<p>Nueva edición.</p>';
        $saved=$lib->save($draft['id'],0,$c,'test');
        $this->post(route('biblioteca.policy.approve',$draft['id']),['revision'=>0,'confirmed'=>1])->assertConflict();
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.show',['document'=>$v['document_id'],'version'=>$draft['id']]))->assertNotFound();
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk()->assertDontSee('Nueva edición.');
        $this->managerApprover();
        $this->post(route('biblioteca.policy.approve',$draft['id']),['revision'=>$saved['revision'],'confirmed'=>1])->assertRedirect();
        $this->assertSame(2,DB::table('bib_policy_approvals')->count());
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.show',$v['document_id']))->assertSee('Nueva edición.');
        $this->get(route('biblioteca.export',[$v['id'],'pdf']))->assertNotFound();
    }

    public function test_sections_can_be_hidden_and_management_has_all_documents_on_one_page(): void
    {
        $this->get(route('biblioteca.index'))->assertDontSee('href="'.route('biblioteca.catalog','instructivos').'"',false);
        $this->get(route('biblioteca.catalog','instructivos'))->assertNotFound();
        $this->actingAs(User::findOrFail(99));
        $this->get(route('biblioteca.visibility'))->assertOk()->assertSee('Instructivos');
        $this->post(route('biblioteca.visibility.save'),['key'=>'section:instructivos','visible'=>1,'revision'=>0])->assertRedirect();
        $this->actingAs(User::findOrFail(100));
        $this->get(route('biblioteca.catalog','instructivos'))->assertOk();
        $this->actingAs(User::findOrFail(99));
        for($i=0;$i<32;$i++) $this->job(false);
        $this->get(route('biblioteca.manage',['page'=>4]))->assertOk()->assertViewHas('entries',fn($p)=>$p->count()===32 && !$p->hasPages());
    }

    public function test_index_only_prompts_for_an_unsigned_current_job(): void
    {
        $this->get(route('biblioteca.index'))->assertDontSee('Ver y firmar mi descriptivo');
        $v=$this->job();$this->assign($v);
        $this->get(route('biblioteca.index'))->assertSee('Ver y firmar mi descriptivo');
        $this->post(route('biblioteca.sign'),$this->payload())->assertRedirect();
        $this->get(route('biblioteca.index'))->assertDontSee('Ver y firmar mi descriptivo');
        $lib=new Library;$draft=$lib->draft($v['id'],'test');$lib->publish($draft['id'],0,'Nueva versión',true,'test');
        $this->get(route('biblioteca.index'))->assertSee('Ver y firmar mi descriptivo');
    }

    public function test_preview_is_read_only_and_never_replaces_the_real_session_identity(): void
    {
        $v=$this->job();$this->assign($v);
        $this->get(route('biblioteca.preview'))->assertForbidden();
        $this->post(route('biblioteca.preview.start'),['user_id'=>99])->assertForbidden();
        $this->actingAs(User::findOrFail(99));
        $this->post(route('biblioteca.preview.start'),['user_id'=>100])->assertForbidden();
        DB::table('users')->where('id',99)->update(['username'=>'ffernandez']);
        $real=User::findOrFail(99);$this->actingAs($real);
        $this->get(route('biblioteca.preview'))->assertOk()->assertSee('Persona Uno');
        $this->post(route('biblioteca.preview.start'),['user_id'=>100])->assertRedirect(route('biblioteca.mine'));
        $response=$this->get(route('biblioteca.mine'))->assertOk()->assertSee('Estás viendo como Persona Uno')->assertSee('Tenés una aceptación pendiente')->assertDontSee('id="bib-sign-form"',false);
        $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
        $this->assertAuthenticatedAs($real);
        $this->assertSame(99,auth()->id());
        $this->get(route('biblioteca.manage'))->assertForbidden();
        $this->post(route('biblioteca.sign'),['confirmed'=>1])->assertForbidden();
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'draft'])->assertForbidden();
        $this->post(route('biblioteca.visibility.save'),['key'=>'section:politicas','visible'=>0,'revision'=>0])->assertForbidden();
        $this->post(route('biblioteca.policy.approve',$v['id']),['confirmed'=>1,'revision'=>0])->assertForbidden();
        $this->assertSame(0,DB::table('bib_acceptances')->count());
        \Illuminate\Support\Facades\Route::middleware(['web','auth'])->get('/preview-test-real-user',fn()=>response()->json(['id'=>auth()->id()]));
        $this->get('/preview-test-real-user')->assertJson(['id'=>99]);
        $this->post(route('biblioteca.preview.stop'))->assertRedirect(route('biblioteca.index'));
        $this->get(route('biblioteca.manage'))->assertOk();
        $events=DB::table('bib_events')->whereIn('action',['iniciar_vista_usuario','finalizar_vista_usuario'])->get();
        $this->assertCount(2,$events);
        foreach($events as $event){$this->assertStringContainsString('usuario:99',$event->actor);$this->assertSame(100,Content::decode($event->after_json)['target_id']);}
    }

    public function test_preview_revalidates_the_superuser_and_target_and_supports_switching(): void
    {
        $this->managerApprover();
        $this->postJson(route('biblioteca.preview.start'),['user_id'=>99])->assertUnprocessable();
        $this->post(route('biblioteca.preview.start'),['user_id'=>100])->assertRedirect();
        $this->post(route('biblioteca.preview.start'),['user_id'=>101])->assertRedirect();
        $this->get(route('biblioteca.mine'))->assertOk()->assertSee('Estás viendo como Persona Dos');
        DB::table('users')->where('id',101)->update(['estado'=>'DE BAJA']);
        $this->get(route('biblioteca.mine'))->assertRedirect(route('biblioteca.preview'));
        $this->assertFalse(session()->has('biblioteca_preview'));
        $this->post(route('biblioteca.preview.start'),['user_id'=>101])->assertNotFound();
        $this->post(route('biblioteca.preview.start'),['user_id'=>100])->assertRedirect();
        DB::table('users')->where('id',99)->update(['rol'=>'Colaborador/a']);
        $this->get(route('biblioteca.mine'))->assertForbidden();
        $this->assertFalse(session()->has('biblioteca_preview'));
    }

    public function test_preview_cannot_be_reused_by_a_different_signed_in_user(): void
    {
        $this->managerApprover();
        $this->post(route('biblioteca.preview.start'),['user_id'=>100])->assertRedirect();
        $this->actingAs(User::findOrFail(101));
        $this->get(route('biblioteca.mine'))->assertForbidden();
        $this->assertFalse(session()->has('biblioteca_preview'));
        $this->assertSame(101,auth()->id());
    }

}
