<?php
namespace App\Http\Controllers\Biblioteca;
use App\Http\Controllers\Controller;
use App\Services\Biblioteca\{Governance,Library};
use Illuminate\Http\Request;
class GovernanceController extends Controller {
    public function index(Governance $service,Library $library) {
        return view('biblioteca.visibility',['settings'=>$service->visibility(),'pending'=>array_values(array_filter($library->entries(true),fn($e)=>$e['document']['kind']==='politicas'&&!$e['version']['published_at']&&$e['version']['state']!=='Histórico / retirado')),'policies'=>array_values(array_filter($library->entries(),fn($e)=>$e['document']['kind']==='politicas')),'collections'=>config('biblioteca.collections'),'canManage'=>true]);
    }
    public function save(Request $r,Governance $service) {
        $data=$r->validate(['key'=>'required|string|max:190','visible'=>'required|boolean','revision'=>'required|integer|min:0']);
        $key=$data['key'];
        if(str_starts_with($key,'section:')) abort_unless(isset(config('biblioteca.collections')[substr($key,8)]),422);
        else { abort_unless(str_starts_with($key,'document:'),422); $d=(new Library)->document(substr($key,9)); abort_unless($d['kind']==='politicas',422); }
        $service->setVisibility($key,(bool)$data['visible'],(int)$data['revision'],$r->user());
        return back()->with('status','Visibilidad actualizada.');
    }
    public function approve(Request $r,string $version,Library $library) {
        abort_unless(Governance::canApprove($r->user()),403,'Sólo el gerente designado puede aprobar políticas.');
        $r->validate(['revision'=>'required|integer|min:0','confirmed'=>'accepted','reason'=>'nullable|string|max:4000']);
        $v=$library->version($version); abort_unless($library->document($v['document_id'])['kind']==='politicas',422);
        $library->publish($version,$r->integer('revision'),$r->input('reason')??'Aprobación de Gerencia.',true,Library::actor($r->user()),true,true);
        return redirect()->route('biblioteca.show',$v['document_id'])->with('status','Política aprobada y publicada. Su acceso respeta la configuración de visibilidad.');
    }
}
