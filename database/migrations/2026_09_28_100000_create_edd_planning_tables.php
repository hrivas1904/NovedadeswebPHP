<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edd_listas_competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            // "generales" identifica la única lista común; las bibliotecas usan UUID.
            $table->string('codigo', 40);
            $table->string('nombre', 160);
            $table->json('competencias_json');
            $table->unsignedInteger('revision')->default(1);
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periodo_id', 'codigo']);
        });
        Schema::create('edd_evaluador_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            $table->foreignId('evaluador_user_id')->constrained('users')->restrictOnDelete();
            $table->integer('area_id');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periodo_id', 'evaluador_user_id', 'area_id'], 'edd_evaluador_area_unique');
        });
        // Conserva la pertenencia implícita de los responsables ya asignados.
        // No cambia las asignaciones, participantes ni registros de la nómina.
        DB::table('edd_evaluador_areas')->insertUsing(
            ['periodo_id', 'evaluador_user_id', 'area_id', 'created_by', 'created_at', 'updated_at'],
            DB::table('edd_asignaciones as s')->join('edd_participantes as p', 'p.id', '=', 's.participante_id')
                ->join('areas as a', 'a.ID_AREA', '=', 'p.area_id')
                ->where('s.current_slot', 1)->where('p.incluido', true)
                ->groupBy('p.periodo_id', 's.evaluador_user_id', 'p.area_id')
                ->selectRaw('p.periodo_id, s.evaluador_user_id, p.area_id, MIN(s.created_by), MIN(s.vigente_desde), MIN(s.vigente_desde)')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('edd_evaluador_areas');
        Schema::dropIfExists('edd_listas_competencias');
    }
};
