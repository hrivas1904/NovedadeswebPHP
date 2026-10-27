<?php

namespace App\Services\AuthCentral;

/** Resultado de una llamada al auth: éxito con datos, o error con código y mensaje. */
final class RespuestaAuth
{
    public function __construct(
        public readonly bool $ok,
        public readonly int $status,
        public readonly ?string $error = null,   // código del auth, o SERVICIO_NO_DISPONIBLE
        public readonly ?string $mensaje = null,
        public readonly array $datos = [],
    ) {}
}