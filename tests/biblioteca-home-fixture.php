<?php
// Isolated, representative data for the library landing's browser checks.
require __DIR__.'/biblioteca-versions-fixture.php';
\Illuminate\Support\Facades\DB::table('users')->where('id', 1)->update(['username' => 'ffernandez']);
foreach (['descriptivos' => 39, 'politicas' => 4, 'procedimientos' => 4] as $kind => $count) {
    for ($i = 1; $i <= $count; $i++) {
        $content = \App\Services\Biblioteca\Content::decode($versions[$kind]['payload_json']);
        if ($kind === 'descriptivos') {
            $content['name'] = 'Puesto QA '.str_pad($i, 2, '0', STR_PAD_LEFT);
        } else {
            $content['id'] = '';
            $content['code'] = 'HOME-'.$kind.'-'.$i;
            $content['title'] = 'Documento QA '.$kind.' '.$i;
        }
        $version = $library->create($kind, $content, 'fixture', (string) \Illuminate\Support\Str::uuid());
        if ($kind === 'politicas') {
            \Illuminate\Support\Facades\DB::table('bib_versions')->where('id', $version['id'])->update(['state' => 'Importado', 'origin' => 'imported']);
        } else {
            $library->publish($version['id'], 0, 'Versión de prueba', true, 'fixture');
        }
    }
}
echo "51 documentos visibles para administración; base SQLite aislada.\n";
