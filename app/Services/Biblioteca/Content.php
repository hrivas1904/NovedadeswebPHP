<?php

namespace App\Services\Biblioteca;

use DOMDocument;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Content
{
    public static function json(mixed $value): string { return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); }
    public static function decode(?string $value): array { $decoded=$value ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : []; return is_array($decoded)?$decoded:[]; }
    public static function normalize(string $text): string { return trim(preg_replace('/\s+/u', ' ', mb_strtolower(Str::ascii($text)))); }
    public static function fail(string $message): never { throw ValidationException::withMessages(['biblioteca'=>$message]); }
    public static function paragraph(string $text, bool $bullet = false): array {
        return ['id'=>(string)Str::uuid(),'kind'=>'paragraph','text'=>$text] + ($bullet ? ['list'=>['id'=>'editor','level'=>0,'format'=>'bullet','start'=>1]] : []);
    }
    public static function emptyJob(): array {
        return ['name'=>'','area'=>'','sector'=>'','reportsTo'=>'','supervisor'=>'','supervises'=>'','groups'=>array_fill_keys(array_keys(config('biblioteca.groups')), []),'additional'=>[]];
    }
    public static function fromParsed(array $p): array {
        $c=self::emptyJob(); $c['sector']=(string)($p['metadata']['sector']??'');
        foreach(['name','area','reportsTo','supervisor','supervises'] as $key) $c[$key]=(string)($p['fields'][$key]??'');
        $c['name']=$c['name']?:($p['titleName']??$p['title']??'');
        $labels=['relaciones internas'=>'internal','relaciones externas'=>'external','formacion'=>'education','experiencia'=>'experience','conocimientos'=>'knowledge','genericas'=>'generic','especificas'=>'specific'];
        foreach($p['sections']??[] as $s) {
            if($s['key']==='identification') continue;
            if(in_array($s['key'], ['purpose','tasks','commitment','contribution'])) { $c['groups'][$s['key']]=array_merge($c['groups'][$s['key']],$s['blocks']); continue; }
            if(in_array($s['key'], ['context','profile','competencies'])) {
                $selected=null; $extra=[];
                foreach($s['blocks'] as $b) {
                    if($b['kind']==='paragraph' && preg_match('/^\s*[-•]?\s*(Relaciones internas|Relaciones externas|Formación|Experiencia|Conocimientos|(?:Competencias\s+)?Genéricas|(?:Competencias\s+)?Específicas)\s*:?\s*(.*)$/isu',$b['text'],$m)) {
                        $selected=$labels[preg_replace('/^competencias\s+/','',self::normalize($m[1]))];
                        if($m[2]!=='') { $b['text']=$m[2]; $c['groups'][$selected][]=$b; }
                    } elseif($selected) $c['groups'][$selected][]=$b; else $extra[]=$b;
                }
                if($extra) $c['additional'][]=array_merge($s,['blocks'=>$extra]);
            } elseif(!empty($s['blocks'])) $c['additional'][]=$s;
        }
        if(!empty($p['extras'])) $c['additional'][]=['key'=>'preserved','title'=>'Contenido adicional de la fuente','blocks'=>$p['extras']];
        return $c;
    }
    public static function institutional(array $c,bool $defaults=true): array {
        if($defaults && empty($c['groups']['generic']))$c['groups']['generic']=array_map(fn($s)=>self::paragraph($s,true),config('biblioteca.competencies'));
        foreach($c['groups']['commitment']??[] as $i=>$b) if($b['kind']==='paragraph' && trim($b['text'])!=='') {
            $b['text']=preg_replace('/^(?:[-•]|\d+[.)])\s+/u','',trim($b['text'])); $b['heading']=false;
            $b['list']=['id'=>'commitment','level'=>0,'format'=>'bullet','start'=>1]; $c['groups']['commitment'][$i]=$b;
        }
        return $c;
    }
    public static function validateJob(array $c): array {
        if(strlen(self::json($c))>1500000) self::fail('El descriptivo es demasiado extenso.');
        foreach(['name','area','sector','reportsTo','supervisor','supervises'] as $key) if(!isset($c[$key]) || !is_string($c[$key]) || mb_strlen($c[$key])>4000) self::fail('Campo de identificación inválido: '.$key);
        if(!trim($c['name']) || !trim($c['area'])) self::fail('Completá nombre del puesto y área.');
        $count=0;
        foreach(config('biblioteca.groups') as $key=>$label) self::checkBlocks($c['groups'][$key]??null,$count);
        if(!isset($c['additional']) || !is_array($c['additional']) || count($c['additional'])>100) self::fail('Secciones adicionales inválidas.');
        foreach($c['additional'] as $s) { if(!is_string($s['title']??null)||mb_strlen($s['title'])>500||!is_string($s['key']??null)) self::fail('Título de sección inválido.'); self::checkBlocks($s['blocks']??null,$count); }
        foreach(config('biblioteca.groups') as $key=>$label)$c['groups'][$key]=self::sanitizeBlocks($c['groups'][$key]);
        foreach($c['additional'] as &$section)$section['blocks']=self::sanitizeBlocks($section['blocks']);
        unset($section);
        return self::institutional($c,false);
    }
    public static function sanitizeBlocks(array $blocks): array {
        foreach($blocks as &$block) {
            if(isset($block['html']) && !is_string($block['html']))unset($block['html']);
            if($block['kind']==='paragraph' && isset($block['html'])) {
                $block['html']=self::sanitize($block['html']);
                $block['text']=trim(html_entity_decode(strip_tags(preg_replace('~<br\s*/?>|</(?:p|div|li|h[1-6])>~i',"\n",$block['html'])),ENT_QUOTES|ENT_HTML5,'UTF-8'));
                if(mb_strlen($block['text'])>50000)self::fail('El texto del bloque es demasiado extenso.');
            }
            if($block['kind']==='table') {
                foreach($block['rows'] as &$row)foreach($row['cells'] as &$cell) {
                    $cell['blocks']=self::sanitizeBlocks($cell['blocks']);
                    if(array_filter($cell['blocks'],fn($b)=>isset($b['html'])))$cell['text']=implode("\n",array_column($cell['blocks'],'text'));
                }
                unset($row,$cell);
            }
        }
        unset($block);
        return $blocks;
    }
    private static function checkBlocks(mixed $blocks, int &$count, int $depth=0): void {
        if(!is_array($blocks)||count($blocks)>1000||$depth>4) self::fail('Estructura de bloques inválida.');
        foreach($blocks as $b) {
            if(++$count>5000||!is_array($b)||!is_string($b['id']??null)||!is_string($b['text']??null)||mb_strlen($b['text'])>50000||!in_array($b['kind']??'', ['paragraph','table','image'])) self::fail('Bloque inválido.');
            if(isset($b['html']) && ($b['kind']!=='paragraph'||!is_string($b['html'])||strlen($b['html'])>150000))self::fail('Formato de texto inválido.');
            if(isset($b['list']) && (!is_int($b['list']['level']??null)||$b['list']['level']<0||$b['list']['level']>12)) self::fail('Lista inválida.');
            if($b['kind']==='table') {
                if(!is_array($b['rows']??null)||count($b['rows'])>500) self::fail('Tabla inválida.');
                foreach($b['rows'] as $r) {
                    if(!is_array($r['cells']??null)||count($r['cells'])>50) self::fail('Fila inválida.');
                    foreach($r['cells'] as $cell) { if(!is_int($cell['colspan']??null)||$cell['colspan']<1||$cell['colspan']>50||!is_string($cell['text']??null)) self::fail('Celda inválida.'); self::checkBlocks($cell['blocks']??null,$count,$depth+1); }
                }
            }
        }
    }
    public static function toParsed(array $c): array {
        $sections=[];
        foreach(config('biblioteca.groups') as $key=>$title) $sections[]=['key'=>$key,'title'=>$title,'blocks'=>$c['groups'][$key]];
        $sections=array_merge($sections,$c['additional']);
        return ['parserVersion'=>'laravel-editor-1','format'=>'SYSTEM','structure'=>'standard','title'=>$c['name'],'titleName'=>$c['name'], 'fields'=>array_intersect_key($c,array_flip(['name','area','reportsTo','supervisor','supervises'])), 'metadata'=>['sector'=>$c['sector']], 'sections'=>$sections,'blocks'=>array_merge(...array_column($sections,'blocks')),'extras'=>[],'headers'=>[],'warnings'=>[], 'rawText'=>self::plain(self::json($c))];
    }
    public static function plain(string $html): string { return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags(preg_replace('/<\/(p|div|li|tr|h[1-6])>/i','$0 ',$html)),ENT_QUOTES|ENT_HTML5,'UTF-8'))); }
    public static function sanitize(string $html): string {
        $dom=new DOMDocument('1.0','UTF-8'); $old=libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="bib-root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_NONET);
        libxml_clear_errors();libxml_use_internal_errors($old);
        $allowed=['div','p','br','strong','b','em','i','u','s','strike','ul','ol','li','table','thead','tbody','tfoot','tr','th','td','h2','h3','h4','h5','blockquote','a','span','hr','sup','sub'];
        $walk=function($node) use (&$walk,$allowed) {
            foreach(iterator_to_array($node->childNodes) as $child) {
                if($child instanceof \DOMElement) {
                    $tag=strtolower($child->tagName);
                    if(in_array($tag,['script','style','iframe','object','embed','form','input','button','svg','math','img','link','meta','base'])) { $node->removeChild($child); continue; }
                    $walk($child);
                    if(!in_array($tag,$allowed)) { while($child->firstChild) $node->insertBefore($child->firstChild,$child); $node->removeChild($child); continue; }
                    foreach(iterator_to_array($child->attributes) as $attr) {
                        $ok=false;
                        if(in_array($attr->name,['colspan','rowspan','start']) && preg_match('/^\d{1,2}$/',$attr->value) && (int)$attr->value>0) $ok=true;
                        if($attr->name==='href'&&$tag==='a'&&preg_match('~^(https?://|mailto:|#|/(?!/))~i',$attr->value)) $ok=true;
                        if($attr->name==='class' && preg_match('/^(?:step|step-title|step-body|step-number|steps|pill|note|muted|small|table-wrap|table-responsive)(?: [a-z-]+)*$/',$attr->value)) $ok=true;
                        if(!$ok) $child->removeAttributeNode($attr);
                    }
                } elseif(!($child instanceof \DOMText)) $node->removeChild($child);
            }
        };
        $root=$dom->getElementById('bib-root'); if(!$root) return ''; $walk($root); $out=''; foreach($root->childNodes as $child) $out.=$dom->saveHTML($child); return $out;
    }
    public static function validateManual(array $c): array {
        if(strlen(self::json($c))>1500000) self::fail('El documento es demasiado extenso.');
        foreach(['id','sourceId','code','title','sourceHeading','summary','version','declaredState','approver','responsible'] as $key) if(!is_string($c[$key]??null)||mb_strlen($c[$key])>4000) self::fail('Campo inválido: '.$key);
        if(!in_array($c['collection']??'', ['politicas','procedimientos','instructivos'])) self::fail('Sección inválida.');
        if(!trim($c['title'])||!trim($c['code'])||!trim($c['responsible'])) self::fail('Completá título, código y responsable.');
        if(!preg_match('/^[A-Z0-9][A-Z0-9._-]{1,59}$/i',$c['code'])) self::fail('El código admite entre 2 y 60 letras, números, puntos o guiones.');
        $c['code']=strtoupper(trim($c['code']));$c['title']=trim($c['title']);
        if(!preg_match('/^\d+\.\d+(?:\.\d+)?$/',$c['version'])) self::fail('Indicá una versión como 1.0 o 1.1.');
        foreach(['validFrom','lastReview'] as $key) if(($c[$key]??null)!==null) { $d=\DateTimeImmutable::createFromFormat('!Y-m-d',(string)$c[$key]); if(!$d||$d->format('Y-m-d')!==$c[$key]) self::fail('Fecha inválida.'); }
        if(($c['approvalRecord']??null)!==null && (!is_string($c['approvalRecord'])||mb_strlen($c['approvalRecord'])>4000)) self::fail('Instrumento de aprobación inválido.');
        if(!is_int($c['reviewMonths']??null)||$c['reviewMonths']<1||$c['reviewMonths']>120) self::fail('Revisión: entre 1 y 120 meses.');
        if(!is_array($c['sections']??null)||count($c['sections'])>100) self::fail('Secciones inválidas.');
        $ids=[]; foreach($c['sections'] as &$s) {
            if(!is_string($s['id']??null)||isset($ids[$s['id']])||!is_string($s['title']??null)||mb_strlen($s['title'])>300||!is_string($s['html']??null)||strlen($s['html'])>150000) self::fail('Sección inválida.');
            $ids[$s['id']]=true;$s['html']=self::sanitize($s['html']);$s['text']=self::plain($s['html']);
        }
        unset($s);return $c;
    }
}
