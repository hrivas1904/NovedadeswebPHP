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

                        @if (in_array(Auth::id(), [1,2,5,6]))
                        <div class="col-6 col-sm-12 col-md-6 col-lg-2">
                            <button type="button" class="btn btn-primary w-100" onclick="exportarExcel()">
                                <i class="fa-solid fa-file-excel me-2"></i>
                                Exportar a Excel
                            </button>
                        </div>
                        @endif

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

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Estado</label>
                        <input class="form-control" id="verEstado" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Autorización</label>
                        <input class="form-control" id="verAutorizacion" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Requiere Autorización Gerente</label>
                        <input class="form-control" id="reqAutGerente" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Estado Autorización Gerente</label>
                        <input class="form-control" id="estadoAutGerente" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Autorizado/Rechazado/Derivado por</label>
                        <input class="form-control" id="auditorPedido" readonly>
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
                        class="table table-bordered table-hover align-middle nowrap">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario sin Impuestos</th>
                            </tr>
                        </thead>
                        <tbody id="detalleProductosBody">
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-3 mt-4">
                    Presupuestos / cotizaciones adjuntas
                </h6>

                <div id="detalleAdjuntosBody" class="row g-2">
                    <!-- se completa dinámicamente -->
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
                    <!-- Se completa dinámicamente -->
                </div>

                <div id="sinOCMsg" class="text-muted small d-none">
                    Este pedido no tiene una orden de compra asociada.
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
@endpush


@push('scripts')
<script>
    const USER_ID = {{ Auth::id() }};
    const PUEDE_AUTORIZAR_PEDIDOS = {{in_array(Auth::id(), [1, 2, 5, 6, 15]) ? 'true' : 'false'}};
    const PUEDE_APROBAR_GERENCIA = {{in_array(Auth::id(), [1, 5]) ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/administracion/compras/panelAdmin.js') }}"></script>
<script src="{{ asset('js/administracion/compras/scriptComunAdmin.js') }}"></script>
@endpush