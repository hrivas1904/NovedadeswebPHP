@extends('layouts.app')

@section('title', 'Cargar pedido de compras')

@section('content')
<h4 class="fw-bold my-3" style="color: var(--color-default);">CARGAR PEDIDO DE COMPRAS</h4>
<div class="container-fluid card">
    <div id="tabCargar" class="container-fluid mt-3">
        {{-- AVISO DE EDICIÓN --}}
        <div id="edicionBanner"
            class="alert alert-warning d-none align-items-center justify-content-between mb-3"
            role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span id="edicionBannerTexto"></span>
            </div>

            <button type="button"
                class="btn btn-sm btn-outline-dark"
                onclick="cancelarEdicion()">
                <i class="fa-solid fa-xmark me-1"></i>
                Cancelar edición
            </button>
        </div>

        {{-- DATOS DEL SOLICITANTE --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h5 class="mb-0 fw-semibold">
                            Datos del solicitante
                        </h5>
                        <small class="text-muted">
                            Información general de quien realiza el pedido
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <div class="row g-3 mb-3">

                    <div class="col-12 col-lg-8">
                        <label for="fUsuario" class="form-label fw-semibold">
                            Nombre y apellido de quien solicita
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-regular fa-user"></i>
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                id="fUsuario"
                                value="{{ Auth::user()->name }}"
                                readonly>

                            <input
                                type="hidden"
                                class="form-control"
                                id="fUserId"
                                value="{{ Auth::id() }}"
                                readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label for="fFecha" class="form-label fw-semibold">
                            Fecha del pedido
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-regular fa-calendar"></i>
                            </span>

                            <input type="date"
                                id="fFecha"
                                class="form-control">
                        </div>
                    </div>

                </div>

                <div class="row g-3 mb-3">

                    <div class="col-12 col-lg-6">
                        <div id="comboSector" class="position-relative">

                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-building me-2"></i>
                                Centro de costo
                            </label>

                            <select
                                id="cmbCentroCosto"
                                class="form-select">
                            </select>

                            <div id="fSectorList"
                                class="combo-list position-absolute w-100 bg-white border rounded-bottom shadow-sm">
                            </div>

                            <input type="hidden" id="fSectorCodigo">
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div id="comboProveedor" class="position-relative">


                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-truck-field me-2"></i>
                                Proveedor
                            </label>

                            <select
                                id="cmbProveedor"
                                class="form-select">
                            </select>

                            <div id="fProveedorList"
                                class="combo-list position-absolute w-100 bg-white border rounded-bottom shadow-sm">
                            </div>

                            <input type="hidden" id="fProveedorCodigo">
                        </div>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Prioridad / urgencia
                    </label>

                    <div id="prioridadGroup"
                        class="d-flex flex-wrap gap-2">

                        <button type="button"
                            class="btn btn-outline-danger prioridad-btn"
                            data-val="Urgente"
                            onclick="setPrioridad('Urgente')">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Urgente
                        </button>

                        <button type="button"
                            class="btn btn-outline-warning prioridad-btn"
                            data-val="Media"
                            onclick="setPrioridad('Media')">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                            Media
                        </button>

                        <button type="button"
                            class="btn btn-outline-success prioridad-btn"
                            data-val="Baja"
                            onclick="setPrioridad('Baja')">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            Baja
                        </button>
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
                        rows="4"
                        placeholder=""></textarea>

                    <div class="form-text">
                        Indicá el motivo del pedido y para qué área, persona o procedimiento será utilizado.
                    </div>
                </div>

            </div>
        </div>

        {{-- PRODUCTOS SOLICITADOS --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>

                        <div>
                            <h5 class="mb-0 fw-semibold">
                                Productos solicitados
                            </h5>
                            <small class="text-muted">
                                Detalle de productos, cantidades y precios
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive productos-table-container">

                    <table id="tablaLineas"
                        class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width: 260px;">
                                    Producto
                                </th>

                                <th style="min-width: 250px;">
                                    Descripción del ítem
                                </th>

                                <th style="min-width: 130px;">
                                    Cantidad
                                </th>

                                <th style="min-width: 210px;">
                                    Precio total
                                    <small class="d-block text-muted fw-normal">
                                        Sin IVA
                                    </small>
                                </th>

                                <th class="text-center pe-4"
                                    style="width: 70px;">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody id="lineasBody"></tbody>

                    </table>

                </div>

                <div class="border-top p-3">
                    <button type="button"
                        class="btn btn-outline-primary"
                        onclick="agregarLinea()">
                        <i class="fa-solid fa-plus me-1"></i>
                        Agregar otro producto
                    </button>
                </div>

            </div>
        </div>

        {{-- PRESUPUESTOS Y COTIZACIONES --}}
        <div class="card border-0 shadow-sm mb-4 d-none">

            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon">
                        <i class="fa-solid fa-paperclip"></i>
                    </div>

                    <div>
                        <h5 class="mb-0 fw-semibold">
                            Presupuestos / cotizaciones
                        </h5>
                        <small class="text-muted">
                            Documentación complementaria opcional
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="upload-area">

                    <div class="upload-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>

                    <div class="flex-grow-1">
                        <label for="fAdjuntos"
                            class="form-label fw-semibold mb-1">
                            Adjuntar archivos
                        </label>

                        <p class="text-muted small mb-3">
                            Se permiten archivos PDF, imágenes, Excel o Word.
                            Tamaño máximo: 3 MB por archivo.
                        </p>

                        <input type="file"
                            id="fAdjuntos"
                            class="form-control"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.webp,.xlsx,.xls,.doc,.docx"
                            onchange="onAdjuntosSeleccionados(event)">
                    </div>

                </div>

                <div id="adjuntosList" class="mt-3"></div>

            </div>
        </div>

        {{-- ACCIONES PRINCIPALES --}}
        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-4">

            <button type="button"
                class="btn btn-outline-secondary px-4"
                onclick="cancelarEdicion()">
                <i class="fa-solid fa-rotate-left me-1"></i>
                Limpiar formulario
            </button>

            <button type="button"
                class="btn btn-primary px-4"
                id="btnEnviarPedido"
                onclick="enviarPedido()">
                <i class="fa-solid fa-paper-plane me-1"></i>
                Enviar pedido
            </button>

        </div>

    </div>
</div>
@endsection


@push('scripts')
<script src="{{ asset('js/administracion/cargarPedido.js') }}"></script>
@endpush