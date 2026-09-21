<?php

use App\Http\Controllers\Biblioteca\BibliotecaController as Library;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth',\App\Http\Middleware\BibliotecaUserPreview::class])->prefix('biblioteca')->name('biblioteca.')->group(function () {
    Route::get('/ver-como',[\App\Http\Controllers\Biblioteca\UserPreviewController::class,'index'])->name('preview');
    Route::post('/ver-como',[\App\Http\Controllers\Biblioteca\UserPreviewController::class,'start'])->name('preview.start');
    Route::post('/volver-a-mi-usuario',[\App\Http\Controllers\Biblioteca\UserPreviewController::class,'stop'])->name('preview.stop');

    Route::get('/mi-descriptivo', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'mine'])->name('mine');
    Route::post('/mi-descriptivo/firmar', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'sign'])->name('sign')->middleware('throttle:20,1');
    Route::get('/constancias/{acceptance}', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'receipt'])->name('receipt');
    Route::middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->group(function () {
        Route::get('/administracion/visibilidad',[\App\Http\Controllers\Biblioteca\GovernanceController::class,'index'])->name('visibility');
        Route::post('/administracion/visibilidad',[\App\Http\Controllers\Biblioteca\GovernanceController::class,'save'])->name('visibility.save');
        Route::post('/versiones/{version}/aprobar-politica',[\App\Http\Controllers\Biblioteca\GovernanceController::class,'approve'])->name('policy.approve');

        Route::get('/administracion/categorias-y-firmas', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'coverage'])->name('coverage');
        Route::post('/administracion/asignaciones/{scope}/{id}', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'assign'])->where('scope', 'category|employee')->whereNumber('id')->name('assign');
        Route::get('/administracion/firmas', [\App\Http\Controllers\Biblioteca\AcceptanceController::class, 'register'])->name('acceptance.register');
    });

    Route::get('/',[Library::class,'index'])->name('index');
    Route::get('/administracion/sincronizacion',[\App\Http\Controllers\Biblioteca\SyncController::class,'index'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('sync');
    Route::post('/administracion/sincronizacion',[\App\Http\Controllers\Biblioteca\SyncController::class,'run'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('sync.run');
    Route::get('/buscar',[Library::class,'catalog'])->name('search');
    Route::get('/coleccion/{kind}',[Library::class,'catalog'])->name('catalog');
    Route::get('/control-documental',[Library::class,'catalog'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('control');
    Route::get('/administracion',[Library::class,'catalog'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('manage');
    Route::get('/administracion/nuevo',[Library::class,'create'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('new');
    Route::get('/administracion/importar',[Library::class,'upload'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('upload');
    Route::post('/administracion/importar',[Library::class,'stage'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('upload.stage');
    Route::get('/administracion/importar/{id}',[Library::class,'upload'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('upload.review');
    Route::post('/administracion/importar/{id}',[Library::class,'confirmUpload'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('upload.confirm');
    Route::post('/documentos',[Library::class,'store'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('store');
    Route::get('/documentos/{document}',[Library::class,'show'])->name('show');
    Route::get('/versiones/{version}/editar',[Library::class,'edit'])->name('edit');
    Route::post('/versiones/{version}/accion',[Library::class,'action'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('action');
    Route::get('/versiones/{version}/revision',[Library::class,'review'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('review');
    Route::post('/versiones/{version}/revision',[Library::class,'saveReview'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('review.save');
    Route::get('/versiones/{version}/exportar/{format}',[Library::class,'export'])->name('export');
    Route::get('/fuentes/{source}',[Library::class,'source'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('source');
    Route::get('/fuentes/{source}/archivo',[Library::class,'file'])->middleware(\App\Http\Middleware\RequireBibliotecaAdmin::class)->name('source.file');
});
