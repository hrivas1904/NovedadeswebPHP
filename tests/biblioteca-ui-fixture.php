<?php

require __DIR__.'/biblioteca-versions-fixture.php';
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$library=new Library;
for($i=1;$i<=155;$i++){
    $content=Content::institutional(Content::emptyJob());
    $content['name']='Prueba '.$i;$content['area']=$i%2?'Área Z':'Área A';
    $content['groups']['purpose']=[Content::paragraph('Prueba del editor.')];
    $content['groups']['tasks']=[Content::paragraph('Revisar documentos.')];
    $library->create('descriptivos',$content,'fixture',(string)Str::uuid());
    DB::table('empleados')->insert(['LEGAJO'=>2000+$i,'COLABORADOR'=>'Colaborador '.str_pad($i,3,'0',STR_PAD_LEFT),'ID_CATEG'=>1,'ESTADO'=>'ACTIVO']);
}
$user=\App\Models\User::findOrFail(2);
$service=new \App\Services\Biblioteca\Acceptances;
$context=$service->context($user);
$receipt=$service->accept($user,['version_id'=>$context['entry']['version']['id'],'assignment_token'=>$context['token'],'content_hash'=>$context['hash'],'confirmed'=>true]);
for($i=1;$i<=155;$i++){
    $copy=(array)$receipt;$copy['id']=(string)Str::uuid();$copy['legajo']=2000+$i;
    $copy['employee_name']='Colaborador '.str_pad($i,3,'0',STR_PAD_LEFT);
    $copy['version_number']=$i;$copy['assignment_token']=hash('sha256','fixture-'.$i);
    DB::table('bib_acceptances')->insert($copy);
}
echo "Listados de prueba preparados.\n";
