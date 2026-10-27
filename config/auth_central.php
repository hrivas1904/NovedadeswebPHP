<?php

return [
    // false = RRHH usa su login local de siempre (vuelta atrás sin tocar código)
    'habilitado' => (bool) env('AUTH_CENTRAL_HABILITADO', false),

    'url'     => rtrim((string) env('AUTH_CENTRAL_URL', ''), '/'),
    'key'     => env('AUTH_CENTRAL_KEY'),
    'timeout' => (int) env('AUTH_CENTRAL_TIMEOUT', 10),
];