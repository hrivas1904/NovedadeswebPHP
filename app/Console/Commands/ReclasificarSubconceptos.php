<?php

namespace App\Console\Commands;

use App\Support\MotorClasificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReclasificarSubconceptos extends Command
{
    protected $signature = 'ff:reclasificar-subconceptos
                            {--concepto= : Limitar a un concepto puntual (ej: "OBRAS SOCIALES")}
                            {--apply : Sin esto, solo muestra el reporte (dry-run)}';

    protected $description = 'Recalcula subconcepto segun reglas aprendidas (ff_reglas_subconcepto), validando contra el catalogo cerrado ff_subconceptos';

    public function handle(): int
    {
        $motor = new MotorClasificacion();
        $apply = (bool) $this->option('apply');
        $conceptoFiltro = $this->option('concepto');

        // Catalogo cerrado: concepto => [subconceptos validos]
        // Catalogo cerrado: concepto => [subconceptos validos]
        $catalogo = [];
        foreach (
            DB::select('
    SELECT c.nombre AS concepto, s.nombre AS subconcepto
    FROM ff_subconceptos s
    JOIN ff_conceptos c ON c.id = s.id_concepto
    WHERE s.activo = 1
') as $row
        ) {
            $catalogo[$row->concepto][] = $row->subconcepto;
        }

        $sql = "SELECT m.id, m.detalle, m.subconcepto AS subconcepto_actual, c.nombre AS concepto
                FROM ff_movimientos m
                JOIN ff_conceptos c ON c.id = m.id_concepto
                WHERE m.deleted_at IS NULL";
        $params = [];
        if ($conceptoFiltro) {
            $sql .= " AND c.nombre = ?";
            $params[] = $conceptoFiltro;
        }

        $movimientos = DB::select($sql, $params);

        $corregibles = [];   // regla existe y es valida en el catalogo -> se puede corregir con confianza
        $reglaInvalida = []; // regla existe pero el valor no esta en el catalogo cerrado -> revisar a mano
        $sinRegla = [];      // no hay regla aprendida -> candidato al bug de "lista[0]", no tocar automatico

        foreach ($movimientos as $m) {
            $sugerido = $motor->reglaSubconceptoPara($m->detalle);

            if ($sugerido === null) {
                if ($m->subconcepto_actual !== '') {
                    $sinRegla[] = $m;
                }
                continue;
            }

            $esValido = in_array($sugerido, $catalogo[$m->concepto] ?? [], true);

            if (!$esValido) {
                $reglaInvalida[] = (object)['m' => $m, 'sugerido' => $sugerido];
                continue;
            }

            if ($sugerido !== $m->subconcepto_actual) {
                $corregibles[] = (object)['m' => $m, 'sugerido' => $sugerido];
            }
        }

        $this->info("=== A CORREGIR (regla valida en catalogo, difiere del actual): " . count($corregibles) . " ===");
        foreach ($corregibles as $c) {
            $this->line("#{$c->m->id} [{$c->m->concepto}] '{$c->m->subconcepto_actual}' -> '{$c->sugerido}'  ({$c->m->detalle})");
        }

        $this->warn("\n=== REGLA APRENDIDA INVALIDA EN CATALOGO ACTUAL (revisar a mano): " . count($reglaInvalida) . " ===");
        foreach ($reglaInvalida as $c) {
            $this->line("#{$c->m->id} [{$c->m->concepto}] actual='{$c->m->subconcepto_actual}' regla_vieja='{$c->sugerido}' ({$c->m->detalle})");
        }

        $this->comment("\n=== SIN REGLA APRENDIDA (posible bug de default, no se toca): " . count($sinRegla) . " ===");
        foreach ($sinRegla as $m) {
            $this->line("#{$m->id} [{$m->concepto}] actual='{$m->subconcepto_actual}' ({$m->detalle})");
        }

        if (!$apply) {
            $this->info("\nDry-run. Corre con --apply para aplicar los " . count($corregibles) . " cambios de la primera lista.");
            return 0;
        }

        if (!$this->confirm("Vas a aplicar " . count($corregibles) . " correcciones. Confirmar?")) {
            return 0;
        }

        foreach ($corregibles as $c) {
            DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_TEXTO(?,?,?)', [$c->m->id, 'subconcepto', $c->sugerido]);
        }

        $this->info("Aplicados " . count($corregibles) . " cambios.");
        return 0;
    }
}
