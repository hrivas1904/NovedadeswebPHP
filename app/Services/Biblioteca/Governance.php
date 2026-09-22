<?php
namespace App\Services\Biblioteca;
use Illuminate\Support\Facades\DB;

class Governance
{
    public static function canApprove($user): bool {
        return Library::manages($user) && $user->username === config('biblioteca.policy_approver_username');
    }
    public function visibility(): array { return DB::table('bib_visibility')->get()->keyBy('key')->all(); }
    public function sectionVisible(string $kind): bool {
        return (bool) (DB::table('bib_visibility')->where('key','section:'.$kind)->value('visible') ?? ($kind !== 'instructivos'));
    }
    public function visibleEntries(array $entries, $user): array {
        if(Library::manages($user)) return $entries;
        $visibility=$this->visibility();
        $approvals=DB::table('bib_policy_approvals')->get()->keyBy('version_id');
        return array_values(array_filter($entries,function($e) use($visibility,$approvals) {
            $d=$e['document']; $v=$e['version'];
            if(!($visibility['section:'.$d['kind']]->visible ?? ($d['kind']!=='instructivos'))) return false;
            if(!($visibility['document:'.$d['id']]->visible ?? true)) return false;
            if($d['kind']!=='politicas') return true;
            $approval=$approvals->get($v['id']);
            return $v['current_slot'] && $v['published_at'] && $v['state']==='Publicado' && $approval
                && hash_equals($approval->content_hash,hash('sha256',$v['payload_json']));
        }));
    }
    public function assertReadable(array $entry,$user): void { abort_unless($this->visibleEntries([$entry],$user),404); }
    public function setVisibility(string $key,bool $visible,int $revision,$user): void {
        abort_unless(Library::manages($user),403);
        DB::transaction(function() use($key,$visible,$revision,$user) {
            // Serialize panel updates, including first-time document settings.
            DB::table('bib_visibility')->where('key','section:politicas')->lockForUpdate()->first();
            $old=DB::table('bib_visibility')->where('key',$key)->lockForUpdate()->first();
            abort_unless((int)($old->revision??0)===$revision,409,'La visibilidad cambió. Recargá el panel.');
            DB::table('bib_visibility')->updateOrInsert(['key'=>$key],['visible'=>$visible,'revision'=>$revision+1]);
            DB::table('bib_events')->insert(['id'=>(string)\Illuminate\Support\Str::uuid(),'document_id'=>str_starts_with($key,'document:')?substr($key,9):null,'version_id'=>null,'action'=>'cambiar_visibilidad','actor'=>Library::actor($user),'happened_at'=>Library::now(),'before_json'=>Content::json($old),'after_json'=>Content::json(['key'=>$key,'visible'=>$visible])]);
        });
    }
}
