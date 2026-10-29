<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Biblioteca\{Content,Library,SectorCatalog};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Route,Schema};
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaSectorCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array']);DB::purge();
        foreach(['2026_09_18_150000_create_biblioteca_tables.php','2026_09_21_010000_add_biblioteca_policy_governance.php'] as $migration)(require database_path('migrations/'.$migration))->up();
        Schema::create('areas',function(Blueprint $table){$table->integer('ID_AREA')->primary();$table->string('NOMBRE');$table->integer('ESTADO')->default(1);});
        DB::table('areas')->insert([
            ['ID_AREA'=>1,'NOMBRE'=>'Administración','ESTADO'=>1],
            ['ID_AREA'=>2,'NOMBRE'=>'Farmacia','ESTADO'=>1],
            ['ID_AREA'=>3,'NOMBRE'=>'Administración','ESTADO'=>1],
            ['ID_AREA'=>4,'NOMBRE'=>'Área retirada','ESTADO'=>0],
        ]);
        if(!Route::has('biblioteca.index'))Route::middleware('web')->group(base_path('routes/biblioteca.php'));Route::getRoutes()->refreshNameLookups();
        $admin=new User(['name'=>'Administradora de prueba','username'=>'prueba','rol'=>'Administrador/a']);$admin->id=99;$this->actingAs($admin);
    }

    private function job(string $sector=''): array
    {
        $content=Content::emptyJob();$content['name']='Puesto de prueba';$content['area']='Administración';$content['sector']=$sector;
        $content['groups']['purpose']=[Content::paragraph('Propósito conservado.')];$content['groups']['tasks']=[Content::paragraph('Tarea conservada.',true)];
        return $content;
    }

    public function test_editor_uses_active_unique_areas_and_retains_unlisted_literal_without_mutating_the_draft(): void
    {
        $library=new Library;$v=$library->create('descriptivos',$this->job('Honorarios médicos'),'test',(string)Str::uuid());
        $this->assertSame(['Administración','Farmacia'],(new SectorCatalog)->names());
        $response=$this->get(route('biblioteca.edit',$v['id']))->assertOk()->assertSee('id="bib-sector" data-field="sector"',false)->assertSee('Honorarios médicos — valor actual fuera del catálogo activo');
        $dom=new \DOMDocument;@$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());$xpath=new \DOMXPath($dom);
        $options=$xpath->query('//select[@id="bib-sector"]/option');$this->assertSame(4,$options->length);
        $this->assertSame('Honorarios médicos',$xpath->query('//select[@id="bib-sector"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertSame($v['payload_json'],$library->version($v['id'])['payload_json']);
        $content=Content::decode($v['payload_json']);$content['name']='Nombre revisado';
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>0,'content'=>$content])->assertOk();
        $this->assertSame('Honorarios médicos',Content::decode($library->version($v['id'])['payload_json'])['sector']);
        DB::table('areas')->where('ID_AREA',2)->update(['NOMBRE'=>'Farmacia actualizada']);
        $this->get(route('biblioteca.edit',$v['id']))->assertOk()->assertSee('Farmacia actualizada');
    }

    public function test_save_accepts_catalog_selection_rejects_unlisted_or_inactive_changes_and_preserves_current_inactive_value(): void
    {
        $library=new Library;$v=$library->create('descriptivos',$this->job('Área retirada'),'test',(string)Str::uuid());$content=Content::decode($v['payload_json']);
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>0,'content'=>$content])->assertOk();
        $content['sector']='Farmacia';$this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>1,'content'=>$content])->assertOk();
        $current=$library->version($v['id']);$this->assertSame('Farmacia',Content::decode($current['payload_json'])['sector']);
        foreach(['Área retirada','Texto libre inventado'] as $invalid){$content['sector']=$invalid;$this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>2,'content'=>$content])->assertUnprocessable();}
        $this->assertSame($current,$library->version($v['id']));
    }

    public function test_new_documents_require_a_catalog_sector_but_duplicates_can_keep_their_trusted_source_literal(): void
    {
        $content=$this->job('Literal importado');
        $this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'content'=>$content])->assertUnprocessable();
        $source=(new Library)->create('descriptivos',$content,'test',(string)Str::uuid());
        $this->get(route('biblioteca.new',['kind'=>'descriptivos','duplicate'=>$source['id']]))->assertOk()->assertSee('"sourceVersion":"'.$source['id'].'"',false);
        $copy=$this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'content'=>$content,'sourceVersion'=>$source['id']])->assertOk();
        $this->assertSame('Literal importado',Content::decode((new Library)->version($copy->json('version'))['payload_json'])['sector']);
        $content['sector']='Otro texto';$this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'content'=>$content,'sourceVersion'=>$source['id']])->assertUnprocessable();
        $content['sector']='Administración';$this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'content'=>$content])->assertOk();
    }

    public function test_new_version_and_import_preserve_only_their_server_source_sector(): void
    {
        $content=$this->job('Sector literal');$library=new Library;$v=$library->create('descriptivos',$content,'test',(string)Str::uuid());
        $response=$this->postJson(route('biblioteca.action',$v['id']),['action'=>'new_version','revision'=>0,'requestId'=>(string)Str::uuid(),'content'=>$content])->assertOk();
        $this->assertSame('Sector literal',Content::decode($library->version($response->json('version'))['payload_json'])['sector']);
        $id=(string)Str::uuid();$stage=['id'=>$id,'name'=>'fuente.docx','format'=>'DOCX','sha256'=>str_repeat('a',64),'snapshot'=>'originales/fuente.docx','parsed'=>Content::toParsed($content),'error'=>null];
        DB::table('bib_uploads')->insert(['id'=>$id,'actor'=>'test','created_at'=>Library::now(),'payload_json'=>Content::json($stage)]);
        $changed=$content;$changed['sector']='Valor ajeno';
        $this->postJson(route('biblioteca.upload.confirm',$id),['destination'=>'new','content'=>$changed])->assertUnprocessable();
        $this->postJson(route('biblioteca.upload.confirm',$id),['destination'=>'new','content'=>$content])->assertOk();
        $version=DB::table('bib_uploads')->where('id',$id)->value('version_id');
        $this->assertSame('Sector literal',Content::decode($library->version($version)['payload_json'])['sector']);
    }

    public function test_module_without_catalog_keeps_existing_values_and_older_catalog_schemas_are_supported(): void
    {
        Schema::table('areas',fn(Blueprint $table)=>$table->dropColumn('ESTADO'));
        $this->assertContains('Farmacia',(new SectorCatalog)->names());
        Schema::drop('areas');$this->assertSame([],(new SectorCatalog)->names());
        $v=(new Library)->create('descriptivos',$this->job('Valor previo'),'test',(string)Str::uuid());
        $this->get(route('biblioteca.edit',$v['id']))->assertOk()->assertSee('Valor previo — valor actual fuera del catálogo activo');
    }
}