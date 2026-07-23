<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/core.php';

Route::prefix('rrhh')->name('rrhh.')->group(function () {
    require __DIR__.'/rrhh.php';
});

Route::prefix('calidad')->name('calidad.')->group(function () {
    require __DIR__.'/calidad.php';
});

Route::prefix('administracion')->name('administracion.')->group(function () {
    require __DIR__.'/administracion.php';
});

Route::prefix('configuracion')->name('configuracion.')->group(function () {
    require __DIR__.'/config.php';
});