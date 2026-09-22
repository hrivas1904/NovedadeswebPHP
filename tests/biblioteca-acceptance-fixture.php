<?php

// Standalone fixture: no connection to the application's MySQL tables is used.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root = storage_path('framework/testing/biblioteca-acceptance-browser'.(getenv('BIBLIOTECA_TEST_RUN') ? '-'.preg_replace('/[^a-zA-Z0-9_-]/', '', getenv('BIBLIOTECA_TEST_RUN')) : ''));
if (is_file($root.'/database.sqlite')) {
    throw new RuntimeException('La base aislada ya existe.');
}
Illuminate\Support\Facades\File::ensureDirectoryExists($root);
touch($root.'/database.sqlite');
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $root.'/database.sqlite', 'biblioteca.storage' => $root.'/files', 'cache.default' => 'array']);
Illuminate\Support\Facades\DB::purge();
use App\Services\Biblioteca\Assignments;
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Schema::create('users', function (Blueprint $t) {
    $t->id();
    $t->string('name');
    $t->string('username');
    $t->string('rol');
    $t->integer('legajo');
    $t->string('estado');
});
Schema::create('empleados', function (Blueprint $t) {
    $t->integer('LEGAJO')->primary();
    $t->string('COLABORADOR');
    $t->integer('ID_CATEG');
    $t->integer('ID_SERVICIOS')->nullable();
    $t->integer('ID_ROL')->nullable();
    $t->string('CONVENIO')->nullable();
    $t->string('ESTADO');
});
Schema::create('servicios', function (Blueprint $t) {
    $t->integer('ID_SERVICIOS')->primary();
    $t->string('NOMBRE');
});
Schema::create('rol_empleados', function (Blueprint $t) {
    $t->integer('ID_ROL')->primary();
    $t->string('NOMBRE');
});
Schema::create('categ_empleados', function (Blueprint $t) {
    $t->integer('ID_CATEG')->primary();
    $t->string('NOMBRE');
    $t->integer('estado');
});
foreach (['2026_09_18_150000_create_biblioteca_tables.php', '2026_09_20_120000_add_biblioteca_assignments_and_acceptances.php', '2026_09_21_010000_add_biblioteca_policy_governance.php'] as $file) {
    (require database_path('migrations/'.$file))->up();
}
DB::table('users')->insert([
    ['id' => 1, 'name' => 'Administración de prueba', 'username' => 'admin-test', 'rol' => 'Administrador/a', 'legajo' => 900, 'estado' => 'ACTIVO'],
    ['id' => 2, 'name' => 'Colaborador de prueba', 'username' => 'colaborador-test', 'rol' => 'Colaborador/a', 'legajo' => 1001, 'estado' => 'ACTIVO'],
    ['id' => 3, 'name' => 'Otra persona de prueba', 'username' => 'otra-test', 'rol' => 'Colaborador/a', 'legajo' => 1002, 'estado' => 'ACTIVO'],
]);
DB::table('categ_empleados')->insert([['ID_CATEG' => 1, 'NOMBRE' => 'Analista', 'estado' => 1], ['ID_CATEG' => 2, 'NOMBRE' => 'Categoría sin descriptivo', 'estado' => 1], ['ID_CATEG' => 3, 'NOMBRE' => 'Categoría inactiva', 'estado' => 0]]);
DB::table('empleados')->insert([['LEGAJO' => 1001, 'COLABORADOR' => 'Colaborador de prueba', 'ID_CATEG' => 1, 'ESTADO' => 'ACTIVO'], ['LEGAJO' => 1002, 'COLABORADOR' => 'Otra persona de prueba', 'ID_CATEG' => 2, 'ESTADO' => 'ACTIVO']]);
DB::table('servicios')->insert(['ID_SERVICIOS' => 1, 'NOMBRE' => 'Facturación de prueba']);
DB::table('rol_empleados')->insert(['ID_ROL' => 1, 'NOMBRE' => 'Telefonista de prueba']);
DB::table('empleados')->where('LEGAJO', 1001)->update(['ID_SERVICIOS' => 1, 'ID_ROL' => 1, 'CONVENIO' => 'SANIDAD']);
DB::table('empleados')->where('LEGAJO', 1002)->update(['CONVENIO' => 'FUERA DE CONVENIO']);
$ids = [];
foreach (['Analista de prueba', 'Auxiliar de prueba'] as $name) {
    $c = Content::institutional(Content::emptyJob());
    $c['name'] = $name;
    $c['area'] = 'Calidad';
    $c['groups']['purpose'] = [Content::paragraph('Acompañar a las personas.')];
    $c['groups']['tasks'] = [Content::paragraph('Revisar documentos.')];
    $c['groups']['commitment'] = [Content::paragraph('Cumplir los protocolos.')];
    $lib = new Library;
    $v = $lib->create('descriptivos', $c, 'fixture', (string) Illuminate\Support\Str::uuid());
    $v = $lib->publish($v['id'], 0, 'Publicación de prueba', true, 'fixture');
    $ids[] = $v['document_id'];
}
(new Assignments)->save('category', 1, $ids[0], 0, 'fixture');
file_put_contents($root.'/fixture.json', Content::json(['base' => $ids[0], 'individual' => $ids[1]]));
echo "Fixture aislado listo.\n";
