@extends('layouts.app')

@section('title', 'Cargar pedido de compras')

@section('content')
<div class="container-fluid p-2">
    <div class="d-flex align-items-center gap-3">
        <div class="icon-box">
            <i class="fa-solid fa-list-check fs-3"></i>
        </div>
        <div>
            <h3 class="tituloVista mb-0">PEDIDOS DE COMPRAS</h3>
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
                        <div class="filtro-body d-none" id="listaPrioridades">
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
                        <div class="filtro-body d-none" id="listaAutorizacion">
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
                        <div class="filtro-body d-none" id="listaEstados">
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
                            <i class="fa-solid fa-eraser"></i> Limpiar
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

                        <div class="col-6 col-sm-12 col-md-6 col-lg-6">
                            <input type="text" id="buscarPedido" class="form-control w-100" placeholder="Buscar..." oninput="filtrarPedidos()">
                        </div>

                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <button type="button" class="btn btn-primary w-100" onclick="exportarExcel()">
                                <i class="fa-solid fa-file-excel me-2"></i>
                                Exportar a Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPedidosCompras" class="table table-bordered table-hover align-middle nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Prioridad</th>
                                    <th>Solicitante</th>
                                    <th>Sector</th>
                                    <th>Proveedor</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Líneas</th>
                                    <th class="text-center">Adjuntos</th>
                                    <th class="text-center">Autorización</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center" style="width: 40px;">
                                        <input
                                            type="checkbox"
                                            id="checkTodosPedidos"
                                            class="form-check-input">
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
    <div class="modal-dialog modal-xl">
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Solicitante</label>
                        <input class="form-control" id="verSolicitante" readonly>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Fecha</label>
                        <input class="form-control" id="verFecha" readonly>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Prioridad</label>
                        <input class="form-control" id="verPrioridad" readonly>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-bold">Centro de costo</label>
                        <input class="form-control" id="verCentroCosto" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Proveedor</label>
                        <input class="form-control" id="verProveedor" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado</label>
                        <input class="form-control" id="verEstado" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Autorización</label>
                        <input class="form-control" id="verAutorizacion" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea id="verDescripcion" class="form-control" rows="2" readonly></textarea>
                    </div>
                </div>

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
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                            </tr>
                        </thead>
                        <tbody id="detalleProductosBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/administracion/panelAdmin.js') }}"></script>
<script src="{{ asset('js/administracion/scriptComunAdmin.js') }}"></script>
@endpush