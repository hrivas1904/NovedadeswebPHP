<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalController;

Route::get('/', [HomeController::class, 'index'])
    ->name('index');


Route::get('/', [PersonalController::class, 'nominaPersonal'])
    ->name('nominaPersonal');