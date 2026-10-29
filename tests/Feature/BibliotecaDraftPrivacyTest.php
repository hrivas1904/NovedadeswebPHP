<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Biblioteca\Acceptances;
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaDraftPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array']);
        DB::purge();
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('username'); $t->string('rol'); $t->string('estado');
        });
        foreach (['2026_09_18_150000_create_biblioteca_tables.php','2026_09_21_010000_add_biblioteca_policy_governance.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Gerencia de prueba','username'=>'mcardoner','rol'=>'Administrador/a','estado'=>'ACTIVO'],
            ['id'=>2,'name'=>'Colaborador de prueba','username'=>'colaborador','rol'=>'Colaborador/a','estado'=>'ACTIVO'],
            ['id'=>3,'name'=>'Coordinador de prueba','username'=>'coordinador','rol'=>'Coordinador/a','estado'=>'ACTIVO'],
        ]);
        $this->mock(Acceptances::class, fn($mock)=>$mock->shouldReceive('context')->andReturn(['canSign'=>false]));
        $this->actingAs(User::findOrFail(1));
    }

    private function job(string $title, bool $publish=false): array
    {
        $c=Content::emptyJob();
        $c['name']=$title; $c['area']='Calidad';
        $c['groups']['purpose']=[Content::paragraph('Propósito verificado.')];
        $c['groups']['tasks']=[Content::paragraph('Tarea verificada.')];
        $library=new Library;
        $v=$library->create('descriptivos',$c,'test',(string)Str::uuid());
        return $publish?$library->publish($v['id'],0,'Validación de prueba',true,'test'):$v;
    }

    public function test_unpublished_jobs_are_hidden_from_catalogs_search_direct_links_and_exports(): void
    {
        $draft=$this->job('Borrador confidencial');
        $imported=$this->job('Importado pendiente');
        DB::table('bib_versions')->where('id',$imported['id'])->update(['state'=>'Vigencia no verificada','origin'=>'imported']);
        $valid=$this->job('Documento vigente permitido',true);
        foreach ([2,3] as $user) {
            $this->actingAs(User::findOrFail($user));
            $this->get(route('biblioteca.index'))->assertOk()->assertViewHas('entries',fn($entries)=>count($entries)===1 && $entries[0]['document']['id']===$valid['document_id'])->assertDontSee('Borrador confidencial')->assertDontSee('Importado pendiente');
            $this->get(route('biblioteca.catalog','descriptivos'))->assertOk()->assertSee('Documento vigente permitido')->assertDontSee('Borrador confidencial')->assertDontSee('Importado pendiente');
            $this->getJson(route('biblioteca.search',['q'=>'confidencial','history'=>1]))->assertOk()->assertJsonPath('html',fn($html)=>!str_contains($html,'Borrador confidencial'));
            foreach ([$draft,$imported] as $v) {
                $this->get(route('biblioteca.show',$v['document_id']))->assertNotFound();
                $this->get(route('biblioteca.show',['document'=>$v['document_id'],'version'=>$v['id']]))->assertNotFound();
                foreach (['pdf','docx'] as $format) $this->get(route('biblioteca.export',[$v['id'],$format]))->assertNotFound();
            }
            $this->get(route('biblioteca.show',$valid['document_id']))->assertOk();
        }
        $this->actingAs(User::findOrFail(1));
        $this->get(route('biblioteca.show',$draft['document_id']))->assertOk();
        $this->get(route('biblioteca.show',$imported['document_id']))->assertOk();
    }

    public function test_reader_history_contains_only_the_current_validated_version(): void
    {
        $library=new Library;
        $first=$this->job('Puesto con historial',true);
        $second=$library->draft($first['id'],'test');
        $second=$library->publish($second['id'],0,'Cambio aprobado',true,'test');
        $draft=$library->draft($second['id'],'test');
        $this->actingAs(User::findOrFail(2));
        $this->get(route('biblioteca.show',$first['document_id']))->assertOk()
            ->assertViewHas('history',fn($history)=>count($history)===1 && $history[0]['id']===$second['id']);
        foreach ([$first,$draft] as $v) {
            $this->get(route('biblioteca.show',['document'=>$v['document_id'],'version'=>$v['id']]))->assertNotFound();
            $this->get(route('biblioteca.export',[$v['id'],'pdf']))->assertNotFound();
        }
        $this->actingAs(User::findOrFail(1));
        $this->get(route('biblioteca.show',$first['document_id']))->assertOk()->assertViewHas('history',fn($history)=>count($history)===3);
    }

    public function test_all_publication_prerequisites_are_required(): void
    {
        $v=$this->job('Puesto para validación',true);
        $this->actingAs(User::findOrFail(2));
        foreach ([['review_state'=>'Pendiente'],['published_at'=>null],['current_slot'=>null],['state'=>'Histórico / retirado']] as $change) {
            DB::table('bib_versions')->where('id',$v['id'])->update($change);
            $this->get(route('biblioteca.show',$v['document_id']))->assertNotFound();
            $this->get(route('biblioteca.export',[$v['id'],'docx']))->assertNotFound();
            DB::table('bib_versions')->where('id',$v['id'])->update(array_intersect_key($v,$change));
        }
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk();
    }

    public function test_management_hides_retired_documents_until_history_is_requested(): void
    {
        $retired=$this->job('Base anterior retirada',true);
        (new Library)->retire($retired['id'],$retired['revision'],'Nueva base de trabajo','test');
        $this->job('Nueva base en revisión');
        $this->get(route('biblioteca.manage'))->assertOk()->assertSee('Nueva base en revisión')->assertDontSee('Base anterior retirada');
        $this->get(route('biblioteca.manage',['history'=>1]))->assertOk()->assertSee('Nueva base en revisión')->assertSee('Base anterior retirada');
        $this->get(route('biblioteca.show',$retired['document_id']))->assertOk();
    }
    public function test_preview_obeys_target_permissions_without_losing_real_admin_identity(): void
    {
        $draft=$this->job('Borrador privado para Gerencia');
        $valid=$this->job('Vigente para todos',true);
        $this->post(route('biblioteca.preview.start'),['user_id'=>2])->assertRedirect();
        $this->get(route('biblioteca.show',$draft['document_id']))->assertNotFound();
        $this->get(route('biblioteca.export',[$draft['id'],'pdf']))->assertNotFound();
        $this->get(route('biblioteca.show',$valid['document_id']))->assertOk();
        $this->assertSame(1,auth()->id());
        $this->post(route('biblioteca.preview.stop'))->assertRedirect();
        $this->get(route('biblioteca.show',$draft['document_id']))->assertOk();
    }
}