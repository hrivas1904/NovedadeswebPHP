@extends('layouts.app')

@section('title', 'Productos y Proveedores')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box">
                <i class="fa-solid fa-boxes-packing fs-3"></i>
            </div>
            <div>
                <h3 class="tituloVista mb-0">PRODUCTOS Y PROVEEDORES</h3>
            </div>
        </div>
    </div>

    <div class="card p-2">
        <div class="row g-3 ">
            <div class="col-12 col-md-4 col-lg-4">
                <form id="formNuevoProducto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="fw-bold" style="color: var(--color-default);">Crear Nuevo Producto</h5>
                        </div>
                        <div class="card-body">
                            <div class="row d-flex">
                                <div class="col-12">
                                    <label class="form-label">Producto</label>
                                    <input name="nombreProducto" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Código</label>
                                    <input name="codigoProducto" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button id="btnCrearProducto" type="button" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> Crear producto
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-8 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row d-flex align-items-center">
                            <div class="col-auto">
                                <h5 class="fw-bold mb-0" style="color: var(--color-default);">Lista de Producto</h5>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                                    <button type="button" class="btn btn-secondary" id="limpiarBusqueda">
                                        <i class="fa-solid fa-square-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tbProductos" class="table table-bordered table-hover align-middle nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PRODUCTO</th>
                                    <th>CÓDIGO</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12 col-md-3 col-lg-4">
                <form id="formNuevoProveedor">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="fw-bold" style="color: var(--color-default);">Crear Nuevo Proveedor</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Proveedor</label>
                                    <input name="nombreProveedor" class="form-control">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Razón Social</label>
                                    <input name="razonSocial" class="form-control">
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label">C.U.I.T.</label>
                                    <input type="text" name="cuitProveedor" class="form-control">
                                </div>
                                <div class="col-12 col-lg-8">
                                    <label class="form-label">Código</label>
                                    <input name="codigoProveedor" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button id="btnCrearProveedor" type="button" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> Crear proveedor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-9 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row d-flex align-items-center">
                            <div class="col-auto">
                                <h5 class="fw-bold mb-0" style="color: var(--color-default);">Lista de Proveedores</h5>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="buscarProveedor" placeholder="Buscar proveedor...">
                                    <button type="button" class="btn btn-secondary" id="limpiarBusquedaProveedor">
                                        <i class="fa-solid fa-square-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tbProveedores" class="table table-bordered table-hover align-middle nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PROVEEDOR</th>
                                    <th>RAZÓN SOCIAL</th>
                                    <th>C.U.I.T.</th>
                                    <th>CÓDIGO</th>
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
<div class="modal fade" id="modalDetalleProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-boxes-stacked"></i> Edición de Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row d-flex">
                    <div class="col-12">
                        <label class="form-label">ID Producto</label>
                        <input id="inputIdProducto" class="form-control" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Producto</label>
                        <input id="inputNombreProducto" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Código</label>
                        <input id="inputCodigoProducto" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex gap-3 justify-content-end">
                    <button id="btnGuardarProducto" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleProveedor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-boxes-stacked"></i> Edición de Proveedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row d-flex">
                    <div class="col-12">
                        <label class="form-label">ID Proveedor</label>
                        <input id="inputIdProveedor" class="form-control" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Proveedor</label>
                        <input id="inputNombreProveedor" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Razón Social</label>
                        <input id="inputRazonSocial" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">C.U.I.T.</label>
                        <input id="inputCuitProveedor" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Código</label>
                        <input id="inputCodigoProveedor" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex gap-3 justify-content-end">
                    <button id="btnGuardarProveedor" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/administracion/productosProveedores.js') }}"></script>
@endpush