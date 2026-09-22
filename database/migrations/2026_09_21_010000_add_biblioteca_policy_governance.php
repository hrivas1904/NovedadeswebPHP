<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('bib_visibility', function(Blueprint $t) {
            $t->string('key',190)->primary(); $t->boolean('visible'); $t->unsignedInteger('revision')->default(0);
        });
        foreach(['politicas','procedimientos','instructivos','descriptivos'] as $kind) {
            DB::table('bib_visibility')->insert(['key'=>'section:'.$kind,'visible'=>$kind!=='instructivos','revision'=>0]);
        }
        Schema::create('bib_policy_approvals', function(Blueprint $t) {
            $t->string('version_id',160)->primary(); $t->unsignedBigInteger('user_id');
            $t->string('approver',200); $t->string('approved_at',40); $t->string('content_hash',64);
            $t->foreign('version_id')->references('id')->on('bib_versions')->restrictOnDelete();
        });
    }
    public function down(): void { throw new RuntimeException('Las aprobaciones se conservan como evidencia.'); }
};
