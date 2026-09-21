<?php
namespace Tests\Feature;

use App\Models\User;
use App\Services\Biblioteca\{Content,Importer,Library,Synchronizer,Uploads,WordExport};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB,File,Route};
use Illuminate\Support\Str;
use Tests\TestCase;

class BibliotecaImportTest extends TestCase {
    private string $storage;
    protected function setUp():void {
        parent::setUp();
        $this->storage=storage_path('framework/testing/biblioteca-'.Str::uuid());
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array','biblioteca.storage'=>$this->storage,'biblioteca.python'=>getenv('BIBLIOTECA_TEST_PYTHON')?:'python']);DB::purge();
        (require database_path('migrations/2026_09_18_150000_create_biblioteca_tables.php'))->up();
        (require database_path('migrations/2026_09_21_010000_add_biblioteca_policy_governance.php'))->up();
        if(!Route::has('biblioteca.index'))Route::middleware('web')->group(base_path('routes/biblioteca.php'));Route::getRoutes()->refreshNameLookups();
        $user=new User(['name'=>'Prueba Biblioteca','rol'=>'Administrador/a']);$user->id=99;$this->actingAs($user);
    }
    protected function tearDown():void {if(isset($this->storage)&&str_starts_with($this->storage,storage_path('framework/testing').DIRECTORY_SEPARATOR.'biblioteca-'))File::deleteDirectory($this->storage);parent::tearDown();}
    public function test_json_editor_preserves_optional_empty_fields_and_whitespace():void {
        $c=Content::institutional(Content::emptyJob());$c['name']='Puesto de prueba';$c['area']='Calidad';$c['groups']['purpose']=[Content::paragraph('  Texto con espacios intencionales.  ')];
        $response=$this->postJson(route('biblioteca.store'),['kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'content'=>$c])->assertOk();
        $v=(new Library)->version($response->json('version'));$stored=Content::decode($v['payload_json']);
        $this->assertSame('',$stored['sector']);$this->assertSame($c['groups']['purpose'][0]['text'],$stored['groups']['purpose'][0]['text']);
        $this->postJson(route('biblioteca.action',$v['id']),['action'=>'save','revision'=>0,'content'=>$stored])->assertOk();
    }
    public function test_docx_upload_review_confirmation_source_and_idempotency():void {
        $file=(new WordExport)->create('<h1>Auxiliar de prueba</h1><h2>Propósito</h2><p>Acompañar a las personas.</p>');
        try {
            $hash=hash_file('sha256',$file);
            $response=$this->post(route('biblioteca.upload.stage'),['file'=>new UploadedFile($file,'auxiliar-prueba.docx',null,null,true)])->assertRedirect();
            $row=DB::table('bib_uploads')->first();$this->assertNotNull($row);$u=Content::decode($row->payload_json);$this->assertNull($u['error']);
            $this->get(route('biblioteca.upload.review',$row->id))->assertOk()->assertSee('Revisar importación');
            $c=Content::institutional(Content::emptyJob());$c['name']='Auxiliar importado';$c['area']='Calidad';$c['groups']['purpose']=[Content::paragraph('Acompañar.')];
            $data=['destination'=>'new','document'=>null,'content'=>$c];
            $result=$this->postJson(route('biblioteca.upload.confirm',$row->id),$data)->assertOk();$this->postJson(route('biblioteca.upload.confirm',$row->id),$data)->assertOk()->assertExactJson($result->json());
            $this->assertSame(1,DB::table('bib_documents')->count());$this->assertSame(1,DB::table('bib_versions')->count());
            $source=(array)DB::table('bib_sources')->first();$this->assertSame($hash,hash_file('sha256',Uploads::sourcePath($source)));$this->assertSame($hash,hash_file('sha256',$file));
            $this->get(route('biblioteca.source',$source['id']))->assertOk();$this->get(route('biblioteca.source.file',$source['id']))->assertOk();
            $v=(new Library)->version(DB::table('bib_versions')->first()->id);$this->assertSame('Vigencia no verificada',$v['state']);$this->assertNull($v['current_slot']);
        }finally{if(is_file($file))unlink($file);}
    }
    public function test_sync_unchanged_sources_creates_no_versions_or_events():void {
        $bundle=getenv('BIBLIOTECA_TEST_BUNDLE');$this->assertNotFalse($bundle);(new Importer)->import($bundle);
        $root=getenv('BIBLIOTECA_TEST_SOURCE');$this->assertNotFalse($root);config(['biblioteca.source_root'=>$root]);
        $before=[DB::table('bib_versions')->count(),DB::table('bib_events')->count()];
        $report=(new Synchronizer)->run('prueba');$this->assertSame(41,$report['analyzed']);$this->assertSame(41,$report['unchanged']);
        foreach(['new','modified','removed','restored'] as $key)$this->assertSame(0,$report[$key]);$this->assertSame([],$report['errors']);
        $this->assertSame($before,[DB::table('bib_versions')->count(),DB::table('bib_events')->count()]);
    }
}
