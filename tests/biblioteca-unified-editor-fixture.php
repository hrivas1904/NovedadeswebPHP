<?php

// Isolated browser data. No writes are made to the configured MySQL database.
if (PHP_SAPI !== 'cli' || !getenv('BIBLIOTECA_TEST_RUN')) {
    throw new RuntimeException('Indicá BIBLIOTECA_TEST_RUN para aislar esta prueba.');
}
require __DIR__.'/biblioteca-acceptance-fixture.php';

use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Schema::create('areas', function (Blueprint $t) {
    $t->integer('ID_AREA')->primary();
    $t->string('NOMBRE');
    $t->integer('ESTADO')->default(1);
});
DB::table('areas')->insert([
    ['ID_AREA'=>1,'NOMBRE'=>'Administración QA','ESTADO'=>1],
    ['ID_AREA'=>2,'NOMBRE'=>'Enfermería QA','ESTADO'=>1],
    ['ID_AREA'=>3,'NOMBRE'=>'Administración QA','ESTADO'=>1],
    ['ID_AREA'=>4,'NOMBRE'=>'Área inactiva QA','ESTADO'=>0],
]);
$c=Content::institutional(Content::emptyJob());
$c['name']='Administrativo contable QA';
$c['area']='Administración';
$c['sector']='Servicio original sin equivalencia';
$c['groups']['purpose']=[Content::paragraph('Mantener información confiable.')];
$nested=Content::paragraph('Consultar novedades con el equipo.',true);
$nested['list']['level']=1;
$c['groups']['internal']=[Content::paragraph('Coordinar con Administración.',true),$nested,Content::paragraph('Comunicar los resultados.',true)];
$task=Content::paragraph('Reunir antecedentes y verificar datos.',true);
$task['html']='Reunir <strong>antecedentes</strong> y verificar datos.';
$c['groups']['tasks']=[$task,Content::paragraph('Liquidar honorarios médicos.',true),Content::paragraph('Registrar controles y pendientes.',true)];
$c['groups']['specific']=[Content::paragraph('Planificación, organización y seguimiento.',true),Content::paragraph('Calidad y orden en el trabajo.',true)];
$c['groups']['contribution']=[Content::paragraph('Información confiable para tomar decisiones.'),Content::paragraph('Conciliaciones y archivo para dar continuidad al Hospital.')];
$c['groups']['commitment']=[Content::paragraph('Cumplir los protocolos.',true),Content::paragraph('Trabajar de manera segura.',true)];
$cell=Content::paragraph('Registro de prueba');
$cell['html']='<em>Registro de prueba</em>';
$c['additional']=[['key'=>'firma-qa','title'=>'Firmas y registros','blocks'=>[
    ['id'=>(string)Str::uuid(),'kind'=>'table','text'=>"Responsable | Registro\nAdministración | Registro de prueba",'rows'=>[
        ['cells'=>[['text'=>'Responsable','colspan'=>1,'blocks'=>[Content::paragraph('Responsable')]],['text'=>'Registro','colspan'=>1,'blocks'=>[Content::paragraph('Registro')]]]],
        ['cells'=>[['text'=>'Administración','colspan'=>1,'blocks'=>[Content::paragraph('Administración')]],['text'=>'Registro de prueba','colspan'=>1,'blocks'=>[$cell]]]]
    ]]
]]];
$library=new Library;
$v=$library->create('descriptivos',$c,'fixture',(string)Str::uuid());
DB::table('bib_versions')->where('id',$v['id'])->update(['number'=>0]);
$published=$library->publish($v['id'],0,'Base de prueba publicada',true,'fixture');
$draft=$library->draft($published['id'],'fixture');
file_put_contents($root.'/unified-editor.json',Content::json([
    'draft'=>$draft['id'],'document'=>$draft['document_id'],'published'=>$published['id'],
    'content'=>Content::decode($draft['payload_json']),
    'publishedContent'=>Content::decode($published['payload_json']),
    'publishedPayloadHash'=>hash('sha256',$published['payload_json']),
]));
echo "Fixture del editor unificado listo.\n";
