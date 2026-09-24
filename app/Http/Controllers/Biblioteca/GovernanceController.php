<?php
namespace App\Http\Controllers\Biblioteca;
use App\Http\Controllers\Controller;
use App\Services\Biblioteca\{Governance,Library,Listing};
use Illuminate\Http\Request;
class GovernanceController extends Controller {
    public function index(Request $r,Governance $service,Library $library) {
        $settings=$service->visibility();$collections=config('biblioteca.collections');
        $filtered=$library->filter($library->entries(),$r->only(['q','kind'])+['history'=>1]);
        [$sort,$direction]=Listing::sorting($r,['title','area'],'title');
        $documents=Listing::paginate($filtered,$r,fn($e)=>$sort==='area'?$e['area']:$e['title'],fn($e)=>$e['document']['id'],$direction);
        if($r->expectsJson())return response()->json(['html'=>view('biblioteca.visibility-results',compact('settings','collections','documents'))->render()]);
        $pending=array_values(array_filter($library->entries(true),fn($e)=>$e['document']['kind']==='politicas'&&!$e['version']['published_at']&&$e['version']['state']!=='Histórico / retirado'));
        return view('biblioteca.visibility',compact('settings','collections','documents','pending')+['canManage'=>true]);
    }
    public function save(Request $r,Governance $service) {
        $data=$r->validate(['key'=>'required|string|max:190','visible'=>'required|boolean','revision'=>'required|integer|min:0']);
        $key=$data['key'];
        if(str_starts_with($key,'section:')) abort_unless(isset(config('biblioteca.collections')[substr($key,8)]),422);
        else { abort_unless(str_starts_with($key,'document:'),422); $d=(new Library)->document(substr($key,9)); abort_unless(isset(config('biblioteca.collections')[$d['kind']]),422); }
        $state=$service->setVisibility($key,(bool)$data['visible'],(int)$data['revision'],$r->user());
        if($r->expectsJson())return $this->stateResponse($state);
        return back()->with('status','Visibilidad actualizada.');
    }
    public function state(Request $r,Governance $service) {
        $r->validate(['key'=>'required|string|max:190']);$key=$r->input('key');
        if(str_starts_with($key,'section:'))abort_unless(isset(config('biblioteca.collections')[substr($key,8)]),422);
        else {abort_unless(str_starts_with($key,'document:'),422);(new Library)->document(substr($key,9));}
        $setting=$service->visibility()[$key]??null;
        return $this->stateResponse(['key'=>$key,'visible'=>(bool)($setting->visible??($key!=='section:instructivos')),'revision'=>(int)($setting->revision??0)]);
    }
    private function stateResponse(array $state) {
        if(str_starts_with($state['key'],'section:'))$state['navigation']=view('biblioteca.navigation',['collections'=>config('biblioteca.collections'),'canManage'=>true,'activeCollection'=>null,'administrationActive'=>true])->render();
        return response()->json($state)->header('Cache-Control','no-store, private');
    }
    public function approve(Request $r,string $version,Library $library) {
        abort_unless(Governance::canApprove($r->user()),403,'Sólo el gerente designado puede aprobar políticas.');
        $r->validate(['revision'=>'required|integer|min:0','confirmed'=>'accepted','reason'=>'nullable|string|max:4000']);
        $v=$library->version($version); abort_unless($library->document($v['document_id'])['kind']==='politicas',422);
        $library->publish($version,$r->integer('revision'),$r->input('reason')??'Aprobación de Gerencia.',true,Library::actor($r->user()),true,true);
        return redirect()->route('biblioteca.show',$v['document_id'])->with('status','Política aprobada y publicada. Su acceso respeta la configuración de visibilidad.');
    }
}
