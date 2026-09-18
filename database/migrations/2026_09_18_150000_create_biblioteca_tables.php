<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bib_documents', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('kind', 32)->index();
            $t->string('title', 500); $t->string('area', 500)->default(''); $t->string('code', 80)->nullable()->unique();
            $t->string('canonical_name', 500)->nullable(); $t->string('normalized_area', 500)->nullable();
            $t->longText('aliases'); $t->text('observation')->nullable(); $t->unsignedInteger('revision')->default(0);
            $t->string('created_at', 40); $t->string('origin', 32)->default('system');
        });
        Schema::create('bib_versions', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('document_id', 160); $t->unsignedInteger('number');
            $t->unsignedInteger('revision')->default(0); $t->string('state', 50); $t->string('review_state', 50)->default('Pendiente');
            $t->unsignedTinyInteger('current_slot')->nullable();
            $t->string('based_on', 160)->nullable(); $t->string('source_version_id', 160)->nullable(); $t->string('origin', 32);
            $t->string('created_at', 40); $t->string('updated_at', 40); $t->string('created_by', 200);
            $t->string('published_at', 40)->nullable(); $t->string('published_by', 200)->nullable();
            $t->text('resolution')->nullable(); $t->text('change_reason')->nullable();
            $t->longText('payload_json'); $t->longText('parsed_json')->nullable(); $t->longText('search_text');
            $t->foreign('document_id')->references('id')->on('bib_documents')->restrictOnDelete();
            $t->unique(['document_id', 'number']); $t->unique(['document_id', 'current_slot']);
        });
        Schema::create('bib_sources', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('document_id', 160)->index(); $t->string('source_id', 160)->nullable()->index();
            $t->string('name', 500); $t->string('format', 12); $t->string('sha256', 64)->index(); $t->string('snapshot', 200);
            $t->string('read_state', 50)->default('Correcta'); $t->longText('raw_json'); $t->longText('extraction_json')->nullable();
            $t->foreign('document_id')->references('id')->on('bib_documents')->restrictOnDelete();
        });
        Schema::create('bib_findings', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('document_id', 160)->index(); $t->string('version_id', 160)->index();
            $t->string('status', 30); $t->text('resolution')->nullable(); $t->longText('payload_json');
        });
        Schema::create('bib_events', function (Blueprint $t) {
            $t->string('id', 190)->primary(); $t->string('document_id', 160)->nullable()->index(); $t->string('version_id', 160)->nullable();
            $t->string('action', 100); $t->string('actor', 200); $t->string('happened_at', 40)->index();
            $t->longText('before_json')->nullable(); $t->longText('after_json')->nullable();
        });
        Schema::create('bib_legacy_records', function (Blueprint $t) {
            $t->string('table_name', 80); $t->string('record_key', 190); $t->longText('payload_json'); $t->string('sha256', 64);
            $t->primary(['table_name', 'record_key']);
        });
        Schema::create('bib_migrations', function (Blueprint $t) {
            $t->string('sha256', 64)->primary(); $t->string('imported_at', 40); $t->longText('report_json');
        });
        Schema::create('bib_requests', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('document_id', 160); $t->string('version_id', 160);
        });
        Schema::create('bib_uploads', function (Blueprint $t) {
            $t->string('id', 160)->primary(); $t->string('actor', 200); $t->string('created_at', 40);
            $t->longText('payload_json'); $t->string('document_id', 160)->nullable(); $t->string('version_id', 160)->nullable();
        });
    }

    public function down(): void
    {
        // Document history is deliberately preserved during application rollback.
        throw new RuntimeException('La biblioteca conserva su historial. Restaurar un respaldo para revertir la migración de datos.');
    }
};
