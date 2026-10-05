<?php

use App\Http\Controllers\Recepcion\ConsentimientosController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:acceder-recepcion'])->group(function () {
    Route::prefix('consentimientos')->name('consentimientos.')->group(function () {
        Route::get('/index', [ConsentimientosController::class, 'index'])->name('index');
        Route::get('/listar',    [ConsentimientosController::class, 'listarAtenciones'])->name('listar');
        Route::post('/importar', [ConsentimientosController::class, 'importarAtenciones'])->name('importar');
        Route::post('/marcar',   [ConsentimientosController::class, 'marcarAtencion'])->name('marcar');
    });
});
