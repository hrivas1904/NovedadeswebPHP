<div class="card p-2">
    <div class="col-12">
        <div class="section-divider">
            <span>PRODUCTOS</span>
        </div>
    </div>
    <div class="d-flex flex-column gap-2">
        <form id="formNuevoProducto">
            <div class="row d-flex align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="form-floating">
                        <input name="nombreProducto" class="form-control">
                        <label>PRODUCTO</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="form-floating">
                        <input name="codigoProducto" class="form-control">
                        <label>CÓDIGO</label>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-lg-2">
                    <button id="btnCrearProducto" type="button" class="btn btn-primary w-100">
                        Crear producto
                    </button>
                </div>
            </div>
        </form>
        <div class="input-group">
            <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
            <button type="button" class="btn btn-secondary" id="limpiarBusqueda">
                <i class="fa-solid fa-square-xmark"></i>
            </button>
        </div>
        <table id="tbProductos" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
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