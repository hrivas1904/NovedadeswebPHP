<?php

// Local-only test harness; never register this file as an application route.
if (getenv('BIBLIOTECA_ACCEPTANCE_TEST') !== '1' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public = realpath(__DIR__.'/../public');
$static = realpath($public.$uri);
if ($static && str_starts_with($static, $public.DIRECTORY_SEPARATOR) && is_file($static) && pathinfo($static, PATHINFO_EXTENSION) !== 'php') {
    return false;
}
if (! str_starts_with($uri, '/biblioteca')) {
    http_response_code(404);
    exit;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$root = storage_path('framework/testing/biblioteca-acceptance-browser'.(getenv('BIBLIOTECA_TEST_RUN') ? '-'.preg_replace('/[^a-zA-Z0-9_-]/', '', getenv('BIBLIOTECA_TEST_RUN')) : ''));
if (! is_file($root.'/database.sqlite')) {
    throw new RuntimeException('Falta el fixture aislado.');
}
config(['app.env' => 'testing', 'database.default' => 'sqlite', 'database.connections.sqlite.database' => $root.'/database.sqlite', 'biblioteca.storage' => $root.'/files', 'session.driver' => 'file', 'session.files' => $root.'/sessions', 'session.cookie' => 'bib_acceptance_test_session', 'cache.default' => 'array', 'logging.channels.single.path' => $root.'/laravel.log']);
Illuminate\Support\Facades\File::ensureDirectoryExists($root.'/sessions');
Illuminate\Support\Facades\DB::purge();
$id = (int) ($_COOKIE['bib_test_persona'] ?? 2);
if (! in_array($id, [1, 2, 3], true)) {
    http_response_code(403);
    exit;
}
Illuminate\Support\Facades\Auth::guard()->setUser(App\Models\User::findOrFail($id));
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request,$response);
