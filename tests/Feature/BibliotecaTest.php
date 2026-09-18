<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\Biblioteca\{Content,Importer,Library};
use Illuminate\Support\Facades\{DB,File,Route};
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaTest extends TestCase {
    private string $storage;
    protected function setUp():void {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array']);DB::purge();
        $this->storage=storage_path('framework/testing/biblioteca-'.Str::uuid());config(['biblioteca.storage'=>$this->storage]);
        (require database_path('migrations/2026_09_18_150000_create_biblioteca_tables.php'))->up();
        if(!Route::has('biblioteca.index'))Route::middleware('web')->group(base_path('routes/biblioteca.php'));Route::getRoutes()->refreshNameLookups();
        $user=new User(['name'=>'Prueba Biblioteca','username'=>'prueba','rol'=>'Administrador/a']);$user->id=99;$this->actingAs($user);
    }
    protected function tearDown():void {if(isset($this->storage)&&str_starts_with($this->storage,storage_path('framework/testing').DIRECTORY_SEPARATOR.'biblioteca-'))File::deleteDirectory($this->storage);parent::tearDown();}
    private function job():array {$c=Content::institutional(Content::emptyJob());$c['name']='Auxiliar de prueba';$c['area']='RRHH';$c['groups']['purpose']=[Content::paragraph('Acompañar a las personas.')];$c['groups']['tasks']=[Content::paragraph('Atender consultas.',true)];$c['groups']['commitment']=[Content::paragraph('Cumplir los protocolos.')];return $c;}
    private function manual():array {return ['id'=>'','sourceId'=>'','collection'=>'instructivos','code'=>'INS-TEST','title'=>'Guía de prueba','sourceHeading'=>'','summary'=>'Resumen','version'=>'1.0','declaredState'=>'borrador','validFrom'=>null,'lastReview'=>null,'reviewMonths'=>12,'approver'=>'','approvalRecord'=>null,'responsible'=>'Calidad','history'=>[],'sections'=>[['id'=>'s1','title'=>'Pasos','html'=>'<p>Revisar el equipo.</p><table><tr><td>Responsable</td><td>Enfermería</td></tr></table>','text'=>'']]];}
    public function test_job_publication_history_and_conflict():void {
        $lib=new Library;$v=$lib->create('descriptivos',$this->job(),'test',(string)Str::uuid());$published=$lib->publish($v['id'],0,'Prueba',true,'test');$old=$published['payload_json'];
        $draft=$lib->draft($v['id'],'test');$c=Content::decode($draft['payload_json']);$c['name']='Nombre posterior';$saved=$lib->save($draft['id'],0,$c,'test');
        $this->assertSame($v['id'],$lib->entry($v['document_id'])['version']['id']);
        $this->postJson(route('biblioteca.action',$draft['id']),['action'=>'save','revision'=>0,'content'=>$c])->assertStatus(409);
        $next=$lib->publish($draft['id'],$saved['revision'],'Cambio',true,'test');$this->assertSame('Histórico / retirado',$lib->version($v['id'])['state']);$this->assertSame($old,$lib->version($v['id'])['payload_json']);
        $this->assertSame($next['id'],$lib->entry($v['document_id'])['version']['id']);
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>$lib->version($v['id'])['revision'],'content'=>$c])->assertStatus(422);
        $this->assertSame(1,DB::table('bib_versions')->where('current_slot',1)->count());
    }
    public function test_permissions_and_authentication():void {
        $v=(new Library)->create('descriptivos',$this->job(),'test',(string)Str::uuid());$reader=new User(['name'=>'Lector','rol'=>'Colaborador/a']);$reader->id=100;$this->actingAs($reader);
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk()->assertDontSee('Crear borrador para editar');
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'draft'])->assertForbidden();$this->get(route('biblioteca.new'))->assertForbidden();
        auth()->logout();$this->get(route('biblioteca.index'))->assertRedirect(route('login'));
    }
    public function test_manual_requires_approval_and_retains_tables():void {
        $lib=new Library;$v=$lib->create('instructivos',$this->manual(),'test',(string)Str::uuid());
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'publish','revision'=>0,'confirmed'=>true])->assertStatus(422);
        $c=Content::decode($v['payload_json']);$c['validFrom']='2099-01-01';$c['approver']='Dirección';$c['approvalRecord']='Acta de prueba';$v=$lib->save($v['id'],0,$c,'test');
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'publish','revision'=>$v['revision'],'confirmed'=>true])->assertStatus(422);
        $c['validFrom']='2026-01-01';$v=$lib->save($v['id'],$v['revision'],$c,'test');$v=$lib->publish($v['id'],$v['revision'],'Aprobado',true,'test');
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk()->assertSee('Enfermería')->assertSee('<table',false);
        $lib->retire($v['id'],$v['revision'],'Reemplazado','test');$this->assertSame('Histórico / retirado',$lib->version($v['id'])['state']);
    }
    public function test_simplified_review_and_concurrency():void {
        $v=(new Library)->create('descriptivos',$this->job(),'test',(string)Str::uuid());
        $this->postJson(route('biblioteca.review.save',$v['id']),['action'=>'validate','revision'=>0,'documentRevision'=>0,'canonical_name'=>'Auxiliar','normalized_area'=>'Recursos Humanos','confirmed'=>true,'comment'=>''])->assertOk();
        $this->assertSame('Validado',(new Library)->version($v['id'])['review_state']);
        $this->postJson(route('biblioteca.review.save',$v['id']),['action'=>'save','revision'=>1,'documentRevision'=>0])->assertStatus(409);
    }
    public function test_competencies_and_html_sanitization():void {
        $c=$this->job();$c['groups']['generic']=[Content::paragraph('Otro texto')];$clean=Content::validateJob($c);
        $this->assertSame(config('biblioteca.competencies'),array_column($clean['groups']['generic'],'text'));$this->assertSame('bullet',$clean['groups']['commitment'][0]['list']['format']);
        $html=Content::sanitize('<p onclick="alert(1)">Texto</p><script>alert(1)</script><a href="javascript:alert(1)">enlace</a><table><tr><td colspan="2">Celda</td></tr></table>');
        foreach(['onclick','script','javascript:'] as $bad)$this->assertStringNotContainsString($bad,$html);$this->assertStringContainsString('colspan="2"',$html);
    }
    public function test_migration_preserves_all_versions_records_files_and_views():void {
        $bundle=getenv('BIBLIOTECA_TEST_BUNDLE');$this->assertNotFalse($bundle,'Configurar BIBLIOTECA_TEST_BUNDLE.');$source=Content::decode(file_get_contents($bundle));$report=(new Importer)->import($bundle);
        $this->assertSame(count($source['tables']['candidates'])+count($source['tables']['institutional_documents']),$report['documents']);$this->assertSame(count($source['tables']['managed_versions'])+count($source['tables']['institutional_versions']),$report['versions']);
        $this->assertTrue((new Importer)->import($bundle)['alreadyImported']);$this->get(route('biblioteca.index'))->assertOk()->assertSee('Biblioteca Institucional');
        $library=new Library;foreach($library->entries() as $entry){$this->get(route('biblioteca.show',$entry['document']['id']))->assertOk();if($entry['version']['state']==='Borrador')$this->get(route('biblioteca.edit',$entry['version']['id']))->assertOk();if($entry['job'])$this->get(route('biblioteca.review',$entry['version']['id']))->assertOk();}
        $original=$source['tables']['managed_versions'][0];$this->assertSame($original['parsed_json'],$library->version($original['id'])['parsed_json']);
        $this->get(route('biblioteca.control'))->assertOk();$this->get(route('biblioteca.search',['q'=>'ética']))->assertOk();$this->get(route('biblioteca.new'))->assertOk();$this->get(route('biblioteca.new',['kind'=>'politicas']))->assertOk();
    }
    public function test_pdf_and_word_exports():void {
        $v=(new Library)->create('descriptivos',$this->job(),'test',(string)Str::uuid());$pdf=$this->get(route('biblioteca.export',[$v['id'],'pdf']))->assertOk();$this->assertStringStartsWith('%PDF',$pdf->getContent());
        $word=$this->get(route('biblioteca.export',[$v['id'],'docx']))->assertOk();$path=$word->baseResponse->getFile()->getPathname();$zip=new \ZipArchive;$this->assertTrue($zip->open($path));$xml=$zip->getFromName('word/document.xml');$this->assertStringContainsString('Ética profesional',$xml);$this->assertStringContainsString('w:numPr',$xml);$zip->close();unlink($path);
    }
    public function test_creation_idempotency_and_fixed_identity():void {
        $lib=new Library;$request=(string)Str::uuid();$v=$lib->create('instructivos',$this->manual(),'test',$request);$this->assertSame($v['id'],$lib->create('instructivos',$this->manual(),'test',$request)['id']);
        $c=Content::decode($v['payload_json']);$c['code']='OTRO-CODIGO';$this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>0,'content'=>$c])->assertStatus(422);
    }
}
