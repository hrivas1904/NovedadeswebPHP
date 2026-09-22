<?php

namespace App\Services\Biblioteca;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Uploads
{
    public function stage(UploadedFile $file,string $actor): array {
        $ext=strtolower($file->getClientOriginalExtension());
        if(!in_array($ext,['docx','pdf'])||$file->getSize()>50*1024*1024)Content::fail('Seleccioná un DOCX o PDF de hasta 50 MB.');
        $head=file_get_contents($file->getRealPath(),false,null,0,5);
        if(($ext==='pdf'&&!str_starts_with($head,'%PDF-'))||($ext==='docx'&&!str_starts_with($head,'PK')))Content::fail('El archivo no corresponde al formato indicado.');
        $sha=hash_file('sha256',$file->getRealPath());$snapshot='originales/'.$sha.'.'.$ext;$path=config('biblioteca.storage').'/'.$snapshot;
        File::ensureDirectoryExists(dirname($path));if(!is_file($path))copy($file->getRealPath(),$path);
        $process=new Process([config('biblioteca.python'),resource_path('biblioteca/parse_document.py'),$path]);$process->setTimeout(90);$process->setEnv(['PYTHONIOENCODING'=>'utf-8']);
        $parsed=null;$error=null;
        try {$process->mustRun();$parsed=Content::decode($process->getOutput());if(!isset($parsed['sections']))throw new \RuntimeException('Lectura inválida.');}
        catch(\Throwable $e){$error='No se pudo interpretar el archivo. Revisá que no esté dañado o protegido y que el lector Python esté disponible.';}
        $stage=['id'=>(string)Str::uuid(),'name'=>basename($file->getClientOriginalName()),'format'=>strtoupper($ext),'sha256'=>$sha,'snapshot'=>$snapshot,'byte_size'=>$file->getSize(),'parsed'=>$parsed,'error'=>$error];
        DB::table('bib_uploads')->insert(['id'=>$stage['id'],'actor'=>$actor,'created_at'=>Library::now(),'payload_json'=>Content::json($stage)]);return $stage;
    }
    public function get(string $id): array {$r=DB::table('bib_uploads')->where('id',$id)->first();abort_unless($r,404);return ['row'=>(array)$r,'stage'=>Content::decode($r->payload_json)];}
    public function confirm(string $id,string $destination,?string $document,?array $content,string $actor): array {
        return DB::transaction(function()use($id,$destination,$document,$content,$actor) {
            $row=DB::table('bib_uploads')->where('id',$id)->lockForUpdate()->first();abort_unless($row,404);
            if($row->document_id)return ['document_id'=>$row->document_id,'id'=>$row->version_id];
            $u=Content::decode($row->payload_json);$library=new Library;
            if(!in_array($destination,['new','version','document','support']))Content::fail('Seleccioná el destino de la carga.');
            if($u['error']&&$destination!=='support')Content::fail('La lectura falló. Sólo puede conservarse como fuente complementaria.');
            $c=$content?Content::validateJob($content):($u['parsed']?Content::institutional(Content::fromParsed($u['parsed'])):null);
            if($destination==='new') {
                if(!$c||!trim($c['name'])||!trim($c['area']))Content::fail('Completá nombre y área de la propuesta antes de incorporar.');
                $document=(string)Str::uuid();DB::table('bib_documents')->insert(['id'=>$document,'kind'=>'descriptivos','title'=>$c['name'],'area'=>$c['area'],'aliases'=>'[]','created_at'=>Library::now(),'origin'=>'imported']);
            } else { $d=$library->document($document??'');if($d['kind']!=='descriptivos')Content::fail('Seleccioná un descriptivo de puesto.');DB::table('bib_documents')->where('id',$document)->lockForUpdate()->first(); }
            $source=(string)Str::uuid();$raw=['upload'=>$u,'destination'=>$destination,'source'=>['relationship'=>$destination==='support'?'support':'version','present'=>1,'ingestion'=>'upload']];
            DB::table('bib_sources')->insert(['id'=>$source,'document_id'=>$document,'source_id'=>$source,'name'=>$u['name'],'format'=>$u['format'],'sha256'=>$u['sha256'],'snapshot'=>$u['snapshot'],'read_state'=>$u['error']?'Error':(empty($u['parsed']['warnings'])?'Correcta':'Con advertencias'),'raw_json'=>Content::json($raw),'extraction_json'=>$u['parsed']?Content::json($u['parsed']):null]);
            $v=null;
            if($destination!=='support') {
                $v=$library->insertVersion($document,$c,$actor,null,['state'=>'Vigencia no verificada','origin'=>'imported','source_version_id'=>$source,'parsed_json'=>Content::json(Content::toParsed($c))]);
                foreach($u['parsed']['warnings']??[] as $f) DB::table('bib_findings')->insert(['id'=>(string)Str::uuid(),'document_id'=>$document,'version_id'=>$source,'status'=>'Pendiente','resolution'=>'','payload_json'=>Content::json($f)]);
            }
            $library->event($document,$v['id']??null,'importar_'.$destination,$actor,null,['source'=>$source,'version'=>$v]);
            DB::table('bib_uploads')->where('id',$id)->update(['document_id'=>$document,'version_id'=>$v['id']??null]);return ['document_id'=>$document,'id'=>$v['id']??null];
        });
    }
    public static function sourcePath(array $source): string {
        $root=realpath(config('biblioteca.storage'));$file=realpath(config('biblioteca.storage').'/'.$source['snapshot']);
        abort_unless($root&&$file&&str_starts_with($file,$root.DIRECTORY_SEPARATOR)&&is_file($file),404,'Archivo fuente no disponible.');
        abort_unless(hash_file('sha256',$file)===$source['sha256'],409,'La copia fuente no coincide con su huella registrada.');return $file;
    }
}
