<?php

return [
    'url'     => env('AUTENTICADOR_URL'),          // ej: https://auth.midominio.local
    'key'     => env('AUTENTICADOR_KEY'),          // generada con: php artisan sistema:key RRHH
    'timeout' => env('AUTENTICADOR_TIMEOUT', 5),   // segundos

    // Cada cuántos minutos se revalida la sesión contra /api/me (0 = nunca)
    'revalidar_cada' => env('AUTENTICADOR_REVALIDAR', 5),
];
