<div class="card p-2">
    <div class="d-flex flex-column gap-3">
        <div class="col-12">
            <div class="section-divider">
                <span>PROVEEDORES</span>
            </div>
        </div>

        <form id="formNuevoProveedor">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="form-floating">
                        <input name="nombreProveedor" class="form-control">
                        <label>Proveedor</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="form-floating">
                        <input name="razonSocial" class="form-control">
                        <label>Razón Social</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="form-floating">
                        <input type="text" name="cuitProveedor" class="form-control">
                        <label>C.U.I.T.</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="form-floating">
                        <input name="codigoProveedor" class="form-control">
                        <label>Código</label>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-lg-2">
                    <button id="btnCrearProveedor" type="button" class="btn btn-primary w-100">
                        Crear proveedor
                    </button>
                </div>
            </div>
        </form>

        <div class="input-group">
            <input type="text" class="form-control" id="buscarProveedor" placeholder="Buscar proveedor...">
            <button type="button" class="btn btn-secondary" id="limpiarBusquedaProveedor">
                <i class="fa-solid fa-square-xmark"></i>
            </button>
        </div>

        <table id="tbProveedores" class="table table-striped table-hover align-middle table-header-hp3c w-100">
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