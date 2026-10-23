@extends('layouts.app')

@section('title', 'Pedidos de compras')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>
                Inicio
            </a>
        </li>
        <li class="breadcrumb-item">
            Administración
        </li>
        <li class="breadcrumb-item">
            Gestión de compras
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Pedidos de compra
        </li>
    </ol>
</nav>

<div class="d-flex align-items-center gap-3 justify-content-between mb-3">
    <h3 class="tituloVista mb-2">Gestión de pedidos de compras</h3>
    <button type="button" class="btn btn-primary" id="btnAbrirModalNuevoPedido">
        Nuevo pedido
    </button>
</div>

<div class="row d-flex justify-content-start align-items-start">
    <div class="col-2 d-none d-xl-block">
        <div class="card">
            <div class="card-body d-flex flex-column gap-3">
                <!-- ENCABEZADO -->
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>

                <!-- PRIORIDADES -->
                <div class="filtro-box">
                    <div class="filtro-header" id="togglePrioridad">
                        <span>Prioridad</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>

                    <div class="filtro-body d-none" id="listaPrioridades">

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Prioridades"
                                value="URGENTE"
                                id="prioridadUrgente">
                            URGENTE
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Prioridades"
                                value="ALTA"
                                id="prioridadAlta">
                            ALTA
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Prioridades"
                                value="NORMAL"
                                id="prioridadNormal">
                            NORMAL
                        </label>

                    </div>
                </div>

                <!-- AUTORIZACIÓN -->
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleAutorizacion">
                        <span>Autorización</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>

                    <div class="filtro-body d-none" id="listaAutorizacion">

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Autorizacion"
                                value="APROBADA"
                                id="autorizacionAprobada">
                            APROBADA
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Autorizacion"
                                value="PENDIENTE"
                                id="autorizacionPendiente">
                            PENDIENTE
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Autorizacion"
                                value="RECHAZADA"
                                id="autorizacionRechazada">
                            RECHAZADA
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Autorizacion"
                                value="REQUIERE AUTORIZACIÓN GERENTE"
                                id="autorizacionGerente">
                            REQUIERE GERENTE
                        </label>

                    </div>
                </div>

                <!-- ESTADOS -->
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleEstado">
                        <span>Estado</span>
                        <i class="fa fa-chevron-up"></i>
                    </div>

                    <div class="filtro-body" id="listaEstados">

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Estados"
                                value="PENDIENTE"
                                id="estadoPendiente">
                            PENDIENTE
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Estados"
                                value="ENVIADO"
                                id="estadoEnviado">
                            ENVIADO
                        </label>

                        <label class="filtro-item">
                            <input type="checkbox"
                                class="form-check-input check-Estados"
                                value="GENERADO"
                                id="estadoGenerado">
                            GENERADO
                        </label>

                    </div>
                </div>

                <!-- FACTURA -->
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleFacturas">
                        <span>Factura</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>

                    <div class="filtro-body d-none" id="listaFacturas">

                        <select class="form-select" id="filtroFactura">
                            <option value="">Todas</option>
                            <option value="1">Con factura</option>
                            <option value="0">Sin factura</option>
                        </select>

                    </div>
                </div>

                <!-- LIMPIAR FILTROS -->
                <div class="col-12">
                    <button type="button"
                        id="btn-limpiar-filtros"
                        class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        Limpiar filtros
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="col-12 col-xl-10 col-xxl-10">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column gap-3">

                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-3 col-xl-2">
                            <input id="filtroDesde" class="form-control" type="date">
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <input id="filtroHasta" class="form-control" type="date">
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" id="buscarPedido" class="form-control" placeholder="Buscar..." oninput="filtrarPedidos()">
                            </div>
                        </div>

                        @if (in_array(Auth::id(), [1,2,5,6,15,49]))
                        <div class="col-12 col-md-auto">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="exportarExcel()">
                                <i class="fa-solid fa-download me-2"></i>
                                Exportar excel
                            </button>
                        </div>
                        @endif

                    </div>

                    <div class="table-responsive table-card">
                        <table id="tablaPedidosCompras" class="table table-striped nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>FECHA</th>
                                    <th>PRIORIDAD</th>
                                    <th>SOLICITANTE</th>
                                    <th>SECTOR</th>
                                    <th>PROVEEDOR</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th class="text-center">AUTORIZACIÓN</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center">FACTURA</th>
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
                    Este pedido no tiene presupuestos adjuntos.
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

                    <div id="detalleOCBody" class="row g-2">
                    </div>
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Remitos/s asociado/s
                </h6>

                <div class="row g-2 align-items-end mb-3" id="divRemitosAdjuntos">
                    <div class="col-12 col-md-9">
                        <input type="file" class="form-control" id="inputRemito" accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx">
                    </div>

                    <div class="col-12 col-md-3 d-grid">
                        <button type="button" class="btn btn-primary" id="btnSubirRemito">
                            <i class="fa-solid fa-upload me-2"></i>
                            Subir archivo
                        </button>
                    </div>
                </div>

                <div id="listaRemitosAdjuntos" class="d-flex flex-column gap-2">
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Factura/s asociada/s
                </h6>

                <div class="row g-2 align-items-end mb-3" id="divFacturasAdjuntas">
                    <div class="col-12 col-md-9">
                        <input type="file" class="form-control" id="inputFactura" accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx">
                    </div>

                    <div class="col-12 col-md-3 d-grid">
                        <button type="button" class="btn btn-primary" id="btnSubirFactura">
                            <i class="fa-solid fa-upload me-2"></i>
                            Subir archivo
                        </button>
                    </div>
                </div>

                <div id="listaFacturasAdjuntas" class="d-flex flex-column gap-2">
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
                @if (in_array(Auth::id(), [1,2,5,6,15,49]))
                <button type="button" class="btn btn-primary d-none" id="btnRegenerarExcelFinnegans">
                    Regenerar Excel
                </button>
                @endif
                <button type="button" class="btn btn-secondary" id="btnHabilitarEdicionPedido">
                    Editar
                </button>
                <button type="button" class="btn btn-primary d-none" id="btnGuardarCambiosPedidos">
                    Guardar cambios
                </button>
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

@php
$userId = Auth::id();
$puedeAutorizarPedidos = in_array($userId, [1, 2, 5, 6, 15, 49, 217]);
$puedeAprobarGerencia = in_array($userId, [5]);
@endphp

@push('scripts')
<script>
    const USER_ID = @js($userId);
    const PUEDE_AUTORIZAR_PEDIDOS = @js($puedeAutorizarPedidos);
    const PUEDE_APROBAR_GERENCIA = @js($puedeAprobarGerencia);
</script>

<script src="{{ asset('js/administracion/compras/cargarPedido.js') }}"></script>
<script src="{{ asset('js/administracion/compras/edicionPedidos.js') }}"></script>
<script src="{{ asset('js/administracion/compras/panelAdmin.js') }}"></script>
<script src="{{ asset('js/administracion/compras/scriptComunAdmin.js') }}"></script>
@endpush