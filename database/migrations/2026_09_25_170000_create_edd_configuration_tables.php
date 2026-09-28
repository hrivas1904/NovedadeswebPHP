<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edd_periodos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 160);
            $table->unsignedSmallInteger('anio');
            $table->string('estado', 24)->default('borrador');
            foreach (['fecha_corte', 'inicio_evaluacion', 'fin_evaluacion', 'inicio_autoevaluacion', 'fin_autoevaluacion', 'limite_devolucion'] as $fecha) {
                $table->date($fecha)->nullable();
            }
            $table->json('reglas_json');
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('edd_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40);
            $table->unsignedInteger('version');
            $table->string('nombre', 160);
            $table->string('estado', 24)->default('borrador');
            $table->json('escala_json');
            $table->json('reglas_calculo_json');
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('publicado_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('publicado_at')->nullable();
            $table->timestamps();
            $table->unique(['codigo', 'version']);
        });

        Schema::create('edd_bloques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrumento_id')->constrained('edd_instrumentos')->restrictOnDelete();
            $table->string('codigo', 32);
            $table->string('nombre', 160);
            $table->text('descripcion');
            $table->decimal('peso_porcentaje', 5, 2)->nullable();
            $table->unsignedSmallInteger('orden');
            $table->boolean('activo')->default(true);
            $table->unique(['instrumento_id', 'codigo']);
        });

        Schema::create('edd_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloque_id')->constrained('edd_bloques')->restrictOnDelete();
            $table->string('codigo', 40);
            $table->string('competencia_codigo', 60)->nullable();
            $table->string('tipo', 24);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->json('conductas_json')->nullable();
            $table->json('criterio_cumplimiento_json')->nullable();
            $table->decimal('peso_relativo', 7, 2)->default(1);
            $table->boolean('obligatorio')->default(true);
            $table->boolean('permite_no_aplica')->default(false);
            $table->unsignedSmallInteger('orden');
            $table->unique(['bloque_id', 'codigo']);
        });

        Schema::create('edd_periodo_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            $table->foreignId('instrumento_id')->constrained('edd_instrumentos')->restrictOnDelete();
            $table->json('criterio_aplicacion_json')->nullable();
            $table->unique(['periodo_id', 'instrumento_id']);
        });

        Schema::create('edd_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('edd_periodos')->restrictOnDelete();
            $table->string('entidad_tipo', 32);
            $table->unsignedBigInteger('entidad_id');
            $table->string('accion', 60);
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->uuid('request_id');
            $table->timestamp('created_at');
            $table->index(['periodo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['edd_eventos', 'edd_periodo_instrumentos', 'edd_items', 'edd_bloques', 'edd_instrumentos', 'edd_periodos'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
