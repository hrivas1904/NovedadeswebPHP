<?php

use App\Http\Controllers\Biblioteca\BibliotecaController as Library;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('biblioteca')->name('biblioteca.')->group(function () {
    Route::get('/',[Library::class,'index'])->name('index');
    Route::get('/administracion/sincronizacion',[\App\Http\Controllers\Biblioteca\SyncController::class,'index'])->name('sync');
    Route::post('/administracion/sincronizacion',[\App\Http\Controllers\Biblioteca\SyncController::class,'run'])->name('sync.run');
    Route::get('/buscar',[Library::class,'catalog'])->name('search');
    Route::get('/coleccion/{kind}',[Library::class,'catalog'])->name('catalog');
    Route::get('/control-documental',[Library::class,'catalog'])->name('control');
    Route::get('/administracion',[Library::class,'catalog'])->name('manage');
    Route::get('/administracion/nuevo',[Library::class,'create'])->name('new');
    Route::get('/administracion/importar',[Library::class,'upload'])->name('upload');
    Route::post('/administracion/importar',[Library::class,'stage'])->name('upload.stage');
    Route::get('/administracion/importar/{id}',[Library::class,'upload'])->name('upload.review');
    Route::post('/administracion/importar/{id}',[Library::class,'confirmUpload'])->name('upload.confirm');
    Route::post('/documentos',[Library::class,'store'])->name('store');
    Route::get('/documentos/{document}',[Library::class,'show'])->name('show');
    Route::get('/versiones/{version}/editar',[Library::class,'edit'])->name('edit');
    Route::post('/versiones/{version}/accion',[Library::class,'action'])->name('action');
    Route::get('/versiones/{version}/revision',[Library::class,'review'])->name('review');
    Route::post('/versiones/{version}/revision',[Library::class,'saveReview'])->name('review.save');
    Route::get('/versiones/{version}/exportar/{format}',[Library::class,'export'])->name('export');
    Route::get('/fuentes/{source}',[Library::class,'source'])->name('source');
    Route::get('/fuentes/{source}/archivo',[Library::class,'file'])->name('source.file');
});
