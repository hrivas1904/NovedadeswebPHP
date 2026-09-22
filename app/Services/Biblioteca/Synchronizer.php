<?php
namespace App\Services\Biblioteca;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Cache,DB};

class Synchronizer {
    public function run(string $actor):array {
        $root=realpath(config('biblioteca.source_root')??'');if(!$root)Content::fail('La carpeta de fuentes no está configurada o no está disponible.');
        $lock=Cache::store('file')->lock('biblioteca-sync',600);if(!$lock->get())Content::fail('Ya hay una sincronización en curso.');
        try {
            $folders=Content::decode(file_get_contents(resource_path('biblioteca/sources.json')))['folders'];
            $known=[];foreach(DB::table('bib_sources')->get() as $row) {
                $raw=Content::decode($row->raw_json);$relative=$raw['source']['relative_path']??null;
                if(!$relative)continue;$key=str_replace('\\','/',$relative);
                if(!isset($known[$key])||($raw['version']['imported_at']??$raw['imported_at']??'')>($known[$key]['time']))$known[$key]=['row'=>(array)$row,'raw'=>$raw,'time'=>$raw['version']['imported_at']??$raw['imported_at']??''];
            }
            $report=['analyzed'=>0,'unchanged'=>0,'new'=>0,'modified'=>0,'removed'=>0,'restored'=>0,'errors'=>[]];$seen=[];$available=[];
            foreach($folders as $folder) {
                $path=realpath($root.DIRECTORY_SEPARATOR.$folder['path']);
                if(!$path||!str_starts_with($path,$root.DIRECTORY_SEPARATOR)){$report['errors'][]='Carpeta no disponible: '.$folder['path'];continue;}
                $available[]=str_replace('\\','/',$folder['path']).'/';
                foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,\FilesystemIterator::SKIP_DOTS)) as $file) {
                    if(!$file->isFile()||$file->isLink()||str_starts_with($file->getFilename(),'~$')||!in_array(strtolower($file->getExtension()),['docx','pdf']))continue;
                    $real=$file->getRealPath();if(!str_starts_with($real,$root.DIRECTORY_SEPARATOR))continue;
                    $relative=str_replace('\\','/',substr($real,strlen($root)+1));$seen[$relative]=true;$report['analyzed']++;$sha=hash_file('sha256',$real);$old=$known[$relative]??null;
                    if($old && $old['row']['sha256']===$sha) {
                        $report['unchanged']++;$raw=$old['raw'];if(empty($raw['source']['present'])){ $raw['source']['present']=1;DB::table('bib_sources')->where('id',$old['row']['id'])->update(['raw_json'=>Content::json($raw)]);(new Library)->event($old['row']['document_id'],null,'restaurar_fuente',$actor,$old['raw'],$raw);$report['restored']++; }continue;
                    }
                    try {
                        $uploads=new Uploads;$u=$uploads->stage(new UploadedFile($real,$file->getFilename(),null,null,true),$actor);
                        if($u['error']){$report['errors'][]=$relative.': '.$u['error'];continue;}
                        $c=Content::institutional(Content::fromParsed($u['parsed']));$c['name']=$c['name']?:$file->getBasename('.'.$file->getExtension());$c['area']=$c['area']?:$folder['label'];
                        $result=$uploads->confirm($u['id'],$old?'version':'new',$old['row']['document_id']??null,$c,$actor);$v=(new Library)->version($result['id']);
                        $s=DB::table('bib_sources')->where('id',$v['source_version_id'])->first();$raw=Content::decode($s->raw_json);
                        $raw['source']=['relative_path'=>$relative,'folder'=>$folder['label'],'name'=>$file->getFilename(),'present'=>1,'ingestion'=>'folder','relationship'=>'version'];$raw['imported_at']=Library::now();
                        DB::table('bib_sources')->where('id',$s->id)->update(['source_id'=>$old['row']['source_id']??$s->id,'raw_json'=>Content::json($raw)]);$report[$old?'modified':'new']++;
                    }catch(\Throwable $e){$report['errors'][]=$relative.': no se pudo incorporar. Revisar la carga pendiente.';}
                }
            }
            foreach($known as $relative=>$old)if(!isset($seen[$relative])&&array_filter($available,fn($prefix)=>str_starts_with($relative,$prefix))&&!empty($old['raw']['source']['present'])) {
                $raw=$old['raw'];$raw['source']['present']=0;DB::table('bib_sources')->where('id',$old['row']['id'])->update(['raw_json'=>Content::json($raw)]);(new Library)->event($old['row']['document_id'],null,'retirar_fuente_de_carpeta',$actor,$old['raw'],$raw);$report['removed']++;
            }
            return $report;
        }finally{$lock->release();}
    }
}
