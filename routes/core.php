<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\LoginController;
use App\Http\Controllers\Core\HomeController;
use App\Http\Controllers\Core\PushController;
use App\Http\Controllers\Core\AlertasController;
use App\Http\Controllers\Core\NotificacionController;
use App\Http\Controllers\Core\LogController;

//loggeo
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/ingresar', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/forgotPassword', [HomeController::class, 'forgotPassword'])->name('forgotPassword');

Route::post('/push/subscribe', [PushController::class, 'subscribe']);

Route::get('/usuario', [HomeController::class, 'crearUsuario'])->name('usuario');
Route::post('/usuario/guardar', [HomeController::class, 'guardar'])->name('usuario.guardar');
Route::get('/index', [HomeController::class, 'index'])->name('index');
Route::post('/restaurarPassword', [HomeController::class, 'restaurarPassword'])->name('restaurarPassword');

//alertas
Route::get('/alertas/listar', [AlertasController::class, 'listar']);
Route::post('/alertas/leida', [AlertasController::class, 'marcarLeida']);
Route::post('/alertas/limpiar', [AlertasController::class, 'limpiarTodas']);

//mensajes
Route::post('/notificaciones/publicar', [NotificacionController::class, 'registrarNotificacion']);
Route::get('/notificaciones/lista', [NotificacionController::class, 'listarNotificaciones'])->name('notificaciones.lista');
Route::post('/notificaciones/borrar', [NotificacionController::class, 'eliminarNotificacion']);
Route::post('/notificaciones/editar', [NotificacionController::class, 'editarNotificacion']);
Route::get('/notificaciones/ver/{id}', [NotificacionController::class, 'verNotificacion'])->name('notificaciones.ver');
Route::post('/notificaciones/editar', [NotificacionController::class, 'editarNotificacion'])->name('notificaciones.editar');
Route::get('/feriados/lista', [NotificacionController::class, 'obtenerFeriados']);
Route::get('/eventosProgramados/lista', [NotificacionController::class, 'obtenerEventosProgramados']);
Route::post('/calendario/agendarEvento', [NotificacionController::class, 'agendarEvento']);
Route::get('/eventosProgramados/verDetalle/{idEvento}', [NotificacionController::class, 'verDetalleEventoProgramado']);
Route::post('/eventosProgramados/editar', [NotificacionController::class, 'editarEventoProgramado']);
Route::post('/eventosProgramados/eliminar', [NotificacionController::class, 'eliminarEventoProgramado']);

//log transacciones
Route::get('/log/vista', [LogController::class, 'logTransactView'])->name('logTransactView');