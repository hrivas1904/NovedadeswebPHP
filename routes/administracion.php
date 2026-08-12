<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Administracion\ComprasController;
use App\Http\Controllers\Administracion\AnalisisController;
use App\Http\Controllers\Administracion\DashboardController;
use App\Http\Controllers\Administracion\ImportacionController;
use App\Http\Controllers\Administracion\InterbankingController;
use App\Http\Controllers\Administracion\MovimientosController;
use App\Http\Controllers\Administracion\PresupuestarController;
use App\Http\Controllers\Administracion\SaldosCuentaController;
use App\Http\Controllers\Administracion\ConciliacionesController;

Route::middleware(['auth'])->group(function () {
    //COMPRAS
    Route::get('/pedidosComprasView', [ComprasController::class, 'pedidosComprasView'])->name('pedidosComprasView');
    Route::get('/panelAdminView', [ComprasController::class, 'panelAdminView'])->name('panelAdminView');
    Route::get('/productosProveedoresView', [ComprasController::class, 'productosProveedoresView'])->name('productosProveedoresView');
    Route::get('/compras/centros-costo/listar', [ComprasController::class, 'listarCentrosCosto'])->name('compras.listarCentrosCosto');
    Route::get('/compras/proveedores/listar', [ComprasController::class, 'listarProveedores'])->name('compras.listarProveedores');
    Route::get('/compras/productos/listar', [ComprasController::class, 'listarProductos'])->name('compras.listarProductos');
    Route::post('/compras/guardar', [ComprasController::class, 'guardarPedido'])->name('compras.guardar');
    Route::get('/compras/listar', [ComprasController::class, 'listarPedidosCompras'])->name('compras.listar');
    Route::post('/compras/exportar-excel', [ComprasController::class, 'exportarExcel'])->name('compras.exportarExcel');
    Route::post('/compras/aprobar', [ComprasController::class, 'aprobarPedido']);
    Route::post('/compras/rechazar', [ComprasController::class, 'rechazarPedido']);
    Route::get('/compras/ver/{id}', [ComprasController::class, 'verDetallePedido']);
    Route::get('/compras/{id}/adjuntos', [ComprasController::class, 'listarAdjuntos'])->name('compras.adjuntos.listar');
    Route::get('/compras/{id}/adjuntos/{tipo?}', [ComprasController::class, 'listarAdjuntos'])->name('compras.adjuntos.listar');
    Route::post('/compras/{id}/orden-compra', [ComprasController::class, 'subirOrdenCompra'])->name('compras.ordenCompra.subir');
    Route::post('/compras/productos/crear', [ComprasController::class, 'crearProducto'])->name('compras.crearProducto');
    Route::post('/compras/productos/editar', [ComprasController::class, 'editarProducto'])->name('compras.editarProducto');
    Route::post('/compras/proveedores/crear', [ComprasController::class, 'crearProveedor'])->name('compras.crearProveedor');
    Route::post('/compras/proveedores/editar', [ComprasController::class, 'editarProveedor'])->name('compras.editarProveedor');
    Route::post('/compras/aprobar-gerente', [ComprasController::class, 'aprobarPedidoGerente']);
    Route::get('pedidos/{pedido}/listarObservaciones', [ComprasController::class, 'listarObservaciones'])->name('pedidos.observaciones.index');
    Route::post('pedidos/{pedido}/agregarObservaciones', [ComprasController::class, 'crearObservacion'])->name('pedidos.observaciones.store');

    Route::post(
        '/compras/pedidos/{id}/presupuestos',
        [ComprasController::class, 'subirPresupuesto']
    );

    Route::delete(
        '/compras/presupuestos/{id}',
        [ComprasController::class, 'eliminarPresupuesto']
    );

    Route::put(
        '/compras/pedidos/{id}/actualizar',
        [ComprasController::class, 'actualizarPedido']
    );

    //DASHBOARD
    Route::get('/homeView', [DashboardController::class, 'homeView'])->name('homeViewFinance');
    Route::get('/operacionDiaView', [DashboardController::class, 'operacionDiaView'])->name('operacionDiaView');

    Route::get('/posicion/data', [DashboardController::class, 'data'])->name('posicion.data');
    Route::get('/hoy/data', [DashboardController::class, 'dataHoy'])->name('hoy.dataHoy');

    //PRESUPUESTAR
    Route::get('/presupuestarView', [PresupuestarController::class, 'presupuestarView'])->name('presupuestarView');

    Route::get('/presupuestar', [PresupuestarController::class, 'index'])->name('presupuestar.index');
    Route::post('/presupuestar/geclisa/preview', [PresupuestarController::class, 'previewGeclisa'])->name('presupuestar.geclisa.preview');
    Route::post('/presupuestar/geclisa/confirmar', [PresupuestarController::class, 'confirmarGeclisa'])->name('presupuestar.geclisa.confirmar');
    Route::post('/presupuestar/finnegans/preview', [PresupuestarController::class, 'previewFinnegans'])->name('presupuestar.finnegans.preview');
    Route::post('/presupuestar/finnegans/confirmar', [PresupuestarController::class, 'confirmarFinnegans'])->name('presupuestar.finnegans.confirmar');
    Route::get('/presupuestar/recurrentes', [PresupuestarController::class, 'listarRecurrentes'])->name('presupuestar.recurrentes.listar');
    Route::post('/presupuestar/recurrentes', [PresupuestarController::class, 'guardarRecurrente'])->name('presupuestar.recurrentes.guardar');
    Route::delete('/presupuestar/recurrentes/{id}', [PresupuestarController::class, 'eliminarRecurrente'])->name('presupuestar.recurrentes.eliminar');
    Route::post('/presupuestar/recurrentes/aplicar', [PresupuestarController::class, 'aplicarRecurrentes'])->name('presupuestar.recurrentes.aplicar');

    //MOVIMIENTOS
    Route::get('/movimientosView', [MovimientosController::class, 'movimientosView'])->name('movimientosView');
    Route::get('/movimientos/data', [MovimientosController::class, 'movimientosData'])->name('movimientosData');
    Route::post('/movimientos/manual', [MovimientosController::class, 'movimientosGuardarManual'])->name('movimientosGuardarManual');

    Route::post('/movimientos/masivo/estado', [MovimientosController::class, 'actualizarEstadoMasivo'])->name('movimientos.estadoMasivo');
    Route::post('/movimientos/masivo/duplicar', [MovimientosController::class, 'duplicarMasivo'])->name('movimientos.duplicarMasivo');
    Route::post('/movimientos/masivo/eliminar', [MovimientosController::class, 'eliminarMasivo'])->name('movimientos.eliminarMasivo');
    Route::post('/movimientos/masivo/fecha', [MovimientosController::class, 'actualizarFechaMasiva'])->name('movimientos.fechaMasiva');

    Route::post('/movimientos/{id}/estado', [MovimientosController::class, 'actualizarEstado'])->name('movimientos.estado');
    Route::post('/movimientos/{id}/fecha', [MovimientosController::class, 'actualizarFecha'])->name('movimientos.fecha');
    Route::post('/movimientos/{id}/duplicar', [MovimientosController::class, 'duplicar'])->name('movimientos.duplicar');
    Route::post('/movimientos/{id}/eliminar', [MovimientosController::class, 'eliminar'])->name('movimientos.eliminar');
    Route::post('/movimientos/{id}/operacion', [MovimientosController::class, 'actualizarOperacion'])->name('movimientos.operacion');

    Route::post('/movimientos/{id}/cuenta', [MovimientosController::class, 'actualizarCuenta'])->name('movimientos.cuenta');
    Route::post('/movimientos/{id}/concepto', [MovimientosController::class, 'actualizarConcepto'])->name('movimientos.concepto');
    Route::post('/movimientos/{id}/texto', [MovimientosController::class, 'actualizarTexto'])->name('movimientos.texto');
    Route::post('/movimientos/{id}/importe', [MovimientosController::class, 'actualizarImporte'])->name('movimientos.importe');

    //INTERBANKING
    Route::get('/interbankingView', [InterbankingController::class, 'interbankingView'])->name('interbankingView');
    Route::post('/interbanking/preview', [InterbankingController::class, 'preview'])->name('interbanking.preview');
    Route::post('/interbanking/confirmar', [InterbankingController::class, 'confirmar'])->name('interbanking.confirmar');

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
    Route::post('/importacion/bancos/preview', [ImportacionController::class, 'previewBancos'])->name('importacion.bancos.preview');
    Route::post('/importacion/bancos/confirmar', [ImportacionController::class, 'confirmarBancos'])->name('importacion.bancos.confirmar');
    Route::post('/importacion/caja/preview', [ImportacionController::class, 'previewCaja'])->name('importacion.caja.preview');
    Route::post('/importacion/caja/confirmar', [ImportacionController::class, 'confirmarCaja'])->name('importacion.caja.confirmar');
    Route::post('/importacion/tsv/preview', [ImportacionController::class, 'previewTsv'])->name('importacion.tsv.preview');
    Route::post('/importacion/tsv/confirmar', [ImportacionController::class, 'confirmarTsv'])->name('importacion.tsv.confirmar');

    //SALDOS POR CUENTAS
    Route::get('/saldos-cuenta', [SaldosCuentaController::class, 'index'])->name('saldosCuenta.index');
    Route::get('/saldos-cuenta/data', [SaldosCuentaController::class, 'data'])->name('saldosCuenta.data');
    Route::post('/saldos-cuenta/guardar', [SaldosCuentaController::class, 'guardar'])->name('saldosCuenta.guardar');
    Route::post('/saldos-cuenta/eliminar', [SaldosCuentaController::class, 'eliminar'])->name('saldosCuenta.eliminar');

    //CONCILIACIONES
    Route::get('/conciliacionesHomeView', [ConciliacionesController::class, 'conciliacionesHomeView'])->name('conciliacionesHomeView');
    Route::get('/conciliacionesMacroView', [ConciliacionesController::class, 'conciliacionesMacroView'])->name('conciliacionesMacroView');
    Route::get('/conciliacionesNacionView', [ConciliacionesController::class, 'conciliacionesNacionView'])->name('conciliacionesNacionView');
    Route::get('/conciliacionesFrances986View', [ConciliacionesController::class, 'conciliacionesFrances986View'])->name('conciliacionesFrances986View');
    Route::get('/conciliacionesFrances1001View', [ConciliacionesController::class, 'conciliacionesFrances1001View'])->name('conciliacionesFrances1001View');

    Route::get('/conciliacion/data', [ConciliacionesController::class, 'data'])->name('conciliacion.data');
    Route::post('/conciliacion/movimiento/{id}/estado', [ConciliacionesController::class, 'actualizarEstado'])->name('conciliacion.estado');
    Route::post('/conciliacion/estado-masivo', [ConciliacionesController::class, 'actualizarEstadoMasivo'])->name('conciliacion.estadoMasivo');
    Route::post('/conciliacion/movimiento/{id}/comprobante', [ConciliacionesController::class, 'actualizarComprobante'])->name('conciliacion.comprobante');
    Route::post('/conciliacion/pagos/preview', [ConciliacionesController::class, 'previewPagos'])->name('conciliacion.pagos.preview');
    Route::post('/conciliacion/pagos/confirmar', [ConciliacionesController::class, 'confirmarPagos'])->name('conciliacion.pagos.confirmar');

    Route::post('/conciliacion/extracto/preview', [ImportacionController::class, 'previewBancos'])->name('conciliacion.extracto.preview');
    Route::post('/conciliacion/extracto/confirmar', [ImportacionController::class, 'confirmarBancos'])->name('conciliacion.extracto.confirmar');
});
