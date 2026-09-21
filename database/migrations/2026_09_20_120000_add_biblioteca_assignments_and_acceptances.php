<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bib_category_documents' => 'category_id', 'bib_employee_documents' => 'legajo'] as $table => $key) {
            Schema::create($table, function (Blueprint $t) use ($key) {
                $t->integer($key)->primary();
                $t->string('document_id', 160)->nullable();
                $t->unsignedInteger('revision')->default(0);
                $t->string('updated_at', 40);
                $t->string('updated_by', 200);
                $t->foreign('document_id')->references('id')->on('bib_documents')->restrictOnDelete();
            });
        }
        Schema::create('bib_acceptances', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('user_id')->index();
            $t->integer('legajo')->index();
            $t->integer('category_id');
            $t->string('category_name', 200);
            $t->string('employee_name', 250);
            $t->string('user_name', 255);
            $t->string('document_id', 160);
            $t->string('version_id', 160);
            $t->unsignedInteger('version_number');
            $t->string('assignment_token', 64);
            $t->string('assignment_origin', 20);
            $t->string('content_hash', 64);
            $t->longText('content_snapshot');
            $t->text('statement');
            $t->string('accepted_at', 40);
            $t->foreign('document_id')->references('id')->on('bib_documents')->restrictOnDelete();
            $t->foreign('version_id')->references('id')->on('bib_versions')->restrictOnDelete();
            $t->unique(['user_id', 'legajo', 'version_id', 'assignment_token'], 'bib_acceptances_once');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Las aceptaciones y sus asignaciones se conservan. Revertir datos requiere un respaldo verificado.');
    }
};
