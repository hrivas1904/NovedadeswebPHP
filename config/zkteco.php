<?php

return [

    'ip' => env('ZKTECO_IP'),

    'port' => (int) env('ZKTECO_PORT', 4370),

    'protocol' => env('ZKTECO_PROTOCOL', 'tcp'),

    'timeout' => (int) env('ZKTECO_TIMEOUT', 5),

    'password' => (int) env('ZKTECO_PASSWORD', 0),

    'mock' => env('ZKTECO_MOCK', false),

];