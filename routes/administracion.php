<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Administracion\AdministracionController;

Route::middleware(['auth'])->group(function () {
    Route::get('/pedidosComprasView', [AdministracionController::class, 'pedidosComprasView'])->name('pedidosComprasView');
    Route::get('/panelAdminView', [AdministracionController::class, 'panelAdminView'])->name('panelAdminView');
    Route::get('/productosProveedoresView', [AdministracionController::class, 'productosProveedoresView'])->name('productosProveedoresView');
    Route::get('/compras/centros-costo/listar', [AdministracionController::class, 'listarCentrosCosto'])
        ->name('compras.listarCentrosCosto');
    Route::get('/compras/proveedores/listar', [AdministracionController::class, 'listarProveedores'])
        ->name('compras.listarProveedores');
    Route::get('/compras/productos/listar', [AdministracionController::class, 'listarProductos'])
        ->name('compras.listarProductos');
    Route::post('/compras/guardar', [AdministracionController::class, 'guardarPedido'])
        ->name('compras.guardar');
    Route::get(
        '/compras/listar',
        [AdministracionController::class, 'listarPedidosCompras']
    )->name('compras.listar');
    Route::post(
        '/compras/exportar-excel',
        [AdministracionController::class, 'exportarExcel']
    )->name('compras.exportarExcel');
    Route::post(
        '/compras/aprobar',
        [AdministracionController::class, 'aprobarPedido']
    );
    Route::post(
        '/compras/rechazar',
        [AdministracionController::class, 'rechazarPedido']
    );
    Route::get(
        '/compras/ver/{id}',
        [AdministracionController::class, 'verDetallePedido']
    );
    Route::get('/compras/{id}/adjuntos', [AdministracionController::class, 'listarAdjuntos'])
        ->name('compras.adjuntos.listar');
    Route::get('/compras/{id}/adjuntos/{tipo?}', [AdministracionController::class, 'listarAdjuntos'])
        ->name('compras.adjuntos.listar');
    Route::post('/compras/{id}/orden-compra', [AdministracionController::class, 'subirOrdenCompra'])
        ->name('compras.ordenCompra.subir');
    Route::post('/compras/productos/crear', [AdministracionController::class, 'crearProducto'])
        ->name('compras.crearProducto');
    Route::post('/compras/productos/editar', [AdministracionController::class, 'editarProducto'])
        ->name('compras.editarProducto');
    Route::post('/compras/proveedores/crear', [AdministracionController::class, 'crearProveedor'])
        ->name('compras.crearProveedor');
    Route::post('/compras/proveedores/editar', [AdministracionController::class, 'editarProveedor'])
        ->name('compras.editarProveedor');
    Route::post('/compras/aprobar-gerente', [AdministracionController::class, 'aprobarPedidoGerente']);
});
