<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bib_baseline_batches', function (Blueprint $t) {
            $t->string('id', 64)->primary();
            $t->string('created_at', 40);
            $t->string('actor', 200);
            $t->text('source_root');
            $t->longText('manifest_json');
            $t->text('backup_path');
        });
        Schema::table('bib_documents', function (Blueprint $t) {
            $t->string('baseline_batch', 64)->nullable()->index();
            $t->string('baseline_role', 20)->nullable()->index();
            $t->unsignedInteger('baseline_source_id')->nullable();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('La base documental conserva sus fuentes e historial. Utilizar el respaldo verificado para revertir datos.');
    }
};
