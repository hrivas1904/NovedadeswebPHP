<?php

namespace App\Services\Biblioteca;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Importer
{
    public function import(string $file,bool $validateOnly=false): array
    {
        $bundle=Content::decode(file_get_contents($file)); $root=realpath(dirname($file));
        if(($bundle['format']??'')!=='hp3c-biblioteca-migration-1') throw new \RuntimeException('Formato de migración desconocido.');
        $hash=hash_file('sha256',$file);$tables=$bundle['tables'];
        foreach($bundle['files'] as $f) {
            $path=realpath($root.DIRECTORY_SEPARATOR.$f['path']);
            if(!$path||!str_starts_with($path,$root.DIRECTORY_SEPARATOR)||hash_file('sha256',$path)!==$f['sha256'])throw new \RuntimeException('Archivo faltante o modificado: '.$f['path']);
        }
        $report=['bundle'=>$hash,'sourceCounts'=>$bundle['counts'],'files'=>count($bundle['files']),'createdAt'=>$bundle['createdAt']];
        if($validateOnly)return $report+['validated'=>true];
        if(DB::table('bib_migrations')->where('sha256',$hash)->exists())return $this->verify($bundle,$hash)+['alreadyImported'=>true];
        if(DB::table('bib_documents')->exists())throw new \RuntimeException('La biblioteca destino ya contiene datos. No se sobrescriben ediciones con otra migración.');
        foreach($bundle['files'] as $f) {
            $target=config('biblioteca.storage').DIRECTORY_SEPARATOR.$f['path'];
            File::ensureDirectoryExists(dirname($target));
            if(is_file($target)) { if(hash_file('sha256',$target)!==$f['sha256'])throw new \RuntimeException('El archivo de destino difiere: '.$f['path']); }
            else { if(!copy($root.DIRECTORY_SEPARATOR.$f['path'],$target))throw new \RuntimeException('No se pudo copiar '.$f['path']); }
        }
        DB::transaction(function()use($tables,$report,$hash) {
            foreach($tables as $table=>$rows) foreach(array_chunk($rows,15) as $chunk) {
                $values=[]; foreach($chunk as $row) {$json=Content::json($row);$values[]=['table_name'=>$table,'record_key'=>(string)($row['id']??$row['key']??hash('sha256',$json)),'payload_json'=>$json,'sha256'=>hash('sha256',$json)];}
                if($values)DB::table('bib_legacy_records')->insert($values);
            }
            $managed=collect($tables['managed_versions'])->groupBy('candidate_id');$aliases=collect($tables['aliases'])->groupBy('candidate_id');
            foreach($tables['candidates'] as $d) {
                $v=$managed[$d['id']]->sortBy(fn($v)=>($v['document_state']==='Vigente'?0:($v['document_state']==='Borrador'?2000:1000))-$v['number'])->first();
                $p=Content::decode($v['parsed_json']);$c=$v['editor_json']?Content::decode($v['editor_json']):Content::fromParsed($p);
                DB::table('bib_documents')->insert(['id'=>$d['id'],'kind'=>'descriptivos','title'=>$d['canonical_name']?:($c['name']?:($p['title']??'Documento sin título')),'area'=>$d['normalized_area']?:$c['area'],'code'=>null,'canonical_name'=>$d['canonical_name'],'normalized_area'=>$d['normalized_area'],'aliases'=>Content::json(isset($aliases[$d['id']])?$aliases[$d['id']]->pluck('value')->all():[]),'observation'=>$d['observation'],'revision'=>$d['revision'],'created_at'=>$d['created_at'],'origin'=>'imported']);
            }
            foreach($tables['managed_versions'] as $v) {
                $p=Content::decode($v['parsed_json']);$c=$v['editor_json']?Content::decode($v['editor_json']):Content::fromParsed($p);
                DB::table('bib_versions')->insert(['id'=>$v['id'],'document_id'=>$v['candidate_id'],'number'=>$v['number'],'revision'=>$v['revision'],'state'=>$v['document_state'],'review_state'=>$v['review_state'],'current_slot'=>$v['document_state']==='Vigente'?1:null,'based_on'=>$v['based_on'],'source_version_id'=>$v['source_version_id'],'origin'=>$v['origin'],'created_at'=>$v['created_at'],'updated_at'=>$v['updated_at'],'created_by'=>$v['created_by'],'published_at'=>$v['published_at'],'published_by'=>$v['published_by'],'resolution'=>$v['resolution'],'change_reason'=>$v['change_reason'],'payload_json'=>Content::json($c),'parsed_json'=>$v['parsed_json'],'search_text'=>$p['rawText'].' '.Content::plain(Content::json($c))]);
            }
            $manuals=collect($tables['institutional_versions'])->groupBy('document_id');
            foreach($tables['institutional_documents'] as $d) {
                $v=$manuals[$d['id']]->sortByDesc('number')->first();$c=Content::decode($v['payload_json']);
                DB::table('bib_documents')->insert(['id'=>$d['id'],'kind'=>$d['collection'],'title'=>$c['title'],'area'=>$c['responsible'],'code'=>$d['code'],'aliases'=>'[]','created_at'=>$d['created_at'],'origin'=>'imported']);
            }
            foreach($tables['institutional_versions'] as $v) {
                $c=Content::decode($v['payload_json']);
                DB::table('bib_versions')->insert(['id'=>$v['id'],'document_id'=>$v['document_id'],'number'=>$v['number'],'revision'=>$v['revision'],'state'=>$v['state'],'review_state'=>$v['state']==='Publicado'?'Validado':'Pendiente','current_slot'=>$v['state']==='Publicado'?1:null,'based_on'=>$v['based_on'],'source_version_id'=>null,'origin'=>$v['state']==='Importado'?'imported':'system','created_at'=>$v['created_at'],'updated_at'=>$v['updated_at'],'created_by'=>$v['created_by'],'published_at'=>$v['published_at'],'published_by'=>$v['published_by'],'resolution'=>'','change_reason'=>$v['change_reason'],'payload_json'=>$v['payload_json'],'parsed_json'=>null,'search_text'=>Content::plain(Content::json($c))]);
            }
            $sources=collect($tables['sources'])->keyBy('id');$extractions=collect($tables['extractions'])->groupBy('version_id');
            foreach($tables['versions'] as $v) {
                $s=$sources[$v['source_id']];$x=$extractions->get($v['id'],collect())->sortByDesc('created_at')->first();
                DB::table('bib_sources')->insert(['id'=>$v['id'],'document_id'=>$v['candidate_id'],'source_id'=>$v['source_id'],'name'=>$s['name'],'format'=>$s['format'],'sha256'=>$v['sha256'],'snapshot'=>'originales/'.$v['snapshot'],'read_state'=>$x['read_state']??'Error','raw_json'=>Content::json(['version'=>$v,'source'=>$s]),'extraction_json'=>$x['payload']??null]);
            }
            $manualSource=config('biblioteca.storage').'/manual/sources/hp3c-politicas_1.html';
            foreach($tables['institutional_documents'] as $d)if(!empty($d['source_sha'])&&is_file($manualSource))DB::table('bib_sources')->insert(['id'=>'manual-'.$d['id'],'document_id'=>$d['id'],'source_id'=>null,'name'=>'hp3c-politicas_1.html','format'=>'HTML','sha256'=>hash_file('sha256',$manualSource),'snapshot'=>'manual/sources/hp3c-politicas_1.html','read_state'=>'Correcta','raw_json'=>Content::json($d),'extraction_json'=>null]);
            foreach($tables['findings'] as $f)DB::table('bib_findings')->insert(['id'=>$f['id'],'document_id'=>$f['candidate_id'],'version_id'=>$f['version_id'],'status'=>$f['status'],'resolution'=>$f['resolution'],'payload_json'=>Content::json($f)]);
            foreach(['review_events','management_events','institutional_events'] as $table)foreach($tables[$table] as $e)DB::table('bib_events')->insert(['id'=>$table.':'.$e['id'],'document_id'=>$e['candidate_id']??$e['document_id']??null,'version_id'=>$e['version_id'],'action'=>$e['action']??'revision_documental','actor'=>$e['actor'],'happened_at'=>$e['happened_at'],'before_json'=>$e['before_json'],'after_json'=>$e['after_json']]);
            foreach($tables['upload_staging'] as $u)DB::table('bib_uploads')->insert(['id'=>$u['id'],'actor'=>$u['created_by'],'created_at'=>$u['created_at'],'payload_json'=>Content::json($u),'document_id'=>null,'version_id'=>null]);
            DB::table('bib_migrations')->insert(['sha256'=>$hash,'imported_at'=>Library::now(),'report_json'=>Content::json($report)]);
        });
        return $this->verify($bundle,$hash);
    }
    private function verify(array $bundle,string $hash): array {
        $counts=[];foreach($bundle['tables'] as $name=>$rows) {
            $actual=DB::table('bib_legacy_records')->where('table_name',$name)->get()->keyBy('record_key');
            if($actual->count()!==count($rows))throw new \RuntimeException('Conteo de archivo histórico diferente: '.$name);
            foreach($rows as $r) { $json=Content::json($r);$key=(string)($r['id']??$r['key']??hash('sha256',$json));if(!isset($actual[$key])||hash('sha256',$actual[$key]->payload_json)!==hash('sha256',$json))throw new \RuntimeException('Registro histórico diferente: '.$name); }
            $counts[$name]=count($rows);
        }
        foreach($bundle['files'] as $f) if(hash_file('sha256',config('biblioteca.storage').'/'.$f['path'])!==$f['sha256'])throw new \RuntimeException('Archivo de destino diferente: '.$f['path']);
        return ['bundle'=>$hash,'legacyVerified'=>$counts,'filesVerified'=>count($bundle['files']),'documents'=>DB::table('bib_documents')->count(),'versions'=>DB::table('bib_versions')->count(),'events'=>DB::table('bib_events')->count()];
    }
}
