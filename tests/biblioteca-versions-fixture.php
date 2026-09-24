<?php

// Reuse the isolated SQLite fixture; never seed the application's configured database.
if (PHP_SAPI !== 'cli' || !getenv('BIBLIOTECA_TEST_RUN')) {
    throw new RuntimeException('Indicá BIBLIOTECA_TEST_RUN para aislar esta prueba.');
}
require __DIR__.'/biblioteca-acceptance-fixture.php';
$versions=[];
$library=new \App\Services\Biblioteca\Library;
foreach(['politicas','procedimientos','instructivos'] as $kind) {
    $content=['id'=>'','sourceId'=>'','collection'=>$kind,'code'=>'QA-'.strtoupper($kind),'title'=>'Documento QA '.$kind,'sourceHeading'=>'','summary'=>'Verificación de versiones','version'=>'1.0','declaredState'=>'borrador','validFrom'=>'2026-01-01','lastReview'=>null,'reviewMonths'=>12,'approver'=>'Dirección','approvalRecord'=>'Acta de prueba','responsible'=>'Calidad','history'=>[],'sections'=>[['id'=>'pasos','title'=>'Contenido','html'=>'<p>Contenido original.</p><table><tr><td>Responsable</td><td>Calidad</td></tr></table>','text'=>'']]];
    $version=$library->create($kind,$content,'fixture',(string)\Illuminate\Support\Str::uuid());
    if($kind==='politicas') {
        \Illuminate\Support\Facades\DB::table('bib_versions')->where('id',$version['id'])->update(['state'=>'Importado','origin'=>'imported']);
        $version=$library->version($version['id']);
    } else {
        $version=$library->publish($version['id'],0,'Versión inicial',true,'fixture');
    }
    $versions[$kind]=$version;
}
$versions['descriptivos']=$library->entry($ids[0])['version'];
file_put_contents($root.'/versions.json',\App\Services\Biblioteca\Content::json($versions));
echo "Documentos de las cuatro colecciones listos.\n";
