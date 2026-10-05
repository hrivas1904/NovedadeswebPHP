<?php
// Base aislada para QA; nunca carga ni modifica la conexión MySQL configurada.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root = storage_path('framework/testing/organigrama-browser');
Illuminate\Support\Facades\File::ensureDirectoryExists($root);
if (is_file($root.'/database.sqlite')) {
    throw new RuntimeException('La base de QA ya existe. Usar otro directorio o conservarla para continuar pruebas.');
}
touch($root.'/database.sqlite');
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $root.'/database.sqlite']);
Illuminate\Support\Facades\DB::purge();
Tests\Support\OrganigramaFixture::crear();
$report = app(App\Services\Organigrama\Importador::class)->importar(Tests\Support\OrganigramaFixture::origen());
file_put_contents($root.'/import-report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['posiciones' => $report['total_posiciones'], 'dependencias' => $report['total_dependencias'], 'coincidencias_fixture' => count($report['coincidencias']), 'pendientes_fixture' => count($report['pendientes'])]).PHP_EOL;
