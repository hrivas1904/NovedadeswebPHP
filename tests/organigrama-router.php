<?php
// Solo servidor explícito de pruebas, localhost y base aislada. No registrado en public/index.php.
if (getenv('ORGANIGRAMA_BROWSER_TEST') !== '1' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public = realpath(__DIR__.'/../public');
$static = realpath($public.$uri);
if ($static && str_starts_with($static, $public.DIRECTORY_SEPARATOR) && is_file($static) && pathinfo($static, PATHINFO_EXTENSION) !== 'php') {
    return false;
}
if (! str_starts_with($uri, '/rrhh/organigrama')) {
    http_response_code(404);
    exit;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$root = storage_path('framework/testing/organigrama-browser');
if (! is_file($root.'/database.sqlite')) {
    throw new RuntimeException('Ejecutar primero tests/organigrama-fixture.php.');
}
config(['app.env' => 'testing', 'database.default' => 'sqlite', 'database.connections.sqlite.database' => $root.'/database.sqlite', 'session.driver' => 'file', 'session.files' => $root.'/sessions', 'session.cookie' => 'org_test_session', 'cache.default' => 'array', 'logging.channels.single.path' => $root.'/laravel.log']);
Illuminate\Support\Facades\File::ensureDirectoryExists($root.'/sessions');
Illuminate\Support\Facades\DB::purge();
$id = ($_COOKIE['org_test_persona'] ?? '1') === '2' ? 2 : 1;
Illuminate\Support\Facades\Auth::guard()->setUser(App\Models\User::findOrFail($id));
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
