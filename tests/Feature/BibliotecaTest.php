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
        (require database_path('migrations/2026_09_21_010000_add_biblioteca_policy_governance.php'))->up();
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
        $this->assertSame(['Otro texto'],array_column($clean['groups']['generic'],'text'));$this->assertSame('bullet',$clean['groups']['commitment'][0]['list']['format']);
        $html=Content::sanitize('<p onclick="alert(1)">Texto</p><script>alert(1)</script><a href="javascript:alert(1)">enlace</a><table><tr><td colspan="2">Celda</td></tr></table>');
        foreach(['onclick','script','javascript:'] as $bad)$this->assertStringNotContainsString($bad,$html);$this->assertStringContainsString('colspan="2"',$html);
    }
    public function test_migration_preserves_all_versions_records_files_and_views():void {
        $bundle=getenv('BIBLIOTECA_TEST_BUNDLE');$this->assertNotFalse($bundle,'Configurar BIBLIOTECA_TEST_BUNDLE.');$source=Content::decode(file_get_contents($bundle));$report=(new Importer)->import($bundle);
        $this->assertSame(count($source['tables']['candidates'])+count($source['tables']['institutional_documents']),$report['documents']);$this->assertSame(count($source['tables']['managed_versions'])+count($source['tables']['institutional_versions']),$report['versions']);
        $this->mock(\App\Services\Biblioteca\Acceptances::class,fn($mock)=>$mock->shouldReceive('context')->andReturn(['canSign'=>false]));$this->assertTrue((new Importer)->import($bundle)['alreadyImported']);$this->get(route('biblioteca.index'))->assertOk()->assertSee('Biblioteca Institucional');
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
    private function publishedDocument(string $kind): array {
        $lib=new Library;
        if($kind==='descriptivos') {
            $c=$this->job();
        } else {
            $c=$this->manual();$c['collection']=$kind;$c['code']='TEST-'.strtoupper($kind);
            $c['validFrom']='2026-01-01';$c['approver']='Dirección';$c['approvalRecord']='Acta original';
        }
        $v=$lib->create($kind,$c,'Autor original',(string)Str::uuid());
        if($kind==='politicas')config(['biblioteca.policy_approver_username'=>auth()->user()->username]);
        return $lib->publish($v['id'],0,'Publicación original',true,'Autor original',false,$kind==='politicas');
    }
    public function test_edit_and_save_new_version_for_every_collection_without_overwriting_original():void {
        $lib=new Library;
        foreach(['politicas','procedimientos','instructivos','descriptivos'] as $kind) {
            $original=$this->publishedDocument($kind);$before=DB::table('bib_versions')->count();
            $this->get(route('biblioteca.show',$original['document_id']))->assertOk()->assertSee('Editar y crear nueva versión');
            $this->get(route('biblioteca.edit',$original['id']))->assertOk()->assertSee('Guardar nueva versión');
            $this->assertSame($before,DB::table('bib_versions')->count(),'Abrir el editor no crea versiones.');
            $content=$lib->contentForNewVersion($original);
            if($kind==='descriptivos')$content['groups']['tasks'][]=Content::paragraph("Nueva tarea con tildes.\nSegundo renglón.");
            else {
                $this->assertSame('1.1',$content['version']);
                $this->assertNull($content['approvalRecord']);$this->assertSame('',$content['approver']);
                $content['sections'][0]['html'].='<p>Nueva instrucción con tildes.</p>';
            }
            $body=['action'=>'new_version','revision'=>$original['revision'],'requestId'=>(string)Str::uuid(),'reason'=>'Actualizar el circuito de trabajo','content'=>$content];
            $response=$this->postJson(route('biblioteca.action',$original['id']),$body)->assertOk()->assertJsonPath('number',2);
            $saved=$lib->version($response->json('version'));
            $this->assertSame($original,$lib->version($original['id']));
            $this->assertSame($original['document_id'],$saved['document_id']);
            $this->assertSame($original['id'],$saved['based_on']);$this->assertSame('Borrador',$saved['state']);
            $this->assertNull($saved['published_at']);$this->assertNull($saved['current_slot']);
            $this->assertSame('Actualizar el circuito de trabajo',$saved['change_reason']);
            $this->assertStringContainsString('Prueba Biblioteca',$saved['created_by']);
            $this->assertSame($original['id'],$lib->entry($original['document_id'])['version']['id']);
            $this->postJson(route('biblioteca.action',$original['id']),$body)->assertOk()->assertJsonPath('version',$saved['id']);
            $changedRetry=$body;$changedRetry['reason']='Otro cambio después del reintento';
            $this->postJson(route('biblioteca.action',$original['id']),$changedRetry)->assertStatus(409);
            $this->assertSame($saved,$lib->version($saved['id']));
            $this->assertSame($before+1,DB::table('bib_versions')->count(),'Reintentar no duplica una versión.');
            $this->assertSame(1,DB::table('bib_events')->where('version_id',$saved['id'])->where('action','guardar_nueva_version')->count());
            $this->get(route('biblioteca.show',$original['document_id']))->assertOk()->assertSee('Continuar borrador')->assertSee('Actualizar el circuito de trabajo');
            $this->getJson(route('biblioteca.catalog',$kind))->assertOk()->assertSee('Editar');
            $updated=Content::decode($saved['payload_json']);
            if($kind!=='descriptivos') {
                $this->assertStringContainsString('<table>',$updated['sections'][0]['html']);
                if($kind==='politicas') {
                    $this->assertSame(0,DB::table('bib_policy_approvals')->where('version_id',$saved['id'])->count());
                    $this->postJson(route('biblioteca.action',$saved['id']),['action'=>'publish','revision'=>0,'confirmed'=>true])->assertForbidden();
                } else {
                    $updated['approver']='Dirección';$updated['approvalRecord']='Acta nueva';
                    $saved=$lib->save($saved['id'],$saved['revision'],$updated,'Revisor');
                }
            }
            $published=$lib->publish($saved['id'],$saved['revision'],'',true,'Revisor',false,$kind==='politicas');
            $this->assertSame('Actualizar el circuito de trabajo',$published['change_reason']);
            $this->assertSame($original['payload_json'],$lib->version($original['id'])['payload_json']);
            $this->assertSame('Histórico / retirado',$lib->version($original['id'])['state']);
            $this->assertSame($published['id'],$lib->entry($original['document_id'])['version']['id']);
            $this->assertSame(1,DB::table('bib_versions')->where('document_id',$original['document_id'])->where('current_slot',1)->count());
            if($kind!=='descriptivos')$this->assertSame('1.2',$lib->contentForNewVersion($original)['version']);
        }
    }
    public function test_new_version_validation_conflicts_and_permissions_leave_no_partial_data():void {
        $lib=new Library;$original=$this->publishedDocument('procedimientos');
        $content=$lib->contentForNewVersion($original);
        $body=['action'=>'new_version','revision'=>$original['revision'],'requestId'=>(string)Str::uuid(),'reason'=>'Cambio','content'=>$content];
        $beforeVersions=DB::table('bib_versions')->count();$beforeEvents=DB::table('bib_events')->count();
        $invalid=$body;$invalid['content']['code']='OTRO-CODIGO';
        $this->postJson(route('biblioteca.action',$original['id']),$invalid)->assertStatus(422);
        $invalid=$body;$invalid['content']['title']='';
        $this->postJson(route('biblioteca.action',$original['id']),$invalid)->assertStatus(422);
        $invalid=$body;unset($invalid['requestId']);
        $this->postJson(route('biblioteca.action',$original['id']),$invalid)->assertStatus(422);
        $stale=$body;$stale['revision']=0;
        $this->postJson(route('biblioteca.action',$original['id']),$stale)->assertStatus(409);
        $this->assertSame($beforeVersions,DB::table('bib_versions')->count());$this->assertSame($beforeEvents,DB::table('bib_events')->count());
        $this->assertSame($original,$lib->version($original['id']));
        $reader=new User(['name'=>'Lector','rol'=>'Colaborador/a']);$reader->id=100;$this->actingAs($reader);
        $this->get(route('biblioteca.edit',$original['id']))->assertForbidden();
        $this->postJson(route('biblioteca.action',$original['id']),$body)->assertForbidden();
        $this->get(route('biblioteca.show',$original['document_id']))->assertOk()->assertDontSee('Editar y crear nueva versión');
        $this->getJson(route('biblioteca.catalog','procedimientos'))->assertOk()->assertDontSee('/editar');
        $this->assertSame($beforeVersions,DB::table('bib_versions')->count());
    }
    public function test_draft_resave_keeps_its_number_reason_and_rejects_stale_edits():void {
        $lib=new Library;$original=$this->publishedDocument('descriptivos');$c=$lib->contentForNewVersion($original);
        $saved=$lib->saveNewVersion($original['id'],$original['revision'],$c,'Primera modificación','Editor',(string)Str::uuid());
        $this->get(route('biblioteca.edit',$saved['id']))->assertOk()->assertSee('Guardar borrador')->assertDontSee('Guardar nueva versión');
        DB::table('bib_versions')->where('id',$saved['id'])->update(['review_state'=>'Validado','resolution'=>'Revisión previa']);
        $c['groups']['tasks'][]=Content::paragraph('Otra tarea.');
        $body=['action'=>'save','revision'=>0,'content'=>$c,'reason'=>'Cambio revisado'];
        $this->postJson(route('biblioteca.action',$saved['id']),$body)->assertOk()->assertJsonPath('number',2)->assertJsonPath('revision',1);
        $current=$lib->version($saved['id']);$this->assertSame('Pendiente',$current['review_state']);$this->assertSame('',$current['resolution']);
        $this->assertSame('Cambio revisado',$current['change_reason']);
        $this->postJson(route('biblioteca.action',$saved['id']),$body)->assertStatus(409);
        $this->assertSame($current,$lib->version($saved['id']));$this->assertSame(2,DB::table('bib_versions')->count());
        $this->assertSame($original,$lib->version($original['id']));
    }
    public function test_imported_and_historical_versions_can_be_used_without_mutating_sources():void {
        $lib=new Library;$base=$lib->create('descriptivos',$this->job(),'Importador',(string)Str::uuid());
        DB::table('bib_versions')->where('id',$base['id'])->update(['origin'=>'imported','state'=>'Vigencia no verificada','source_version_id'=>'fuente-original']);
        $original=$lib->version($base['id']);
        $this->get(route('biblioteca.edit',$base['id']))->assertOk()->assertSee('Guardar nueva versión');
        $next=$lib->saveNewVersion($base['id'],0,$lib->contentForNewVersion($original),'Corrección','Editor',(string)Str::uuid());
        $this->assertSame('fuente-original',$next['source_version_id']);$this->assertSame($original,$lib->version($base['id']));
        $historical=$lib->retire($base['id'],0,'Fuente anterior','Editor');
        $this->get(route('biblioteca.edit',$base['id']))->assertOk()->assertSee('Guardar nueva versión');
        $restored=$lib->saveNewVersion($base['id'],$historical['revision'],$lib->contentForNewVersion($historical),'Recuperar contenido','Editor',(string)Str::uuid());
        $this->assertSame(3,$restored['number']);$this->assertSame($historical,$lib->version($base['id']));
    }

    public function test_custom_competencies_and_emojis_survive_saving_publishing_and_new_versions():void {
        $lib=new Library;$c=$this->job();
        $this->assertSame(config('biblioteca.competencies'),array_column($c['groups']['generic'],'text'));
        $c['groups']['generic']=[Content::paragraph('Comunicación del equipo 🤝',true)];
        $v=$lib->create('descriptivos',$c,'test',(string)Str::uuid());
        $published=$lib->publish($v['id'],0,'',true,'test');
        $this->assertSame('Comunicación del equipo 🤝',Content::decode($published['payload_json'])['groups']['generic'][0]['text']);
        $this->get(route('biblioteca.show',$v['document_id']))->assertSee('Comunicación del equipo 🤝')->assertDontSee('Demuestra altos estándares');
        $draft=$lib->draft($v['id'],'test');$content=Content::decode($draft['payload_json']);
        $this->assertSame($c['groups']['generic'],$content['groups']['generic']);
        $content['groups']['generic']=[];$saved=$lib->save($draft['id'],0,$content,'test');
        $this->assertSame([],Content::decode($saved['payload_json'])['groups']['generic']);
        $this->assertSame([],$lib->contentForNewVersion($saved)['groups']['generic']);
        $manual=$this->manual();$manual['sections'][0]['html']='<h3>Título</h3><p><u>Subrayado</u> <strike>Tachado</strike> 😊 <a href="https://example.com">Enlace</a></p><blockquote>Destacado</blockquote>';
        $validated=Content::validateManual($manual);
        foreach(['<h3>','<u>','<strike>','😊','<blockquote>','href="https://example.com"'] as $markup)$this->assertStringContainsString($markup,$validated['sections'][0]['html']);
    }
    public function test_catalog_page_sizes_and_sorting_apply_before_pagination():void {
        $lib=new Library;
        for($i=1;$i<=55;$i++){
            $content=$this->job();$content['name']='Puesto '.$i;$content['area']=$i%2?'Área Z':'Área A';
            $v=$lib->create('descriptivos',$content,'test',(string)Str::uuid());
            DB::table('bib_versions')->where('id',$v['id'])->update(['number'=>$i,'review_state'=>$i%2?'Validado':'Pendiente']);
        }
        foreach(['biblioteca.control','biblioteca.manage','biblioteca.search'] as $route){
            $this->get(route($route))->assertOk()->assertViewHas('entries',fn($p)=>$p->count()===25&&$p->perPage()===25);
            foreach([25,50,100,150] as $size)$this->get(route($route,['per_page'=>$size]))->assertOk()->assertViewHas('entries',fn($p)=>$p->count()===min(55,$size)&&$p->perPage()===$size);
        }
        $this->get(route('biblioteca.search',['per_page'=>999]))->assertOk()->assertViewHas('entries',fn($p)=>$p->perPage()===25);
        $this->get(route('biblioteca.search',['sort'=>'title','direction'=>'desc','page'=>2]))->assertOk()->assertViewHas('entries',fn($p)=>$p->items()[0]['title']==='Puesto 30');
        $this->get(route('biblioteca.search',['sort'=>'version','direction'=>'desc']))->assertOk()->assertViewHas('entries',fn($p)=>$p->items()[0]['version']['number']===55);
        $this->get(route('biblioteca.search',['sort'=>'area','direction'=>'asc']))->assertOk()->assertViewHas('entries',fn($p)=>$p->items()[0]['area']==='Área A');
        $this->get(route('biblioteca.search',['sort'=>'review','direction'=>'asc']))->assertOk()->assertViewHas('entries',fn($p)=>$p->items()[0]['version']['review_state']==='Pendiente');
        $html=$this->getJson(route('biblioteca.search',['q'=>'Puesto','per_page'=>50,'sort'=>'version','direction'=>'desc']))->assertOk()->json('html');
        $this->assertStringContainsString('Página 1 de 2',$html);$this->assertStringContainsString('per_page=50',$html);
        $this->assertStringContainsString('aria-sort="descending"',$html);
    }


    public function test_rich_job_blocks_survive_save_reading_and_word_without_unsafe_html():void {
        $c=$this->job();$c['groups']['purpose'][0]['html']='<b>Propósito enriquecido</b><br><i>Atención</i> <u>segura</u> <s>anterior</s> ✅<script>alert(1)</script><a href="javascript:alert(1)">enlace</a>';
        $v=(new Library)->create('descriptivos',$c,'test',(string)Str::uuid());
        $saved=Content::decode($v['payload_json'])['groups']['purpose'][0];
        $this->assertStringContainsString('<b>Propósito enriquecido</b>',$saved['html']);
        $this->assertStringContainsString("Propósito enriquecido\nAtención segura anterior ✅enlace",$saved['text']);
        foreach(['script','javascript:','alert(1)'] as $bad)$this->assertStringNotContainsString($bad,$saved['html']);
        $this->get(route('biblioteca.show',$v['document_id']))->assertOk()->assertSee('<b>Propósito enriquecido</b>',false);
        $this->get(route('biblioteca.edit',$v['id']))->assertOk()->assertSee('Eliminar borrador');
        $word=$this->get(route('biblioteca.export',[$v['id'],'docx']))->assertOk();$path=$word->baseResponse->getFile()->getPathname();
        $zip=new \ZipArchive;$zip->open($path);$xml=$zip->getFromName('word/document.xml');$zip->close();unlink($path);
        foreach(['Propósito enriquecido','Atención','✅','<w:b/>','<w:i/>','<w:u w:val="single"/>','<w:strike/>','<w:br/>'] as $expected)$this->assertStringContainsString($expected,$xml);
        $dom=new \DOMDocument;$this->assertTrue($dom->loadXML($xml));
    }
    public function test_delete_draft_preserves_previous_version_is_idempotent_and_does_not_reuse_numbers():void {
        $lib=new Library;
        foreach(['politicas','procedimientos','instructivos','descriptivos'] as $kind){
            $base=$this->publishedDocument($kind);$before=$lib->version($base['id']);
            $request=(string)Str::uuid();$draft=$lib->saveNewVersion($base['id'],$base['revision'],$lib->contentForNewVersion($base),'Cambio','test',$request);
            $action=route('biblioteca.action',$draft['id']);
            $this->postJson($action,['action'=>'delete_draft','revision'=>0])->assertStatus(422);
            $this->postJson($action,['action'=>'delete_draft','revision'=>99,'confirmed'=>true])->assertConflict();
            $deleted=$this->postJson($action,['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertOk()->assertJson(['deleted'=>true])->json();
            $this->assertStringContainsString($base['id'],$deleted['url']);
            $this->assertFalse(DB::table('bib_versions')->where('id',$draft['id'])->exists());
            $this->assertSame($before,$lib->version($base['id']));
            $this->postJson($action,['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertOk()->assertExactJson($deleted);
            $this->postJson(route('biblioteca.action',$base['id']),['action'=>'new_version','revision'=>$base['revision'],'requestId'=>$request,'content'=>$lib->contentForNewVersion($base)])->assertConflict();
            $next=$lib->draft($base['id'],'test');$this->assertSame($draft['number']+1,$next['number']);
            $this->postJson(route('biblioteca.action',$base['id']),['action'=>'delete_draft','revision'=>$base['revision'],'confirmed'=>true])->assertStatus(422);
        }
    }
    public function test_deleting_the_only_new_draft_removes_document_but_retains_audit_and_request():void {
        $lib=new Library;$request=(string)Str::uuid();$draft=$lib->create('descriptivos',$this->job(),'test',$request);
        $this->postJson(route('biblioteca.action',$draft['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertOk()->assertJson(['url'=>route('biblioteca.manage')]);
        $this->assertFalse(DB::table('bib_documents')->where('id',$draft['document_id'])->exists());
        $this->assertTrue(DB::table('bib_events')->where('version_id',$draft['id'])->where('action','eliminar_borrador')->exists());
        $this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>$request,'content'=>$this->job()])->assertConflict();
    }

    public function test_draft_deletion_rejects_readers_and_dependent_versions():void {
        $lib=new Library;$base=$this->publishedDocument('descriptivos');$draft=$lib->draft($base['id'],'test');
        $reader=new User(['name'=>'Lector','rol'=>'Colaborador/a']);$reader->id=100;$this->actingAs($reader);
        $this->postJson(route('biblioteca.action',$draft['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertForbidden();
        $admin=new User(['name'=>'Admin','rol'=>'Administrador/a']);$admin->id=99;$this->actingAs($admin);
        $child=$lib->saveNewVersion($draft['id'],0,Content::decode($draft['payload_json']),'Versión dependiente','test',(string)Str::uuid());
        $this->postJson(route('biblioteca.action',$draft['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertConflict();
        $this->assertTrue(DB::table('bib_versions')->where('id',$draft['id'])->exists());
        $this->postJson(route('biblioteca.action',$child['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertOk();
        $this->postJson(route('biblioteca.action',$draft['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertOk();
        $imported=$lib->draft($base['id'],'test');DB::table('bib_versions')->where('id',$imported['id'])->update(['origin'=>'imported']);
        $this->postJson(route('biblioteca.action',$imported['id']),['action'=>'delete_draft','revision'=>0,'confirmed'=>true])->assertStatus(422);
    }
}
