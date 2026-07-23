<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Calidad\CalidadController;

Route::middleware(['auth'])->group(function () {
     Route::get('/encuestasCalidad', [CalidadController::class, 'encuestasCalidad'])
        ->name('encuestasCalidad');

    Route::get('/dashboardCalidad', [CalidadController::class, 'dashboardCalidad'])
        ->name('dashboardCalidad');

    Route::get('/cmiCalidad', [CalidadController::class, 'cmiCalidad'])
        ->name('cmiCalidad');

    Route::get('/encuestas/tipos', [CalidadController::class, 'listarTiposEncuestas']);

    Route::post('/encuestas/importar', [CalidadController::class, 'importarExcel']);
    Route::post('/encuestas/procesar', [CalidadController::class, 'procesarExcel']);

    Route::get('/encuestas/resultados/{idImportacion}', [CalidadController::class, 'resultados']);

    Route::get('/dashboard/resultados', [CalidadController::class, 'dashboardResultados']);

    Route::get('/dashboard/completo', [CalidadController::class, 'dashboardCompleto']);

    Route::get('/respuestasEncuestas', [CalidadController::class, 'respuestasEncuestas'])
        ->name('respuestasEncuestas');

    Route::get('/encuestas/guardia-adulto/data', [CalidadController::class, 'dataGuardiaAdulto']);
});

//calidad - enlaces públicos
Route::middleware(['dashboard.publico'])->group(function () {

    Route::get(
        '/dashboardCalidadPublico/{token}',
        [CalidadController::class, 'dashboardCalidadPublico']
    )->name('dashboardCalidadPublico');

    Route::get(
        '/dashboard/completo/{token}',
        [CalidadController::class, 'dashboardCompleto']
    );
});
