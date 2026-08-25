@extends('layouts.app')

@section('title', 'Pedidos de compras')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h3 class="tituloVista mb-0">GESTIÓN DE PEDIDOS DE COMPRAS</h3>
        </div>
    </div>
    <div class="mt-3 row d-flex">
        <div class="col-2 d-none d-xl-block">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                            Filtros
                        </h5>
                        <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="filtro-box">
                        <div class="filtro-header" id="togglePrioridad">
                            <span>Prioridad</span>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="filtro-body" id="listaPrioridades">
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Prioridades" value="0">
                                URGENTE
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Prioridades" value="1">
                                MEDIA
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Prioridades" value="1">
                                BAJA
                            </label>
                        </div>
                    </div>
                    <div class="filtro-box my-3">
                        <div class="filtro-header" id="toggleAutorizacion">
                            <span>Autorización</span>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="filtro-body" id="listaAutorizacion">
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Autorizacion" value="0">
                                APROBADA
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Autorizacion" value="1">
                                PENDIENTE
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Autorizacion" value="1">
                                RECHAZADA
                            </label>
                        </div>
                    </div>
                    <div class="filtro-box">
                        <div class="filtro-header" id="toggleEstado">
                            <span>Estados</span>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="filtro-body" id="listaEstados">
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Autorizacion" value="1">
                                PENDIENTE
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-Estados" value="1">
                                ENVIADO
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary w-100">
                            Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="row d-flex g-2">
                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <input id="filtroDesde" class="form-control" type="date" placeholder="Desde" required>
                        </div>

                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <input id="filtroHasta" class="form-control" type="date" placeholder="Hasta" required>
                        </div>

                        <div class="col-6 col-sm-12 col-md-6 col-lg-4">
                            <input type="text" id="buscarPedido" class="form-control w-100" placeholder="Buscar..." oninput="filtrarPedidos()">
                        </div>

                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <button type="button" class="btn btn-primary w-100" id="btnAbrirModalNuevoPedido">
                                Nuevo pedido
                            </button>
                        </div>

                        @if (in_array(Auth::id(), [1,2,5,6,15]))
                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <button type="button" class="btn btn-primary w-100" onclick="exportarExcel()">
                                Exportar a Excel
                            </button>
                        </div>
                        @endif

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPedidosCompras" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>FECHA</th>
                                    <th>PRIORIDAD</th>
                                    <th>SOLICITANTE</th>
                                    <th>SECTOR</th>
                                    <th>PROVEEDOR</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th class="text-center">Líneas</th>
                                    <th class="text-center">Adjuntos</th>
                                    <th class="text-center">AUTORIZACIÓN</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="checkTodosPedidos" class="form-check-input">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalDetallePedido" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    DETALLE DEL PEDIDO DE COMPRA
                </h5>
                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-1 col-6">
                        <label class="form-label fw-bold">Pedido N°</label>
                        <input class="form-control" id="idPedido" readonly>
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold">Fecha</label>
                        <input class="form-control" id="verFecha" readonly>
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label fw-bold">Solicitante</label>
                        <input class="form-control" id="verSolicitante" readonly>
                    </div>                    

                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold">Prioridad</label>
                        <select class="form-control" id="verPrioridad" disabled>
                            <option value="BAJA">BAJA</option>
                            <option value="MEDIA">MEDIA</option>
                            <option value="URGENTE">URGENTE</option>
                        </select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold">Centro de costo</label>
                        <select class="form-control" id="verCentroCosto" disabled></select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold">Proveedor</label>
                        <select class="form-control" id="verProveedor" disabled></select>
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold">Estado</label>
                        <input class="form-control" id="verEstado" readonly>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Moneda</label>
                        <select class="form-control" id="verMoneda" disabled>
                            <option value="ARS">ARS</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>

                    <div class="col-md-4 col-4">
                        <label class="form-label fw-bold">Autorización</label>
                        <input class="form-control" id="verAutorizacion" readonly>
                    </div>

                    <div class="col-md-4 col-6">
                        <label class="form-label fw-bold">Requiere Autorización Gerencia</label>
                        <input class="form-control" id="reqAutGerente" readonly>
                    </div>

                    <div class="col-md-4 col-6">
                        <label class="form-label fw-bold">Estado Autorización Gerencia</label>
                        <input class="form-control" id="estadoAutGerente" readonly>
                    </div>

                    <div class="col-md-4 col-6">
                        <label class="form-label fw-bold">Autorizado / Rechazado / Derivado por</label>
                        <input class="form-control" id="auditorPedido" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Motivo de rechazo</label>
                        <input id="verMotivoRechazo" class="form-control" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea id="verDescripcion" class="form-control" rows="2" readonly></textarea>
                    </div>                    
                </div>

                <h6 class="fw-bold">
                    Productos solicitados
                </h6>

                <div class="d-flex justify-content-start my-2 d-none" id="divBotonAgregarProductoDetalle">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarProductoDetalle">Agregar producto</button>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-bordered table-hover align-middle nowrap">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario sin Impuestos</th>
                                <th class="text-center d-none" id="campoDeleteTabla"></th>
                            </tr>
                        </thead>
                        <tbody id="detalleProductosBody">
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Presupuestos / cotizaciones adjuntas
                </h6>

                <div class="row g-2 align-items-end mb-3" id="divPresupuestosAdjuntos">
                    <div class="col-12 col-md-9">
                        <input type="file" class="form-control" id="inputPresupuesto" accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx">
                    </div>

                    <div class="col-12 col-md-3 d-grid">
                        <button type="button" class="btn btn-primary" id="btnSubirPresupuesto">
                            <i class="fa-solid fa-upload me-2"></i>
                            Subir archivo
                        </button>
                    </div>
                </div>

                <div id="detalleAdjuntosBody" class="row g-2">
                </div>

                <div id="sinAdjuntosMsg" class="text-muted small d-none">
                    Este pedido no tiene archivos adjuntos.
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Orden de compra asociada
                </h6>

                <div class="row g-2 align-items-end mb-3">
                    @if (in_array(Auth::id(), [1,2,5,6]))
                    <div class="col-12 col-md-9">
                        <input type="file" class="form-control" id="inputOrdenCompra" accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx">
                    </div>

                    <div class="col-12 col-md-3 d-grid">
                        <button type="button" class="btn btn-primary" id="btnSubirOrdenCompra">
                            <i class="fa-solid fa-upload me-2"></i>
                            Subir archivo
                        </button>
                    </div>
                    @endif
                </div>

                <div id="detalleOCBody" class="row g-2">
                </div>

                <div id="sinOCMsg" class="text-muted small d-none">
                    Este pedido no tiene una orden de compra asociada.
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Observaciones del pedido
                </h6>

                <div id="hiloObservaciones" class="border rounded p-3 mb-3" style="max-height: 250px; overflow-y: auto; background:#f8f9fa;">
                </div>

                <div class="input-group">
                    <textarea id="inputNuevaObservacion" class="form-control" rows="2" placeholder="Escribí una observación..."></textarea>
                    <button type="button" class="btn btn-primary" id="btnEnviarObservacion">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                @if (in_array(Auth::id(), [1,2,5,6,15]))                
                <button type="button" class="btn btn-primary d-none" id="btnRegenerarExcelFinnegans">
                    Regenerar Excel
                </button>
                <button type="button" class="btn btn-secondary" id="btnHabilitarEdicionPedido">
                    Editar
                </button>
                <button type="button" class="btn btn-primary d-none" id="btnGuardarCambiosPedidos">
                    Guardar cambios
                </button>                
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVisorAdjunto" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visorAdjuntoNombre">
                    <i class="fa-solid fa-file me-2"></i>
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <div id="visorAdjuntoContenido" class="w-100 h-100"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCargaPedido" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    CARGAR NUEVO PEDIDO DE COMPRA
                </h5>
                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom" style="color: var(--color-default);">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa-solid fa-user"></i>
                                    Datos del Solicitante
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="fUsuario" class="form-label fw-semibold">
                                    Solicitante
                                </label>
                                <input type="text" class="form-control" id="fUsuario" value="{{ Auth::user()->name }}" readonly>
                                <input type="hidden" class="form-control" id="fUserId" value="{{ Auth::id() }}" readonly>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label for="fFecha" class="form-label fw-semibold">
                                    Fecha del pedido
                                </label>
                                <input type="date" id="fFecha" class="form-control">
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div id="comboSector" class="position-relative">
                                    <label class="form-label fw-semibold">
                                        Centro de costo
                                    </label>
                                    <select id="cmbCentroCostoModal" class="form-select">
                                    </select>
                                    <input type="hidden" id="fSectorCodigo">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4">
                                <div id="comboProveedor" class="position-relative">
                                    <label class="form-label fw-semibold">
                                        Proveedor
                                    </label>
                                    <select id="cmbProveedorModal" class="form-select">
                                    </select>
                                    <input type="hidden" id="fProveedorCodigo">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label fw-semibold">
                                    Moneda
                                </label>

                                <select id="fMonedaModal" class="form-select">
                                    <option value="ARS" selected>ARS - Peso argentino</option>
                                    <option value="USD">USD - Dólar estadounidense</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Prioridad / Urgencia
                            </label>

                            <div id="prioridadGroup" class="row d-flex">
                                <div class="col-4">
                                    <button type="button"
                                        class="btn btn-outline-danger prioridad-btn w-100"
                                        data-val="Urgente"
                                        onclick="setPrioridad('Urgente')">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        Urgente
                                    </button>
                                </div>

                                <div class="col-4">
                                    <button type="button"
                                        class="btn btn-outline-warning prioridad-btn w-100"
                                        data-val="Media"
                                        onclick="setPrioridad('Media')">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                                        Media
                                    </button>
                                </div>

                                <div class="col-4">
                                    <button type="button"
                                        class="btn btn-outline-success prioridad-btn w-100"
                                        data-val="Baja"
                                        onclick="setPrioridad('Baja')">
                                        <i class="fa-solid fa-circle-check me-1"></i>
                                        Baja
                                    </button>
                                </div>
                            </div>

                            <input type="hidden"
                                id="fPrioridad"
                                value="Media">
                        </div>

                        <div>
                            <label for="fDescripcion" class="form-label fw-semibold">
                                Descripción general del pedido
                            </label>

                            <textarea id="fDescripcion"
                                class="form-control"
                                rows="2"
                                placeholder=""></textarea>

                            <div class="form-text">
                                Indicá el motivo del pedido y para qué área, persona o procedimiento será utilizado.
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom" style="color: var(--color-default);">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    Productos Solicitados
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive productos-table-container">
                            <table id="tablaLineas" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="min-width: 260px;">
                                            Producto
                                        </th>
                                        <th style="min-width: 250px;">
                                            Descripción del Ítem
                                        </th>
                                        <th style="min-width: 130px;">
                                            Cantidad
                                        </th>
                                        <th style="min-width: 210px;">
                                            Precio Unitario sin Impuestos
                                        </th>
                                        <th class="text-center pe-4"
                                            style="width: 70px;">
                                            
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="lineasBody"></tbody>
                            </table>
                        </div>

                        <div class="border-top p-3">
                            <button type="button"
                                class="btn btn-primary"
                                onclick="agregarLinea()">
                                <i class="fa-solid fa-plus me-1"></i>
                                Agregar otro producto
                            </button>
                        </div>

                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom" style="color: var(--color-default);">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fa-solid fa-paperclip"></i>
                                    Adjuntar Presupuestos / Cotizaciones (Opcional)
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="upload-area">
                            <div class="flex-grow-1">
                                <input type="file" id="fAdjuntos" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx" onchange="onAdjuntosSeleccionados(event)">
                            </div>
                        </div>
                        <div id="adjuntosList" class="mt-3">

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <button type="button"
                        class="btn btn-secondary px-4"
                        onclick="cancelarEdicion()">
                        <i class="fa-solid fa-eraser"></i>
                        Limpiar formulario
                    </button>

                    <button type="button"
                        class="btn btn-primary px-4"
                        id="btnEnviarPedido"
                        onclick="enviarPedido(true)">
                        <i class="fa-solid fa-paper-plane me-1"></i>
                        Enviar pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalObservacionRechazo" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    RECHAZAR PEDIDO DE COMPRA
                </h5>
                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column g-3">
                    <label class="form-label">Observaciones</label>
                    <input class="form-control" type="text" id="inputObservRechazo">
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-secondary" id="btnCancelarRechazo">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarRechazo">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endpush


@push('scripts')
<script>
    const USER_ID = {{ Auth::id()}};
    const PUEDE_AUTORIZAR_PEDIDOS = {{in_array(Auth::id(), [1, 2, 5, 6, 15])?'true':'false'}};
    const PUEDE_APROBAR_GERENCIA = {{in_array(Auth::id(), [1, 5])?'true':'false'}};
</script>
<script src="{{ asset('js/administracion/compras/cargarPedido.js') }}"></script>
<script src="{{ asset('js/administracion/compras/edicionPedidos.js') }}"></script>
<script src="{{ asset('js/administracion/compras/panelAdmin.js') }}"></script>
<script src="{{ asset('js/administracion/compras/scriptComunAdmin.js') }}"></script>
@endpush