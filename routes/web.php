<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalController;

Route::get('/', [HomeController::class, 'index'])
    ->name('index');


Route::get('/nomina', [PersonalController::class, 'nominaPersonal'])
    ->name('nominaPersonal');

Route::get('/cronograma', [PersonalController::class, 'cronogramaPersonal'])
    ->name('cronogramaPersonal');

Route::get('/registroAsistencia', [PersonalController::class, 'registroAsistencia'])
    ->name('registroAsistencia');

Route::get('/controlAsistencia', [PersonalController::class, 'controlAsistencia'])
    ->name('controlAsistencia');
