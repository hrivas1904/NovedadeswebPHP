@extends('layouts.app')

@section('title', 'Cargar pedido de compras')

@section('content')
<h4 class="fw-bold my-3" style="color: var(--color-default);">ADMINISTRAR PEDIDOS DE COMPRAS</h4>
<div class="container-fluid card">
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <div>

                        <h5 class="mb-0 fw-semibold">
                            Pedidos de compra
                        </h5>

                        <small class="text-muted">
                            Gestión y autorización de pedidos
                        </small>

                    </div>

                    <div>
                        <button
                            type="button"
                            class="btn btn-success"
                            onclick="exportarExcel()">

                            <i class="fa-solid fa-file-excel me-2"></i>

                            Exportar a Excel

                        </button>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaPedidosCompras"
                    class="table table-hover table-striped align-middle w-100">

                    <thead class="table-light">

                        <tr>

                            <th class="text-center" style="width: 40px;">
                                <input
                                    type="checkbox"
                                    id="checkTodosPedidos"
                                    class="form-check-input">
                            </th>

                            <th>Fecha</th>

                            <th>Prioridad</th>

                            <th>Solicitante</th>

                            <th>Sector</th>

                            <th>Proveedor</th>

                            <th>Descripción</th>

                            <th class="text-center">
                                Líneas
                            </th>

                            <th class="text-center">
                                Adjuntos
                            </th>

                            <th class="text-center">
                                Autorización
                            </th>

                            <th class="text-center">
                                Estado
                            </th>

                            <th class="text-center">
                                Acciones
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
@endsection

@push('modals')
<div class="modal fade"
    id="modalDetallePedido"
    tabindex="-1">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    Detalle del pedido
                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <!-- Datos generales -->

                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Solicitante</label>
                        <input class="form-control"
                            id="verSolicitante"
                            readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input class="form-control"
                            id="verFecha"
                            readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Prioridad</label>
                        <input class="form-control"
                            id="verPrioridad"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Centro de costo</label>
                        <input class="form-control"
                            id="verCentroCosto"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Proveedor</label>
                        <input class="form-control"
                            id="verProveedor"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <input class="form-control"
                            id="verEstado"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Autorización</label>
                        <input class="form-control"
                            id="verAutorizacion"
                            readonly>
                    </div>

                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Descripción
                        </label>

                        <textarea
                            id="verDescripcion"
                            class="form-control"
                            rows="3"
                            readonly>
                        </textarea>

                    </div>

                </div>

                <hr>

                <h6 class="fw-bold mb-3">
                    Productos solicitados
                </h6>

                <div class="table-responsive">

                    <table
                        class="table table-hover">

                        <thead>

                            <tr>

                                <th>Producto</th>

                                <th>Descripción</th>

                                <th class="text-center">
                                    Cantidad
                                </th>

                                <th class="text-end">
                                    Precio
                                </th>

                            </tr>

                        </thead>

                        <tbody id="detalleProductosBody">

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>
@endpush

@push('scripts')
<script src="{{ asset('js/administracion/panelAdmin.js') }}"></script>
@endpush