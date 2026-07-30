<div class="p-2" style="overflow:hidden;">

    <div class="card mb-3" id="divExtractoMacro">
        <div class="card-header collapsible-header" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fe-bold">Extracto MACRO</h5>
                <i class="fs-5 fa-solid fa-circle-chevron-down"></i>
            </div>
        </div>
        <div class="card-body d-none">
            <div class="d-flex align-items-center gap-3">
                <label class="form-label">Formato: </label>
                <button type="button" id="btnPegado" class="btn btn-sm btn-outline-secondary active" data-formato="PEGADO">Pegado Homebanking</button>
                <button type="button" id="btnExcel" class="btn btn-sm btn-outline-secondary" data-formato="EXCEL">Excel conciliacion</button>
            </div>

            <div class="my-2 p-2 justify-content-between align-items-center" id="headerColumnas" style="
                --color-second-rgb: 0, 137, 199;
                background-color: rgba(var(--color-second-rgb), 0.15);
                border: 2px solid var(--color-default);
                border-radius: 10px;
                color: var(--color-default);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            ">
            </div>

            <div class="d-flex flex-column gap-2">
                <input id="inputArchivo" type="file" class="form-control"></input>
                <textarea id="textAreaArchivo" rows="2" class="form-control" placeholder="Pegar aquí el extracto de MACRO..."></textarea>
            </div>
        </div>
    </div>

    <div class="card" id="divPagosProveedores">
        <div class="card-header collapsible-header" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fe-bold">Pagos a Proveedores - MACRO</h5>
                <i class="fs-5 fa-solid fa-circle-chevron-down"></i>
            </div>
        </div>
        <div class="card-body d-none">
            <div class="d-flex align-items-center gap-3">
                <label class="form-label">Pegá el archivo de pagos efectivizados del homebanking. Se va a matchear contra los presupuestos cargados para marcarlos como CUMPLIDO.</label>
            </div>

            <div class="d-flex flex-column gap-2">
                <input id="inputArchivoPagoProv" type="file" class="form-control"></input>
                <textarea id="textAreaArchivoProv" rows="2" class="form-control" placeholder="O pegá el contenido acá..."></textarea>
            </div>
        </div>
    </div>

    <hr class="my-3">

    <div class="d-flex align-content-center gap-3 mb-3">
        <select id="selectorConcepto" class="form-control w-auto">
            <option value="">Conceptos</option>
        </select>
        <select id="selectorOperaciones" class="form-control w-auto">
            <option value="">Operaciones</option>
        </select>
        <input id="inputSubconceptos" class="form-control" placeholder="Sub-conceptos..."></input>
        <input id="inputBuscador" class="form-control" placeholder="Buscar detalle/comprobante..."></input>
        <label class="form-label text-muted">Desde</label>
        <input type="date" class="form-control w-auto" id="inputFechaDesde">
        <label class="form-label text-muted">Hasta</label>
        <input type="date" class="form-control w-auto" id="inputFechaHasta">
    </div>

    <div class="d-flex gap-3 mb-3">
        <div class="card w-100">
            <div class="card-body">
                <label class="form-label">SALDO INICIAL</label>
                <h4 class="fw-bold" id="importeSaldoInicial">$0,00</h4>
            </div>
        </div>
        <div class="card w-100">
            <div class="card-body">
                <label class="form-label">SALDO EXTRACTO</label>
                <h4 class="fw-bold" id="importeSaldoExtracto">$0,00</h4>
            </div>
        </div>
        <div class="card w-100">
            <div class="card-body">
                <label class="form-label">PEND. FINNEGANS</label>
                <h4 class="fw-bold" id="importePendFinnegans">-</h4>
            </div>
        </div>
        <div class="card w-100">
            <div class="card-body">
                <label class="form-label">PEND. QR</label>
                <h4 class="fw-bold" id="importePendQr">-</h4>
            </div>
        </div>
        <div class="card w-100">
            <div class="card-body">
                <label class="form-label">SALDO CONTABLE</label>
                <h4 class="fw-bold" id="importeSaldoContable">$0,00</h4>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-2">
        <button type="button" id="btnMarcarFinnMasivo" class="btn btn-sm btn-primary">Marcar Finnegans</button>
        <button type="button" id="btnMarcarQrMasivo" class="btn btn-sm btn-secondary">Marcar QR</button>
        <button type="button" id="btnLimpiarEstadoMasivo" class="btn btn-sm btn-outline-secondary">Limpiar estado</button>
    </div>

    <div>
        <div class="card">
            <div class="card-header d-flex gap-4 justify-content-between align-content-center">
                <div>
                    <h5 class="fw-bold form-label" style="color: var(--color-default);">MOVIMIENTOS</h5>
                </div>
                <div class="d-flex gap-4">
                    <label class="fs-6 fw-bold form-label">F = Pend. Finnegans</label>
                    <label class="fs-6 fw-bold form-label">QR = Pend. QR</label>
                </div>
            </div>
            <div class="card-body">
                <table id="tbMovimientos" class="table table-bordered table-hover align-middle nowrap">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Nro Comp</th>
                            <th>Operación</th>
                            <th>Concepto</th>
                            <th>Sub-concepto</th>
                            <th>Detalle</th>
                            <th>Importe</th>
                            <th>Saldo acumulado</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>