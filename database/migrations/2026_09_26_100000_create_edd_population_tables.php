<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edd_area_competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            // Clave institucional int signed; se valida contra áreas sin modificar su esquema.
            $table->integer('area_id');
            $table->json('competencias_json');
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periodo_id', 'area_id']);
        });
        Schema::create('edd_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            $table->integer('legajo');
            $table->integer('area_id');
            $table->json('contexto_snapshot_json');
            $table->boolean('incluido')->default(true);
            $table->text('motivo_exclusion')->nullable();
            // NULL hereda la base de área; un array conserva una lista personalizada.
            $table->json('competencias_json')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periodo_id', 'legajo']);
            $table->index(['periodo_id', 'area_id', 'incluido']);
        });
        Schema::create('edd_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participante_id')->constrained('edd_participantes')->restrictOnDelete();
            $table->foreignId('evaluador_user_id')->constrained('users')->restrictOnDelete();
            $table->string('funcion', 24);
            $table->unsignedTinyInteger('current_slot')->nullable();
            $table->timestamp('vigente_desde');
            $table->timestamp('vigente_hasta')->nullable();
            $table->text('motivo')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unique(['participante_id', 'current_slot']);
            $table->index(['evaluador_user_id', 'current_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edd_asignaciones');
        Schema::dropIfExists('edd_participantes');
        Schema::dropIfExists('edd_area_competencias');
    }
};
