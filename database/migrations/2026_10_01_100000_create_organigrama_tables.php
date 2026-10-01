<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Serializa modificaciones del grafo completo, incluso entre ramas distintas.
        Schema::create('organigrama_estado', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('revision')->default(0);
            $table->string('root_id', 120)->nullable();
            $table->string('import_hash', 64)->nullable();
            $table->json('import_report')->nullable();
        });
        DB::table('organigrama_estado')->insert(['id' => 1, 'revision' => 0]);

        // Una posición es una ocurrencia institucional del rol, no otro catálogo laboral.
        Schema::create('organigrama_posiciones', function (Blueprint $table) {
            $table->string('id', 120)->primary();
            $table->string('titulo', 200);
            $table->string('responsable_texto', 1000)->default('');
            // Claves legacy int signed: misma convención que EDD, validación en servicio.
            $table->integer('rol_id')->nullable()->index();
            $table->integer('area_id')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('organigrama_dependencias', function (Blueprint $table) {
            $table->id();
            $table->string('parent_id', 120);
            $table->string('child_id', 120);
            $table->string('tipo', 16);
            $table->unsignedInteger('orden')->default(0);
            $table->foreign('parent_id')->references('id')->on('organigrama_posiciones')->restrictOnDelete();
            $table->foreign('child_id')->references('id')->on('organigrama_posiciones')->restrictOnDelete();
            $table->unique(['parent_id', 'child_id']);
        });
        Schema::create('organigrama_responsables', function (Blueprint $table) {
            $table->id();
            $table->string('posicion_id', 120);
            $table->integer('legajo');
            $table->foreign('posicion_id')->references('id')->on('organigrama_posiciones')->restrictOnDelete();
            $table->unique(['posicion_id', 'legajo']);
        });
        // Patrón de eventos before/after + actor + request_id ya usado en EDD.
        Schema::create('organigrama_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('posicion_id', 120)->nullable();
            $table->string('accion', 40);
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->uuid('request_id');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        foreach (['organigrama_eventos', 'organigrama_responsables', 'organigrama_dependencias', 'organigrama_posiciones', 'organigrama_estado'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
