<?php

namespace App\Services\Biblioteca;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Library
{
    public static function manages($user): bool { return $user && $user->rol === 'Administrador/a' && ($user->estado ?? 'ACTIVO') === 'ACTIVO'; }
    public static function now(): string { return CarbonImmutable::now('UTC')->format('Y-m-d\TH:i:s.v\Z'); }
    public static function today(): string { return CarbonImmutable::now(config('biblioteca.timezone'))->format('Y-m-d'); }
    public static function actor($user): string { return 'usuario:'.$user->getAuthIdentifier().' · '.($user->name ?: $user->username); }
    public function document(string $id): array { $d=DB::table('bib_documents')->where('id',$id)->first(); abort_unless($d,404); return (array)$d; }
    public function version(string $id): array { $v=DB::table('bib_versions')->where('id',$id)->first(); abort_unless($v,404); return (array)$v; }
    public function history(string $id): array { return DB::table('bib_versions')->where('document_id',$id)->orderByDesc('number')->get()->map(fn($v)=>(array)$v)->all(); }
    public function entry(string $id, ?string $version=null): array {
        $d=$this->document($id);
        $v=$version?$this->version($version):((array)DB::table('bib_versions')->where('document_id',$id)->orderByRaw("CASE WHEN current_slot=1 THEN 0 WHEN origin='imported' AND state NOT IN ('Borrador','Histórico / retirado') THEN 1 WHEN state='Borrador' THEN 2 ELSE 3 END")->orderByDesc('number')->first());
        abort_unless($v && $v['document_id']===$id,404); return $this->present($d,$v);
    }
    public function present(array $d,array $v): array {
        $c=Content::decode($v['payload_json']); $p=Content::decode($v['parsed_json']); $job=$d['kind']==='descriptivos';
        $title=$job?($v['published_at']?($p['metadata']['publishedName']??$c['name']??$d['title']):($d['canonical_name']?:($c['name']??$d['title']))):($c['title']??$d['title']);
        $area=$job?($v['published_at']?($p['metadata']['publishedArea']??$c['area']??$d['area']):($d['normalized_area']?:($c['area']??$d['area']))):($c['responsible']??'');
        $label=$v['state'];
        if(!$job && $label==='Importado') $label=($c['declaredState']??'')==='vigente'?(!empty($c['approvalRecord'])?'Vigente declarado':'Vigencia declarada'):'Borrador importado';
        if($label==='Publicado') $label='Vigente';
        $next=null;
        if(!$job && ($date=$c['lastReview']??$c['validFrom']??null)) $next=CarbonImmutable::parse($date)->addMonthsNoOverflow($c['reviewMonths']??12)->format('Y-m-d');
        $status=[];
        if(!$job) { if(($d['kind']==='politicas'&&!DB::table('bib_policy_approvals')->where('version_id',$v['id'])->exists())||empty($c['approvalRecord']))$status[]=$d['kind']==='politicas'?'Pendiente de aprobación de Gerencia':'Falta instrumento de aprobación';if($next && $next<self::today())$status[]='Revisión vencida';elseif($next && $next<=CarbonImmutable::now(config('biblioteca.timezone'))->addDays(60)->format('Y-m-d'))$status[]='Próxima revisión'; }
        return ['document'=>$d,'version'=>$v,'content'=>$c,'parsed'=>$p,'title'=>$title,'area'=>$area,'state'=>$label,'nextReview'=>$next,'situation'=>implode(' · ',$status),'job'=>$job];
    }
    public function entries(bool $allVersions=false): array {
        $docs=DB::table('bib_documents')->get()->keyBy('id'); $out=[];$seen=[];
        $versions=DB::table('bib_versions')->orderByRaw("CASE WHEN current_slot=1 THEN 0 WHEN origin='imported' AND state NOT IN ('Borrador','Histórico / retirado') THEN 1 WHEN state='Borrador' THEN 2 ELSE 3 END")->orderByDesc('number')->get();
        foreach($versions as $v) { if(!$allVersions&&isset($seen[$v->document_id])) continue; $seen[$v->document_id]=true; $out[]=$this->present((array)$docs[$v->document_id],(array)$v); }
        return $out;
    }
    public function filter(array $entries,array $filters): array {
        $tokens=array_filter(explode(' ',Content::normalize($filters['q']??'')));
        $entries=array_values(array_filter($entries,function($e) use($filters,$tokens) {
            if(!empty($filters['kind']) && $e['document']['kind']!==$filters['kind']) return false;
            if(!empty($filters['area']) && $e['area']!==$filters['area']) return false;
            if(!empty($filters['state']) && $e['state']!==$filters['state']) return false;
            if(!empty($filters['review']) && $e['version']['review_state']!==$filters['review']) return false;
            if(empty($filters['history']) && $e['version']['state']==='Histórico / retirado') return false;
            $text=Content::normalize($e['title'].' '.$e['area'].' '.$e['document']['code'].' '.$e['document']['aliases'].' '.$e['version']['search_text']);
            foreach($tokens as $t) if(!str_contains($text,$t)) return false;
            return true;
        }));
        usort($entries,fn($a,$b)=>strnatcasecmp(Content::normalize($a['title']),Content::normalize($b['title'])));return $entries;
    }
    public function events(string $id): array { return DB::table('bib_events')->where('document_id',$id)->orderByDesc('happened_at')->get()->map(fn($x)=>(array)$x)->all(); }
    public function event(string $doc,?string $version,string $action,string $actor,mixed $before,mixed $after): void {
        DB::table('bib_events')->insert(['id'=>(string)Str::uuid(),'document_id'=>$doc,'version_id'=>$version,'action'=>$action,'actor'=>$actor,'happened_at'=>self::now(),'before_json'=>Content::json($before),'after_json'=>Content::json($after)]);
    }
    public function lock(string $id,int $revision): array {
        $v=$this->version($id); DB::table('bib_documents')->where('id',$v['document_id'])->lockForUpdate()->first();
        $v=(array)DB::table('bib_versions')->where('id',$id)->lockForUpdate()->first();
        abort_if((int)$v['revision']!==$revision,409,'Otra persona modificó esta versión. Recargá antes de guardar.'); return $v;
    }
    public function create(string $kind,array $c,string $actor,string $requestId): array {
        abort_unless(isset(config('biblioteca.collections')[$kind]),422);
        $c=$kind==='descriptivos'?Content::validateJob($c):Content::validateManual($c);
        return DB::transaction(function() use($kind,$c,$actor,$requestId) {
            $old=DB::table('bib_requests')->where('id',$requestId)->first();if($old)return $this->version($old->version_id);
            $id=(string)Str::uuid();$now=self::now();$job=$kind==='descriptivos';
            if(!$job && DB::table('bib_documents')->where('code',$c['code'])->exists()) Content::fail('Ya existe un documento con ese código.');
            DB::table('bib_documents')->insert(['id'=>$id,'kind'=>$kind,'title'=>$job?$c['name']:$c['title'],'area'=>$job?$c['area']:$c['responsible'],'code'=>$job?null:$c['code'],'aliases'=>'[]','created_at'=>$now,'origin'=>'system']);
            if(!$job){$c['id']=$id;$c['sourceId']='';$c['sourceHeading']=$c['title'];$c['history']=[];$c['declaredState']='borrador';}
            $v=$this->insertVersion($id,$c,$actor);$this->event($id,$v['id'],'crear_borrador',$actor,null,$v);
            DB::table('bib_requests')->insert(['id'=>$requestId,'document_id'=>$id,'version_id'=>$v['id']]);return $v;
        });
    }
    public function insertVersion(string $id,array $c,string $actor,?array $base=null,array $extra=[]): array {
        $job=$this->document($id)['kind']==='descriptivos';$now=self::now();$vid=(string)Str::uuid();
        $v=['id'=>$vid,'document_id'=>$id,'number'=>1+(int)DB::table('bib_versions')->where('document_id',$id)->max('number'),'revision'=>0,'state'=>'Borrador','review_state'=>'Pendiente','current_slot'=>null,'based_on'=>$base['id']??null,'source_version_id'=>$base['source_version_id']??null,'origin'=>'system','created_at'=>$now,'updated_at'=>$now,'created_by'=>$actor,'published_at'=>null,'published_by'=>null,'resolution'=>'','change_reason'=>'','payload_json'=>Content::json($c),'parsed_json'=>$job?Content::json(Content::toParsed($c)):null,'search_text'=>Content::plain(Content::json($c))];
        $v=array_merge($v,$extra);DB::table('bib_versions')->insert($v);return $v;
    }
    public function draft(string $id,string $actor): array {
        return DB::transaction(function()use($id,$actor) {
            $v=$this->version($id);DB::table('bib_documents')->where('id',$v['document_id'])->lockForUpdate()->first();
            $v=$this->version($id);if($v['state']==='Borrador'&&!$v['published_at'])return $v;
            $existing=DB::table('bib_versions')->where('based_on',$id)->where('state','Borrador')->first();if($existing)return (array)$existing;
            $c=Content::decode($v['payload_json']);$doc=$this->document($v['document_id']);
            if($doc['kind']==='descriptivos')$c=Content::institutional($c);else {$parts=explode('.',$c['version']);$parts[count($parts)-1]=(int)end($parts)+1;$c['version']=implode('.',$parts);$c['declaredState']='borrador';if($doc['kind']==='politicas'){$c['approvalRecord']=null;$c['approver']='';}}
            $next=$this->insertVersion($v['document_id'],$c,$actor,$v);$this->event($v['document_id'],$next['id'],'crear_borrador_desde_version',$actor,['version'=>$id],$next);return $next;
        });
    }
    public function save(string $id,int $revision,array $content,string $actor): array {
        return DB::transaction(function()use($id,$revision,$content,$actor) {
            $v=$this->lock($id,$revision);if($v['state']!=='Borrador'||$v['published_at'])Content::fail('Creá un borrador para editar esta versión.');
            $d=$this->document($v['document_id']);$job=$d['kind']==='descriptivos';$old=Content::decode($v['payload_json']);
            $c=$job?Content::validateJob($content):Content::validateManual($content);
            if(!$job) {
                foreach(['id','code','collection'] as $key)if($c[$key]!==$old[$key])Content::fail('El código y la sección identifican al documento y no se cambian.');
                foreach(['history','sourceId','sourceHeading'] as $key)$c[$key]=$old[$key];$c['declaredState']='borrador';
            }
            DB::table('bib_versions')->where('id',$id)->update(['payload_json'=>Content::json($c),'parsed_json'=>$job?Content::json(Content::toParsed($c)):null,'search_text'=>Content::plain(Content::json($c)),'updated_at'=>self::now(),'revision'=>$revision+1]);
            $next=$this->version($id);$this->event($d['id'],$id,'guardar_borrador',$actor,$v,$next);return $next;
        });
    }
    public function publish(string $id,int $revision,string $reason,bool $confirmed,string $actor,bool $fromReview=false,bool $policyApproval=false): array {
        if(!$confirmed) Content::fail('Confirmá la publicación.');
        return DB::transaction(function()use($id,$revision,$reason,$actor,$fromReview,$policyApproval) {
            $v=$this->lock($id,$revision);$d=$this->document($v['document_id']);$c=Content::decode($v['payload_json']);$job=$d['kind']==='descriptivos';
            if($d['kind']==='politicas') {
                abort_unless($policyApproval && Governance::canApprove(auth()->user()),403,'Esta política requiere la aprobación de Gerencia desde su ficha.');
                $c['approver']=auth()->user()->name.' · Gerente';
                $c['approvalRecord']='Aprobación registrada en Biblioteca Institucional por '.Library::actor(auth()->user()).' · '.self::now().' · versión '.$id;
                $c['validFrom']=$c['validFrom']?:self::today();
            }
            if($v['published_at']||$v['state']==='Histórico / retirado'||(!$fromReview&&$v['state']!=='Borrador')) Content::fail('Creá un nuevo borrador para publicar.');
            if($job) {
                $c=Content::validateJob($c);
                if(!$fromReview && (empty($c['groups']['purpose'])||empty($c['groups']['tasks']))) Content::fail('Completá propósito y al menos una tarea para publicar.');
                if($v['source_version_id'] && DB::table('bib_sources')->where('id',$v['source_version_id'])->where('read_state','Error')->exists() && $fromReview) Content::fail('Corregí el error de lectura en un borrador antes de publicar.');
            } else {
                $c=Content::validateManual($c);
                if(!$c['sections']||!array_filter(array_column($c['sections'],'text'))||array_filter($c['sections'],fn($s)=>!trim($s['title'])))Content::fail('Completá contenido y título de cada sección.');
                if(empty($c['validFrom'])||!trim($c['approver'])||!trim($c['approvalRecord']??''))Content::fail('Para publicar, completá fecha de vigencia, aprobación e instrumento de aprobación.');
                if($c['validFrom']>self::today())Content::fail('La fecha de vigencia es futura. Conservá el borrador hasta esa fecha.');
                foreach($this->history($d['id']) as $other)if($other['id']!==$id && $other['state']!=='Borrador' && (Content::decode($other['payload_json'])['version']??null)===$c['version'])Content::fail('Ese número de versión ya existe. Indicá uno nuevo.');
                $c['declaredState']='vigente';array_unshift($c['history'],['version'=>$c['version'],'date'=>self::today(),'detail'=>$reason?:'Publicación confirmada.']);
            }
            foreach($this->history($d['id']) as $previous)if($previous['id']!==$id && ($previous['current_slot']||(!$job&&$previous['state']==='Importado'))) {
                DB::table('bib_versions')->where('id',$previous['id'])->update(['state'=>'Histórico / retirado','current_slot'=>null,'revision'=>$previous['revision']+1,'updated_at'=>self::now()]);
                $this->event($d['id'],$previous['id'],'pasar_a_historico',$actor,$previous,$this->version($previous['id']));
            }
            $p=$job?Content::toParsed($c):null;if($p){$p['metadata']['publishedName']=$d['canonical_name']?:$c['name'];$p['metadata']['publishedArea']=$d['normalized_area']?:$c['area'];}
            DB::table('bib_versions')->where('id',$id)->update(['state'=>$job?'Vigente':'Publicado','review_state'=>'Validado','current_slot'=>1,'published_at'=>self::now(),'published_by'=>$actor,'updated_at'=>self::now(),'change_reason'=>$reason?:'Publicación confirmada.','resolution'=>$reason?:'Validación y publicación confirmadas.','revision'=>$revision+1,'payload_json'=>Content::json($c),'parsed_json'=>$p?Content::json($p):null,'search_text'=>Content::plain(Content::json($c))]);
            $this->resolveFindings($v,$reason?:'Validación y publicación confirmadas.');
            $next=$this->version($id);
            if($d['kind']==='politicas') {
                DB::table('bib_policy_approvals')->insert(['version_id'=>$id,'user_id'=>auth()->id(),'approver'=>auth()->user()->name,'approved_at'=>self::now(),'content_hash'=>hash('sha256',$next['payload_json'])]);
                $this->event($d['id'],$id,'aprobar_politica',$actor,null,['version'=>$id,'content_hash'=>hash('sha256',$next['payload_json'])]);
            }
            $this->event($d['id'],$id,'publicar_version',$actor,$v,$next);return $next;
        });
    }
    public function retire(string $id,int $revision,string $reason,string $actor): array {
        if(!trim($reason))Content::fail('Registrá el motivo del retiro.');
        return DB::transaction(function()use($id,$revision,$reason,$actor) {
            $v=$this->lock($id,$revision);if($v['state']==='Histórico / retirado')Content::fail('La versión ya está retirada.');
            DB::table('bib_versions')->where('id',$id)->update(['state'=>'Histórico / retirado','current_slot'=>null,'revision'=>$revision+1,'change_reason'=>$reason,'updated_at'=>self::now()]);
            $next=$this->version($id);$this->event($v['document_id'],$id,'retirar_version',$actor,$v,$next);return $next;
        });
    }
    public function review(string $id,int $revision,int $documentRevision,array $input,string $actor): array {
        return DB::transaction(function()use($id,$revision,$documentRevision,$input,$actor) {
            $v=$this->lock($id,$revision);$d=$this->document($v['document_id']);abort_unless($d['kind']==='descriptivos',422);
            abort_if($d['revision']!=$documentRevision,409,'La identidad del puesto cambió. Recargá antes de guardar.');
            $action=$input['action'];$name=trim($input['canonical_name']??'');$area=trim($input['normalized_area']??'');$comment=trim($input['comment']??'');
            if($action!=='save' && (!$name||!$area||empty($input['confirmed'])))Content::fail('Completá nombre y área y confirmá la revisión.');
            if($action!=='save'&&$v['state']==='Histórico / retirado')Content::fail('La versión es histórica. Creá un nuevo borrador.');
            if($action!=='save'&&$v['source_version_id']&&DB::table('bib_sources')->where('id',$v['source_version_id'])->where('read_state','Error')->exists())Content::fail('Corregí el error de lectura antes de validar.');
            $aliases=array_values(array_filter(array_map('trim',preg_split('/\r?\n/',$input['aliases']??''))));
            DB::table('bib_documents')->where('id',$d['id'])->update(['canonical_name'=>$name?:null,'normalized_area'=>$area?:null,'aliases'=>Content::json($aliases),'observation'=>$input['observation']??'','revision'=>$documentRevision+1]);
            $resolution=$comment?:($action==='save'?($v['resolution']??''):'Revisión confirmada: nombre, área y contenido verificados.');
            if($action==='publish') $next=$this->publish($id,$revision,$resolution,true,$actor,true);
            else {
                DB::table('bib_versions')->where('id',$id)->update(['review_state'=>$action==='validate'?'Validado':($v['review_state']==='Validado'?'Validado':'En revisión'),'resolution'=>$resolution,'revision'=>$revision+1,'updated_at'=>self::now()]);
                if($action==='validate')$this->resolveFindings($v,$resolution);$next=$this->version($id);
            }
            $this->event($d['id'],$id,'revisar_'.$action,$actor,['document'=>$d,'version'=>$v],['document'=>$this->document($d['id']),'version'=>$next]);return $next;
        });
    }
    private function resolveFindings(array $v,string $reason): void {
        DB::table('bib_findings')->where('document_id',$v['document_id'])->where('version_id',$v['source_version_id']?:$v['id'])->update(['status'=>'Resuelto','resolution'=>$reason]);
    }
}
