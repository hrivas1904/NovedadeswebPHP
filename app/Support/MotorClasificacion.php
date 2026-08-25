<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Motor de clasificacion automatica de movimientos importados.
 * Orden de prioridad (fiel al artefacto original):
 *   1) Regla aprendida (ff_reglas_concepto / ff_reglas_subconcepto, por hash de detalle)
 *   2) Override fijo por patron (OVERRIDES_FIJOS)
 *   3) Heuristica por palabras clave (mapConcepto / mapConceptoMacro)
 *   4) Sub-concepto: el mas frecuente historicamente para ese concepto (desde 2025-04-01)
 *
 * Se instancia UNA vez por lote de importacion (no por fila), para poder
 * precargar reglas y frecuencias en memoria y evitar N+1 queries.
 */
class MotorClasificacion
{
    private array $reglasConcepto = [];      // detalle_hash => concepto
    private array $reglasSubconcepto = [];   // detalle_hash => subconcepto
    private array $subconceptoFrecuente = []; // concepto => subconcepto mas usado

    public function __construct()
    {
        foreach (DB::select('SELECT detalle_hash, k.nombre AS concepto
                              FROM ff_reglas_concepto r
                              JOIN ff_conceptos k ON k.id = r.id_concepto') as $row) {
            $this->reglasConcepto[$row->detalle_hash] = $row->concepto;
        }

        foreach (DB::select('SELECT detalle_hash, subconcepto FROM ff_reglas_subconcepto') as $row) {
            $this->reglasSubconcepto[$row->detalle_hash] = $row->subconcepto;
        }

        foreach (DB::select("
            SELECT concepto, subconcepto FROM (
              SELECT k.nombre AS concepto, m.subconcepto,
                ROW_NUMBER() OVER (PARTITION BY k.nombre ORDER BY COUNT(*) DESC) AS rn
              FROM ff_movimientos m
              JOIN ff_conceptos k ON k.id = m.id_concepto
              WHERE m.ejecucion = 'EJECUTADO' AND m.fecha >= '2025-04-01'
                AND m.subconcepto IS NOT NULL AND m.subconcepto <> ''
                AND m.deleted_at IS NULL
              GROUP BY k.nombre, m.subconcepto
            ) t WHERE rn = 1
        ") as $row) {
            $this->subconceptoFrecuente[$row->concepto] = $row->subconcepto;
        }
    }

    private static function hash(string $texto): string
    {
        return hash('sha256', mb_strtoupper(trim($texto)));
    }

    /** OVERRIDES_FIJOS del artefacto original */
    public function aplicarOverrideFijo(string $texto, ?float $importe = null): ?array
    {
        $t = mb_strtolower($texto);

        if (str_contains($t, '25413')) {
            return ['concepto' => 'GTOS, COMIS, IMP.', 'sub' => 'IMPUESTO A LOS DEBITOS Y CREDITOS'];
        }
        if (str_contains($t, 'iibb') && str_contains($t, 'sircreb')) {
            return ['concepto' => 'IMPUESTOS Y TASAS', 'sub' => 'IIBB SALTA'];
        }
        if (str_contains($t, 'dr.') || str_contains($t, 'dra.')) {
            return ['concepto' => 'HONORARIOS MÉDICOS', 'sub' => 'LIQUIDACIONES'];
        }
        // Transferencia de un particular (paciente) -- SOLO si es plata entrando
        // (importe positivo) y NO es un traspaso interno a FCI (eso sigue
        // siendo TRANSF. ENTRE CUENTAS, para no romper la contrapartida automatica).
        if ($importe !== null && $importe > 0 && str_contains($t, 'transf') && !$this->esFCI($t)) {
            return ['concepto' => 'INGRESOS PARTICULARES', 'sub' => 'INGRESOS PARTICULARES'];
        }

        if (str_contains($t, 'acreditacion cheque remisas')) {
            return ['concepto' => 'OBRAS SOCIALES', 'sub' => '1 OS-DEPOSITO CHEQUES'];
        }
        return null;
    }

    /** Regla aprendida de CONCEPTO para un texto puntual (sin pasar por override/heuristica) */
    public function reglaConceptoPara(string $texto): ?string
    {
        return $this->reglasConcepto[self::hash($texto)] ?? null;
    }

    /** Regla aprendida de SUBCONCEPTO para un texto puntual */
    public function reglaSubconceptoPara(string $texto): ?string
    {
        return $this->reglasSubconcepto[self::hash($texto)] ?? null;
    }

    /** Subconcepto mas frecuente historicamente para un concepto dado */
    public function subconceptoFrecuentePara(string $concepto): string
    {
        return $this->subconceptoFrecuente[$concepto] ?? '';
    }

    /** mapConcepto() del artefacto original: heuristica generica por palabras clave */
    public function mapConcepto(string $texto): string
    {
        $t = mb_strtolower($texto);

        if (str_contains($t,'sueldo')||str_contains($t,'haberes')||str_contains($t,'remuner')||str_contains($t,'anticipo')) return 'SUELDOS';
        if (str_contains($t,'honorario')) return 'HONORARIOS MÉDICOS';
        if (str_contains($t,'farmacia')||str_contains($t,'medicament')||str_contains($t,'drogueria')) return 'FARMACIA';
        if (str_contains($t,'obras social')||str_contains($t,'os-')||str_contains($t,'osde')||str_contains($t,'ipss')||
            str_contains($t,'omint')||str_contains($t,'swiss')||str_contains($t,'nobis')||str_contains($t,'avalian')||
            str_contains($t,'sancor')||str_contains($t,'prevenci')||str_contains($t,'accord')) return 'OBRAS SOCIALES';
        if (str_contains($t,'cupon')||str_contains($t,'payway')||str_contains($t,'visa')||str_contains($t,'master')||
            str_contains($t,'pago pct')||str_contains($t,'tarjeta d')) return 'TARJETAS D/C';
        if (str_contains($t,'transf')||str_contains($t,'al macro')||str_contains($t,'al nacion')||str_contains($t,'al franc')) return 'TRANSF. ENTRE CUENTAS';
        if (str_contains($t,'impuesto')||str_contains($t,'afip')||str_contains($t,'dgr')||str_contains($t,'iibb')||
            str_contains($t,'iva')||str_contains($t,'gravamen')||str_contains($t,'sircreb')||str_contains($t,'ley 25413')||
            str_contains($t,'percepcion')) return 'IMPUESTOS Y TASAS';
        if (str_contains($t,'gtos')||str_contains($t,'comis')||str_contains($t,'dbcr')||str_contains($t,'com transf')||
            str_contains($t,'debito fiscal')) return 'GTOS, COMIS, IMP.';
        if (str_contains($t,'seguro')) return 'SEGUROS';
        if (str_contains($t,'alquiler')) return 'ALQUILERES';
        if (str_contains($t,'catering')||str_contains($t,'colacion')||str_contains($t,'delight')) return 'CATERING';
        if (str_contains($t,'mantenimiento')||str_contains($t,'equipamiento')) return 'MANTENIMIENTO GENERAL';
        if (str_contains($t,'inversion')) return 'INVERSIONES';
        if (str_contains($t,'particular')||str_contains($t,'paciente')) return 'INGRESOS PARTICULARES';
        if (str_contains($t,'tarjeta corp')) return 'TARJETA CORPORATIVA';
        if (str_contains($t,'plan de pago')||str_contains($t,'planes de pago')) return 'PLANES DE PAGO';

        return 'GTOS, COMIS, IMP.';
    }

    /** mapConceptoMacro() del artefacto: variante especifica para MACRO */
    public function mapConceptoMacro(string $concepto, string $detalle): string
    {
        $t = mb_strtolower($concepto . ' ' . $detalle);

        if (str_contains($t, 'paciente particular') || str_contains($t, 'particular')) return 'INGRESOS PARTICULARES';
        if ($this->esFCI($concepto . ' ' . $detalle)) return 'TRANSF. ENTRE CUENTAS';
        if (str_contains($t, 'equipamiento')) return 'MANTENIMIENTO GENERAL';

        return $this->mapConcepto($concepto . ' ' . $detalle);
    }

    public function esFCI(string $texto): bool
    {
        $t = mb_strtolower($texto);
        return str_contains($t, 'fondo comun') || str_contains($t, 'fondo común')
            || str_contains($t, 'fci') || str_contains($t, 'pionero')
            || str_contains($t, 'liq.susc') || str_contains($t, 'sol.resc');
    }

    /** clasificarOperacion() del artefacto: SOLO para movimientos bancarios (no Caja) */
    public function clasificarOperacionBancaria(string $detalle, string $columna2, float $importe): string
    {
        $c2 = mb_strtolower($columna2);
        $d  = mb_strtolower($detalle);
    
        // Excepcion: "ACREDITACION CHEQUE REMISAS/REMESAS" con importe
        // positivo es un ingreso real (deposito de cheques de terceros),
        // no la emision de un cheque propio -- aunque el texto contenga
        // la palabra "cheque".
        if (str_contains($d, 'acreditacion cheque rem') && $importe > 0) {
            return 'INGRESOS';
        }
    
        if (str_contains($c2, 'cheque') || str_contains($d, 'cheque')) return 'CHEQUES';
        if (str_contains($d, 'credin')) return 'INGRESOS';
        if ($importe > 0) return 'INGRESOS';
        if (str_contains($d, 'reintegro')) return 'INGRESOS';
        return 'TRANSFERENCIAS';
    }

    /**
     * Clasifica un movimiento: devuelve concepto y subconcepto sugeridos.
     * ORDEN DE PRIORIDAD REAL (verificado contra el codigo fuente):
     *   1) Override fijo por patron -- si matchea, gana SIEMPRE, incluso
     *      por encima de una regla aprendida ya existente.
     *   2) Regla aprendida (por hash de detalle)
     *   3) Heuristica por palabras clave
     *
     * $textoParaOverride: el texto sobre el que se prueban los OVERRIDES_FIJOS
     *   (en el artefacto varia segun parser: a veces es solo detalle, a veces concepto+detalle).
     * $heuristica: closure que devuelve el concepto por heuristica si no hay override ni regla.
     */
    public function clasificar(string $detalleParaHash, string $textoParaOverride, \Closure $heuristica, ?float $importe = null): array
    {
        $hash = self::hash($detalleParaHash);
        $override = $this->aplicarOverrideFijo($textoParaOverride, $importe);

        if ($override) {
            $concepto = $override['concepto'];
            $subconcepto = $override['sub'];
        } else {
            $concepto = $this->reglasConcepto[$hash] ?? $heuristica();
            $subconcepto = $this->reglasSubconcepto[$hash] ?? ($this->subconceptoFrecuente[$concepto] ?? '');
        }

        return ['concepto' => $concepto, 'subconcepto' => $subconcepto];
    }

    /** Solo resuelve subconcepto (para casos como la contrapartida de FCI, que no pasa por overrides) */
    public function resolverSubconcepto(string $detalle, string $concepto): string
    {
        $hash = self::hash($detalle);
        return $this->reglasSubconcepto[$hash] ?? ($this->subconceptoFrecuente[$concepto] ?? '');
    }

    /**
     * Se llama al CONFIRMAR la importacion: si el usuario corrigio el concepto/subconcepto
     * sugerido para una fila, el sistema "aprende" esa correccion para la proxima vez.
     */
    public static function aprender(string $detalle, string $conceptoFinal, string $subconceptoFinal): void
    {
        DB::statement(
            'INSERT INTO ff_reglas_concepto (detalle, id_concepto)
             VALUES (?, (SELECT id FROM ff_conceptos WHERE nombre = ?))
             ON DUPLICATE KEY UPDATE id_concepto = VALUES(id_concepto), veces_aplicada = veces_aplicada + 1',
            [$detalle, $conceptoFinal]
        );

        if ($subconceptoFinal !== '') {
            DB::statement(
                'INSERT INTO ff_reglas_subconcepto (detalle, subconcepto)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE subconcepto = VALUES(subconcepto)',
                [$detalle, $subconceptoFinal]
            );
        }
    }
}