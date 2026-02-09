<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\CalendarioServController;
use App\Http\Controllers\CalidadController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\DashboardController;

Route::get('/index', [HomeController::class, 'index'])
    ->name('index');

Route::get('/dashboard', [HomeController::class, 'dashboard'])
    ->name('dashboard');

Route::get('/nomina', [PersonalController::class, 'nominaPersonal'])
    ->name('nominaPersonal');

Route::get('/cronograma', [PersonalController::class, 'cronogramaPersonal'])
    ->name('cronogramaPersonal');

Route::get('/registroAsistencia', [PersonalController::class, 'registroAsistencia'])
    ->name('registroAsistencia');

Route::get('/controlAsistencia', [PersonalController::class, 'controlAsistencia'])
    ->name('controlAsistencia');

Route::get('/configuraciones', [PersonalController::class, 'configuraciones'])
->name('configuraciones');


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

Route::get('/ayuda', [HomeController::class, 'ayuda'])
    ->name('ayuda');


Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/ingresar', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/calendario-servicios', [CalendarioServController::class, 'calendarioServicios'])
    ->name('calendarioServicios');

//RUTAS DE SP PERSONAL
Route::get('/areas/lista', [PersonalController::class, 'listarAreas'])
    ->name('areas.lista');

Route::get('/categorias-empleados/lista', [PersonalController::class, 'listarCategorias'])
    ->name('categorias.lista');

Route::get('/convenios/lista', [PersonalController::class, 'listarTipoConvenio'])
->name('convenios.lista');

Route::get('/regimenes/lista', [PersonalController::class, 'listarRegimenes'])
->name('regimenes.lista');

Route::get('/estados/lista', [PersonalController::class, 'listarEstados'])
->name('estados.lista');

Route::get('/roles-empleados/por-categoria/{id}', [PersonalController::class, 'listarRolesXCategoria'])
    ->name('roles.lista');

Route::get('/obra-social/lista', [PersonalController::class, 'listarObraSocial'])
    ->name('obraSocial.lista');

Route::post('/personal/guardar', [PersonalController::class, 'store'])
    ->name('personal.store');

Route::get('/personal/listar', [PersonalController::class, 'listar'])
    ->name('personal.listar');

Route::get('/personal/ver-legajo/{legajo}', [PersonalController::class, 'verLegajo']);

Route::post('/personal/baja/{legajo}', [PersonalController::class, 'bajaEmpleado'])
    ->name('personal.baja');

Route::get(
    '/novedades/historial/{legajo}',
    [PersonalController::class, 'historialNovedades']
);

Route::get('/servicios-empleados/por-area/{id}', [PersonalController::class, 'listarServiciosxArea'])
    ->name('personal.servicios');

Route::put('/personal/{legajo}', [PersonalController::class, 'update']);



//RUTAS DE SP NOVEDADES

Route::get('/novedades/lista', [NovedadesController::class, 'listarNovedades'])
    ->name('novedades.lista');

Route::get('/novedades/data', [NovedadesController::class, 'cargarTablaNovedades'])
    ->name('novedades.data');

Route::post('/novedades/registrar',[NovedadesController::class, 'registrarNovedad'])->name('novedades.store');


Route::get('/novedades/listarNovedadesPorArea', [NovedadesController::class, 'listarNovedadesPorArea'])
    ->name('novedades.listarNovedadesPorArea');

Route::get(
    '/personal/info/{legajo}',
    [PersonalController::class, 'infoMinimaEmpleado']
)->name('personal.info');

Route::get('/novedades/{codigo}', [NovedadesController::class, 'verNovedad'])
     ->whereNumber('codigo');


Route::post('/novedades/alta-novedad', [NovedadesController::class, 'altaNuevaNovedad']);

Route::post('/novedades/subir-comprobante', 
    [NovedadesController::class, 'subirComprobante']
)->name('novedades.subirComprobante');

Route::get(
    '/novedades/{id}/comprobantes',
    [NovedadesController::class, 'listarComprobantes']
);

Route::get(
    '/comprobantes/{id}/ver',
    [NovedadesController::class, 'verComprobante']
);

Route::get('/novedades/selector', [NovedadesController::class, 'listarNovedadesSelector']);

Route::get('/novedades/lista', [NovedadesController::class, 'listar']);

Route::get('/novedades/verDetalleRegistroNovedad/{idRegistro}', [NovedadesController::class, 'verDetalleRegistroNovedad']);

//mensajes
Route::post('/notificaciones/publicar', 
[NotificacionController::class, 'registrarNovedad'])
->middleware('auth');

Route::get('/notificaciones/lista',
[NotificacionController::class, 'listarNotificaciones'])
->middleware('auth')
->name('notificaciones.lista');

//calidad
Route::get('/encuestasCalidad', [CalidadController::class, 'encuestasCalidad'])
    ->name('encuestasCalidad');

Route::get('/dashboardCalidad', [CalidadController::class, 'dashboardCalidad'])
    ->name('dashboardCalidad');

Route::get('/cmiCalidad', [CalidadController::class, 'cmiCalidad'])
    ->name('cmiCalidad');

Route::get('/encuestas/tipos', [CalidadController::class, 'listarTiposEncuestas'])
    ->name('encuestas.tipos');

Route::post('/encuestas/importar', [CalidadController::class, 'importarExcel']);


//cronograma
Route::get('/calendario/colaboradores-area', [CalendarioServController::class, 'listarColaboradoresArea']);
Route::post('/calendario/guardar', [CalendarioServController::class, 'guardarEvento']);
Route::get('/calendario/eventos/{idArea}', [CalendarioServController::class, 'obtenerEventos']);
Route::post('/eventos/modificar', [CalendarioServController::class, 'modificarEvento']);

//dashboard
Route::get('/dashboard/novedades-por-tipo', [DashboardController::class, 'novedadesPorTipo']);
Route::get('/dashboard/colaboradores-activos', [DashboardController::class, 'colaboradoresActivos']);
Route::get('/dashboard/colaboradores-baja', [DashboardController::class, 'colaboradoresBaja']);
Route::get('/dashboard/novedades-historicos', [DashboardController::class, 'historicoNovedades']);
Route::get('/dashboard/novedades-mes-actual', [DashboardController::class, 'novedadesMesActual']);
Route::get('/dashboard/novedades-mas-frec', [DashboardController::class, 'novedadesMasFrecuente']);
Route::get('/dashboard/novedades-menos-frec', [DashboardController::class, 'novedadesMenosFrecuente']);
Route::get('/dashboard/area-mas-novedades', [DashboardController::class, 'areaMasNovedades']);
Route::get('/dashboard/area-menos-novedades', [DashboardController::class, 'areaMenosNovedades']);
Route::get('/dashboard/novedades-por-area', [DashboardController::class, 'novedadesPorArea']);
Route::get('/dashboard/novedades-por-mes', [DashboardController::class, 'novedadesPorMes']);
Route::get('/dashboard/top-empleados-novedades', [DashboardController::class, 'topEmpleadosNovedades']);
Route::get('/dashboard/historico-colaboradores', [DashboardController::class, 'historicoColaboradores']);
Route::get('/dashboard/tasa-rotacional', [DashboardController::class, 'tasaRotacional']);

