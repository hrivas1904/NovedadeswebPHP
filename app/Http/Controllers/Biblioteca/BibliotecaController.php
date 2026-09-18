<?php

namespace App\Http\Controllers\Biblioteca;

use App\Http\Controllers\Controller;
use App\Services\Biblioteca\{Content,Library,Uploads,WordExport};
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class BibliotecaController extends Controller
{
    public function __construct(private Library $library) {}
    private function manager(Request $r): void {abort_unless(Library::manages($r->user()),403,'La gestión de la biblioteca requiere permiso de administración.');}
    private function page(string $view,array $data=[]) {return view('biblioteca.'.$view,$data+['collections'=>config('biblioteca.collections'),'canManage'=>Library::manages(auth()->user())]);}
    public function index() { $entries=$this->library->filter($this->library->entries(),[]);return $this->page('index',compact('entries')); }
    public function catalog(Request $r,?string $kind=null) {
        if($kind)abort_unless(isset(config('biblioteca.collections')[$kind]),404);
        $control=$r->routeIs('biblioteca.control');$manage=$r->routeIs('biblioteca.manage');if($manage)$this->manager($r);
        $all=$this->library->entries($control);$filters=$r->only(['q','area','state','review','history']);$filters['kind']=$kind?:$r->query('kind');if($control)$filters['history']=1;
        $filtered=$this->library->filter($all,$filters);$page=max(1,(int)$r->query('page',1));
        $entries=new LengthAwarePaginator(array_slice($filtered,($page-1)*15,15),count($filtered),15,$page,['path'=>$r->url(),'query'=>$r->query()]);
        $areas=collect($all)->pluck('area')->filter()->unique()->sort()->values();$states=collect($all)->pluck('state')->unique()->sort()->values();
        return $this->page('catalog',compact('entries','filters','areas','states','control','manage','kind'));
    }
    public function show(Request $r,string $document) {
        $entry=$this->library->entry($document,$r->query('version'));$history=$this->library->history($document);
        $sources=DB::table('bib_sources')->where('document_id',$document)->get();
        $findings=DB::table('bib_findings')->where('document_id',$document)->where('version_id',$entry['version']['source_version_id']?:$entry['version']['id'])->get();
        $events=Library::manages($r->user())?$this->library->events($document):[];
        return $this->page('show',compact('entry','history','sources','findings','events'));
    }
    public function create(Request $r) {
        $this->manager($r);$kind=$r->query('kind','descriptivos');abort_unless(isset(config('biblioteca.collections')[$kind]),404);
        $content=$kind==='descriptivos'?Content::institutional(Content::emptyJob()):['id'=>'','sourceId'=>'','collection'=>$kind,'code'=>'','title'=>'','sourceHeading'=>'','summary'=>'','version'=>'1.0','declaredState'=>'borrador','validFrom'=>null,'lastReview'=>null,'reviewMonths'=>12,'approver'=>'','approvalRecord'=>null,'responsible'=>'','history'=>[],'sections'=>[['id'=>(string)Str::uuid(),'title'=>'Contenido','html'=>'','text'=>'']]];
        $entry=null;$duplicate=$r->query('duplicate');if($duplicate){$v=$this->library->version($duplicate);$source=$this->library->document($v['document_id']);abort_unless($source['kind']===$kind,422);$content=Content::decode($v['payload_json']);if($kind==='descriptivos'){$content=Content::institutional($content);$content['name']='Copia de '.$content['name'];}else{$content['id']='';$content['code']='';$content['title']='Copia de '.$content['title'];$content['version']='1.0';$content['history']=[];}}
        return $this->page('editor',['entry'=>$entry,'content'=>$content,'kind'=>$kind,'requestId'=>(string)Str::uuid(),'upload'=>null]);
    }
    public function edit(Request $r,string $version) {
        $this->manager($r);$v=$this->library->version($version);$entry=$this->library->entry($v['document_id'],$version);
        abort_unless($v['state']==='Borrador'&&!$v['published_at'],409,'Creá un borrador antes de editar.');
        return $this->page('editor',['entry'=>$entry,'content'=>$entry['content'],'kind'=>$entry['document']['kind'],'requestId'=>(string)Str::uuid(),'upload'=>null]);
    }
    public function store(Request $r) {
        $this->manager($r);$r->validate(['kind'=>'required|string','content'=>'required|array','requestId'=>'required|uuid']);
        $v=$this->library->create($r->input('kind'),$this->content($r),Library::actor($r->user()),$r->input('requestId'));return $this->result($v);
    }
    private function content(Request $r): ?array {
        // Document content retains empty strings and intentional whitespace, unlike form metadata.
        if($r->isJson()) {
            abort_if(strlen($r->getContent())>2000000,413,'El documento es demasiado extenso.');
            $data=json_decode($r->getContent(),true);
            return is_array($data['content']??null)?$data['content']:null;
        }
        return $r->input('content');
    }
    private function result(array $v) {return response()->json(['version'=>$v['id'],'documentId'=>$v['document_id'],'revision'=>$v['revision'],'url'=>route('biblioteca.show',['document'=>$v['document_id'],'version'=>$v['id']]),'editUrl'=>route('biblioteca.edit',$v['id'])]);}
    public function action(Request $r,string $version) {
        $this->manager($r);$r->validate(['action'=>'required|in:draft,save,publish,retire','revision'=>'required_unless:action,draft|integer|min:0','reason'=>'nullable|string|max:4000','confirmed'=>'nullable|boolean','content'=>'required_if:action,save|array']);
        $actor=Library::actor($r->user());$action=$r->input('action');
        $v=match($action) {'draft'=>$this->library->draft($version,$actor),'save'=>$this->library->save($version,$r->integer('revision'),$this->content($r),$actor),'publish'=>$this->library->publish($version,$r->integer('revision'),$r->input('reason')??'',$r->boolean('confirmed'),$actor),'retire'=>$this->library->retire($version,$r->integer('revision'),$r->input('reason')??'',$actor)};return $this->result($v);
    }
    public function review(Request $r,string $version) {
        $this->manager($r);$v=$this->library->version($version);$entry=$this->library->entry($v['document_id'],$version);abort_unless($entry['job'],404);
        $findings=DB::table('bib_findings')->where('document_id',$v['document_id'])->where('version_id',$v['source_version_id']?:$version)->get();return $this->page('review',compact('entry','findings'));
    }
    public function saveReview(Request $r,string $version) {
        $this->manager($r);$r->validate(['action'=>'required|in:save,validate,publish','revision'=>'required|integer|min:0','documentRevision'=>'required|integer|min:0','canonical_name'=>'nullable|string|max:500','normalized_area'=>'nullable|string|max:500','aliases'=>'nullable|string|max:8000','observation'=>'nullable|string|max:4000','comment'=>'nullable|string|max:4000','confirmed'=>'nullable|boolean']);
        $v=$this->library->review($version,$r->integer('revision'),$r->integer('documentRevision'),$r->all(),Library::actor($r->user()));return $this->result($v);
    }
    public function source(Request $r,string $source) {
        $s=DB::table('bib_sources')->where('id',$source)->first();abort_unless($s,404);$s=(array)$s;Uploads::sourcePath($s);
        return $this->page('source',['source'=>$s,'parsed'=>Content::decode($s['extraction_json']),'document'=>$this->library->document($s['document_id'])]);
    }
    public function file(Request $r,string $source) {
        $s=DB::table('bib_sources')->where('id',$source)->first();abort_unless($s,404);$s=(array)$s;$path=Uploads::sourcePath($s);
        if($s['format']==='PDF'&&$r->boolean('inline'))return response()->file($path,['Content-Type'=>'application/pdf','X-Content-Type-Options'=>'nosniff']);
        return response()->download($path,$s['name'],['Content-Type'=>'application/octet-stream','X-Content-Type-Options'=>'nosniff']);
    }
    public function export(Request $r,string $version,string $format) {
        $v=$this->library->version($version);$entry=$this->library->entry($v['document_id'],$version);$name=Str::slug($entry['title']).'-v'.$v['number'];
        $html=view('biblioteca.export',compact('entry'))->render();
        if($format==='pdf')return Pdf::loadHTML($html)->setPaper('a4')->setOption('isRemoteEnabled',false)->download($name.'.pdf');
        abort_unless($format==='docx',404);$file=(new WordExport)->create($html);return response()->download($file,$name.'.docx',['Content-Type'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])->deleteFileAfterSend(true);
    }
    public function upload(Request $r,Uploads $uploads,?string $id=null) {
        $this->manager($r);if(!$id)return $this->page('upload',['pending'=>DB::table('bib_uploads')->whereNull('document_id')->orderByDesc('created_at')->get()]);
        $data=$uploads->get($id);if($data['row']['document_id'])return redirect()->route('biblioteca.show',$data['row']['document_id']);
        $stage=$data['stage'];$content=$stage['parsed']?Content::institutional(Content::fromParsed($stage['parsed'])):Content::institutional(Content::emptyJob());
        return $this->page('editor',['entry'=>null,'content'=>$content,'kind'=>'descriptivos','requestId'=>(string)Str::uuid(),'upload'=>$stage,'destinations'=>$this->library->filter($this->library->entries(),['kind'=>'descriptivos','history'=>1])]);
    }
    public function stage(Request $r,Uploads $uploads) {
        $this->manager($r);$r->validate(['file'=>'required|file|max:51200']);$u=$uploads->stage($r->file('file'),Library::actor($r->user()));return redirect()->route('biblioteca.upload.review',$u['id']);
    }
    public function confirmUpload(Request $r,Uploads $uploads,string $id) {
        $this->manager($r);$r->validate(['destination'=>'required|in:new,version,document,support','document'=>'nullable|string|max:160','content'=>'nullable|array']);
        $v=$uploads->confirm($id,$r->input('destination'),$r->input('document'),$this->content($r),Library::actor($r->user()));return response()->json(['url'=>route('biblioteca.show',array_filter(['document'=>$v['document_id'],'version'=>$v['id']]))]);
    }
}
