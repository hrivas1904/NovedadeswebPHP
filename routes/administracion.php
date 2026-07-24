<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Administracion\ComprasController;
use App\Http\Controllers\Administracion\AnalisisController;
use App\Http\Controllers\Administracion\DashboardController;
use App\Http\Controllers\Administracion\ImportacionController;
use App\Http\Controllers\Administracion\InterbankingController;
use App\Http\Controllers\Administracion\MovimientosController;
use App\Http\Controllers\Administracion\PresupuestarController;

Route::middleware(['auth'])->group(function () {
    //COMPRAS
    Route::get('/pedidosComprasView', [ComprasController::class, 'pedidosComprasView'])->name('pedidosComprasView');
    Route::get('/panelAdminView', [ComprasController::class, 'panelAdminView'])->name('panelAdminView');
    Route::get('/productosProveedoresView', [ComprasController::class, 'productosProveedoresView'])->name('productosProveedoresView');
    Route::get('/compras/centros-costo/listar', [ComprasController::class, 'listarCentrosCosto'])->name('compras.listarCentrosCosto');
    Route::get('/compras/proveedores/listar', [ComprasController::class, 'listarProveedores'])->name('compras.listarProveedores');
    Route::get('/compras/productos/listar', [ComprasController::class, 'listarProductos'])->name('compras.listarProductos');
    Route::post('/compras/guardar', [ComprasController::class, 'guardarPedido'])->name('compras.guardar');
    Route::get('/compras/listar',[ComprasController::class, 'listarPedidosCompras'])->name('compras.listar');
    Route::post('/compras/exportar-excel',[ComprasController::class, 'exportarExcel'])->name('compras.exportarExcel');
    Route::post('/compras/aprobar',[ComprasController::class, 'aprobarPedido']);
    Route::post('/compras/rechazar',[ComprasController::class, 'rechazarPedido']);
    Route::get('/compras/ver/{id}',[ComprasController::class, 'verDetallePedido']);
    Route::get('/compras/{id}/adjuntos', [ComprasController::class, 'listarAdjuntos'])->name('compras.adjuntos.listar');
    Route::get('/compras/{id}/adjuntos/{tipo?}', [ComprasController::class, 'listarAdjuntos'])->name('compras.adjuntos.listar');
    Route::post('/compras/{id}/orden-compra', [ComprasController::class, 'subirOrdenCompra'])->name('compras.ordenCompra.subir');
    Route::post('/compras/productos/crear', [ComprasController::class, 'crearProducto'])->name('compras.crearProducto');
    Route::post('/compras/productos/editar', [ComprasController::class, 'editarProducto'])->name('compras.editarProducto');
    Route::post('/compras/proveedores/crear', [ComprasController::class, 'crearProveedor'])->name('compras.crearProveedor');
    Route::post('/compras/proveedores/editar', [ComprasController::class, 'editarProveedor'])->name('compras.editarProveedor');
    Route::post('/compras/aprobar-gerente', [ComprasController::class, 'aprobarPedidoGerente']);

    //DASHBOARD
    Route::get('/homeView', [DashboardController::class, 'homeView'])->name('homeViewFinance');
    Route::get('/operacionDiaView', [DashboardController::class, 'operacionDiaView'])->name('operacionDiaView');

    //PRESUPUESTAR
    Route::get('/presupuestarView', [PresupuestarController::class, 'presupuestarView'])->name('presupuestarView');

    //MOVIMIENTOS
    Route::get('/movimientosView', [MovimientosController::class, 'movimientosView'])->name('movimientosView');
    
    //INTERBANKING
    Route::get('/interbankingView', [InterbankingController::class, 'interbankingView'])->name('interbankingView');

    //ANALISIS
    Route::get('/homeAnalisisView', [AnalisisController::class, 'homeAnalisisView'])->name('homeAnalisisView');
    Route::get('/flujoFondosView', [AnalisisController::class, 'flujoFondosView'])->name('flujoFondosView');
    Route::get('/comparativaView', [AnalisisController::class, 'comparativaView'])->name('comparativaView');
    Route::get('/presupuestadoView', [AnalisisController::class, 'presupuestadoView'])->name('presupuestadoView');
    Route::get('/resumenAnualView', [AnalisisController::class, 'resumenAnualView'])->name('resumenAnualView');

    //IMPORTACION
    Route::get('/importacionView', [ImportacionController::class, 'importacionView'])->name('importacionView');
    Route::get('/importMovBancariosView', [ImportacionController::class, 'importMovBancariosView'])->name('importMovBancariosView');
    Route::get('/importMovCajaView', [ImportacionController::class, 'importMovCajaView'])->name('importMovCajaView');
    Route::get('/importTsvView', [ImportacionController::class, 'importTsvView'])->name('importTsvView');
});
