<?php

namespace App\Support;

/**
 * Resuelve el campo 'operacion' (ENUM: INGRESOS/TRANSFERENCIAS/CHEQUES/EFECTIVO)
 * a partir de banco/concepto/seccion/importe, con el mismo criterio que se aplico
 * durante la migracion del backup original (fusion de CAMBIO OP-4 + opEfectiva()).
 */
class ClasificadorOperacion
{
    public static function resolver(string $banco, string $concepto, string $seccion, float $importe): string
    {
        if ($banco === 'CAJA') {
            return 'EFECTIVO';
        }
        if ($seccion === '5 TRANSFERENCIAS' || $concepto === 'TRANSF. ENTRE CUENTAS') {
            return 'TRANSFERENCIAS';
        }
        return $importe > 0 ? 'INGRESOS' : 'TRANSFERENCIAS';
    }
}