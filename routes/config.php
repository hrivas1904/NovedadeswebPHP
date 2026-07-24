<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\ConfigController;
use PSpell\Config;

Route::middleware(['auth'])->group(function () {

    //VISTAS GENERALES
    Route::get('/rolesPermisosView', [ConfigController::class, 'rolesPermisosView'])->name('rolesPermisosView');

    //CARGA DE ROLES DEL SISTEMA
    Route::get('/roles/lista', [ConfigController::class, 'listarRoles'])->name('roles.lista');

    //CARGA DE PERMISOS POR MODULOS
    Route::get('/roles/{rolId}/permisos/{slugModulo}', [ConfigController::class, 'listarPermisosPorModulo'])
    ->name('roles.permisos');
});
