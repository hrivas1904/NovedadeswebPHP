<?php
// This script can create only an isolated SQLite fixture in storage/framework/testing.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root=storage_path('framework/testing/biblioteca-browser');
if(is_file($root.'/database.sqlite'))throw new RuntimeException('La base de prueba ya existe. Elegí otra carpeta para una nueva prueba.');
Illuminate\Support\Facades\File::ensureDirectoryExists($root);touch($root.'/database.sqlite');
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>$root.'/database.sqlite','biblioteca.storage'=>$root.'/files','cache.default'=>'array']);Illuminate\Support\Facades\DB::purge();
(require database_path('migrations/2026_09_18_150000_create_biblioteca_tables.php'))->up();
$report=(new App\Services\Biblioteca\Importer)->import($argv[1]);
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
