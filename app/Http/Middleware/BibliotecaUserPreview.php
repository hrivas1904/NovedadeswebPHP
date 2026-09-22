<?php
namespace App\Http\Middleware;
use App\Models\User;
use App\Services\Biblioteca\UserPreview;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class BibliotecaUserPreview {
    public function handle(Request $request,Closure $next) {
        $preview=$request->session()->get('biblioteca_preview');
        if(!$preview) return $next($request);
        $original=$request->user();
        $actor=User::find($original->getAuthIdentifier());
        if(!$actor || !UserPreview::allowed($actor) || (int)$preview['actor_id']!==(int)$actor->id) {
            if($actor && (int)$preview['actor_id']===(int)$actor->id) UserPreview::audit($actor,'finalizar_vista_usuario',$preview+['reason'=>'permiso revocado']);
            $request->session()->forget('biblioteca_preview');
            abort(403,'La consulta como otro usuario ya no está autorizada.');
        }
        if($request->routeIs('biblioteca.preview','biblioteca.preview.start','biblioteca.preview.stop')) return $next($request);
        abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'),403,'Modo consulta: no podés firmar, aprobar ni guardar cambios como otro usuario.');
        abort_unless($request->routeIs('biblioteca.index','biblioteca.search','biblioteca.catalog','biblioteca.show','biblioteca.mine','biblioteca.receipt','biblioteca.export'),403,'Volvé a tu usuario para administrar la biblioteca.');
        $target=User::find($preview['target_id']);
        if(!$target || $target->estado!=='ACTIVO') {
            UserPreview::audit($actor,'finalizar_vista_usuario',$preview+['reason'=>'usuario no disponible']);
            $request->session()->forget('biblioteca_preview');
            return redirect()->route('biblioteca.preview')->withErrors(['preview'=>'El usuario seleccionado ya no está activo.']);
        }
        $request->attributes->set('biblioteca_preview',['actor'=>$actor,'target'=>$target]);
        $resolver=$request->getUserResolver();
        Auth::guard()->setUser($target);
        $request->setUserResolver(fn()=>$target);
        try { $response=$next($request); $response->headers->set('Cache-Control','no-store, private'); return $response; }
        finally { Auth::guard()->setUser($original); $request->setUserResolver($resolver); }
    }
}
