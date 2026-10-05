<?php

namespace App\Enums\Edd;

enum EstadoEvaluacion: string
{
    case Pendiente = 'pendiente';
    case Borrador = 'borrador';
    case CompletadaEvaluador = 'completada_evaluador';
    case DevolucionRealizada = 'devolucion_realizada';
    case Cerrada = 'cerrada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Borrador => 'En borrador',
            self::CompletadaEvaluador => 'Completada por evaluador',
            self::DevolucionRealizada => 'Devolución realizada',
            self::Cerrada => 'Cerrada',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::Pendiente => 'Asignada, todavía sin respuestas del evaluador.',
            self::Borrador => 'El evaluador está completando puntajes y comentarios.',
            self::CompletadaEvaluador => 'Respuestas completas, pendientes de devolución.',
            self::DevolucionRealizada => 'Entrevista y acuerdos registrados, pendientes de cierre.',
            self::Cerrada => 'Proceso finalizado y disponible para consulta.',
        };
    }

    public function clase(): string
    {
        return match ($this) {
            self::Pendiente => 'text-bg-secondary',
            self::Borrador => 'text-bg-warning',
            self::CompletadaEvaluador => 'text-bg-info',
            self::DevolucionRealizada => 'text-bg-primary',
            self::Cerrada => 'text-bg-success',
        };
    }
}
