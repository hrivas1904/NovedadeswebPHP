<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {

    Artisan::call('alertas:enviar-push');
})
    ->name('alertas:enviar-push')
    ->everyMinute()
    ->withoutOverlapping();


Schedule::call(function () {

    Artisan::call('zkteco:sincronizar');
})
    ->name('zkteco:sincronizacion-automatica')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
