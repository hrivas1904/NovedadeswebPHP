<?php
namespace App\Http\Controllers\Biblioteca;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Biblioteca\UserPreview;
use Illuminate\Http\Request;
class UserPreviewController extends Controller {
    private function actor(Request $r) {
        $actor=User::find($r->user()->getAuthIdentifier());
        abort_unless(UserPreview::allowed($actor),403);
        return $actor;
    }
    public function index(Request $r) {
        $actor=$this->actor($r);
        $users=User::where('estado','ACTIVO')->where('id','!=',$actor->id)->orderBy('name')->get(['id','name','username','rol','legajo']);
        return view('biblioteca.preview',['users'=>$users,'collections'=>config('biblioteca.collections'),'canManage'=>true]);
    }
    public function start(Request $r) {
        $actor=$this->actor($r);$r->validate(['user_id'=>'required|integer']);
        $target=User::where('estado','ACTIVO')->findOrFail($r->integer('user_id'));
        abort_if((int)$target->id===(int)$actor->id,422,'Seleccioná otro usuario.');
        if($old=$r->session()->get('biblioteca_preview')) UserPreview::audit($actor,'finalizar_vista_usuario',$old+['reason'=>'cambio de usuario']);
        $data=['actor_id'=>$actor->id,'target_id'=>$target->id,'target_name'=>$target->name,'target_username'=>$target->username,'started_at'=>\App\Services\Biblioteca\Library::now()];
        UserPreview::audit($actor,'iniciar_vista_usuario',$data);
        $r->session()->put('biblioteca_preview',$data);
        return redirect()->route('biblioteca.mine');
    }
    public function stop(Request $r) {
        $actor=$this->actor($r);
        if($data=$r->session()->get('biblioteca_preview')) UserPreview::audit($actor,'finalizar_vista_usuario',$data+['reason'=>'salida solicitada']);
        $r->session()->forget('biblioteca_preview');
        return redirect()->route('biblioteca.index')->with('status','Volviste a tu usuario.');
    }
}
