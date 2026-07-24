@extends('layouts.app')

@section('title', 'Cargar pedido de compras')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h3 class="tituloVista mb-0">CARGA DE PEDIDOS DE COMPRAS</h3>
        </div>
    </div>
</div>
<div class="container-fluid card mb-2">
    <div id="tabCargar" class="container-fluid mt-3">
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
                            <select id="cmbCentroCosto" class="form-select">
                            </select>
                            <input type="hidden" id="fSectorCodigo">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div id="comboProveedor" class="position-relative">
                            <label class="form-label fw-semibold">
                                Proveedor
                            </label>
                            <select id="cmbProveedor" class="form-select">
                            </select>
                            <input type="hidden" id="fProveedorCodigo">
                        </div>
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

        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-4">

            <button type="button"
                class="btn btn-secondary px-4"
                onclick="cancelarEdicion()">
                <i class="fa-solid fa-eraser"></i>
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
<script src="{{ asset('js/administracion/compras/cargarPedido.js') }}"></script>
@endpush