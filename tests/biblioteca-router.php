<?php
// Local browser-test router. Never registered in web.php or reachable through public/index.php.
if(getenv('BIBLIOTECA_BROWSER_TEST')!=='1'||!in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true)){http_response_code(404);exit;}
$uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$public=realpath(__DIR__.'/../public');$static=realpath($public.$uri);
if($static&&str_starts_with($static,$public.DIRECTORY_SEPARATOR)&&is_file($static)&&pathinfo($static,PATHINFO_EXTENSION)!=='php')return false;
if(!str_starts_with($uri,'/biblioteca')){http_response_code(404);exit;}
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$request=Illuminate\Http\Request::capture();$app->instance('request',$request);$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$kernel->bootstrap();
$root=storage_path('framework/testing/biblioteca-browser');
if(!is_file($root.'/database.sqlite'))throw new RuntimeException('Falta la base aislada.');
config(['app.env'=>'testing','database.default'=>'sqlite','database.connections.sqlite.database'=>$root.'/database.sqlite','biblioteca.storage'=>$root.'/files','session.driver'=>'file','session.files'=>$root.'/sessions','session.cookie'=>'biblioteca_test_session','cache.default'=>'array','logging.channels.single.path'=>$root.'/laravel.log','biblioteca.source_root'=>getenv('BIBLIOTECA_TEST_SOURCE')?:null]);
Illuminate\Support\Facades\File::ensureDirectoryExists($root.'/sessions');Illuminate\Support\Facades\DB::purge();
if(!Illuminate\Support\Facades\Route::has('biblioteca.index'))Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/biblioteca.php'));
Illuminate\Support\Facades\Route::getRoutes()->refreshNameLookups();
$user=new App\Models\User(['name'=>'Prueba Biblioteca','username'=>'biblioteca-test','rol'=>'Administrador/a']);$user->id=999999;
Illuminate\Support\Facades\Auth::guard()->setUser($user);
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
