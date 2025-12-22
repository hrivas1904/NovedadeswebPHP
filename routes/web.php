<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\LoginController;

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



Route::get('/registroNovedades', [NovedadesController::class, 'registroNovedades'])
    ->name('registroNovedades');

Route::get('/controlNovedades', [NovedadesController::class, 'controlNovedades'])
    ->name('controlNovedades');

Route::get('/configNovedades', [NovedadesController::class, 'configNovedades'])
    ->name('configNovedades');



Route::get('/ajustes', [AjustesController::class, 'ajustes'])
    ->name('ajustes');

Route::get('/configPerfil', [AjustesController::class, 'configPerfil'])
    ->name('configPerfil');



Route::get('/login', [LoginController::class, 'login'])
    ->name('login');


//RUTAS DE SP
Route::get('/areas/lista', [PersonalController::class, 'listarAreas'])
    ->name('areas.lista');
