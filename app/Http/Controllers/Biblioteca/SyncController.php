<?php
namespace App\Http\Controllers\Biblioteca;
use App\Http\Controllers\Controller;
use App\Services\Biblioteca\{Library,Synchronizer};
use Illuminate\Http\Request;

class SyncController extends Controller {
    public function index(Request $request) {
        abort_unless(Library::manages($request->user()),403);
        return view('biblioteca.sync',['collections'=>config('biblioteca.collections'),'canManage'=>true,'configured'=>(bool)config('biblioteca.source_root')]);
    }
    public function run(Request $request,Synchronizer $sync) {
        abort_unless(Library::manages($request->user()),403);
        $report=$sync->run(Library::actor($request->user()));return redirect()->route('biblioteca.sync')->with('sync_report',$report);
    }
}
