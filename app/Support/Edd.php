<?php

namespace App\Support;

class Edd
{
    /** Hasta X,50 → X · desde X,51 → X+1 (regla provisoria, a confirmar con RRHH) */
    public static function valoracion(?float $promedio): ?int
    {
        if ($promedio === null) return null;
        return max(1, min(4, (int) ceil(round($promedio, 2) - 0.5)));
    }

    public static function valoracionLabel(?float $promedio): ?string
    {
        $v = self::valoracion($promedio);
        return $v ? config("edd.escala.$v.label") : null;
    }
}