<?php
namespace App\Services\Biblioteca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class UserPreview {
    public static function allowed($user): bool {
        return Library::manages($user) && in_array($user->username,['mcardoner','ffernandez'],true);
    }
    public static function audit($actor,string $action,array $data): void {
        DB::table('bib_events')->insert(['id'=>(string)Str::uuid(),'document_id'=>null,'version_id'=>null,'action'=>$action,'actor'=>Library::actor($actor),'happened_at'=>Library::now(),'before_json'=>null,'after_json'=>Content::json($data)]);
    }
}
