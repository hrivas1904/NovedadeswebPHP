<div class="p-2" style="overflow:hidden;">

    <div class="card mb-3" id="divExtractoMacro">
        <div class="card-header collapsible-header" style="cursor: pointer; color:var(--color-default)">
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

            <label class="mt-2" id="msgExtracto"></label>
            <div id="previewExtractoWrapper" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small"><span id="cantidadExtracto">0</span> movimientos</span>
                    <button type="button" id="btnConfirmarExtracto" class="btn btn-primary btn-sm">Importar <span id="cantidadExtracto2">0</span> movimientos</button>
                </div>
                <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                    <table class="table table-sm">
                        <thead><tr><th>Fecha</th><th>Concepto</th><th>Sub-concepto</th><th>Detalle</th><th class="text-end">Importe</th></tr></thead>
                        <tbody id="previewExtractoBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card" id="divPagosProveedores">
        <div class="card-header collapsible-header" style="cursor: pointer; color:var(--color-default)">
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

            <label class="mt-2" id="msgPagos"></label>
            <div id="previewPagosWrapper" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small" id="resumenPagos"></span>
                    <button type="button" id="btnConfirmarPagos" class="btn btn-primary btn-sm">✓ Confirmar seleccionados</button>
                </div>
                <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                    <table class="table table-sm">
                        <thead><tr><th>✓</th><th>Proveedor (pago)</th><th>Fecha pago</th><th class="text-end">Importe</th><th>Match en presupuesto</th><th>Estado</th></tr></thead>
                        <tbody id="previewPagosBody"></tbody>
                    </table>
                </div>
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
        <div class="card w-100"  style="background-color: var(--color-second);">
            <div class="card-body">
                <label class="text-white">SALDO INICIAL</label>
                <h4 class="fw-bold text-white" id="importeSaldoInicial">$0,00</h4>
            </div>
        </div>
        <div class="card w-100"  style="background-color: var(--color-second);">
            <div class="card-body">
                <label class="text-white">SALDO EXTRACTO</label>
                <h4 class="fw-bold text-white" id="importeSaldoExtracto">$0,00</h4>
            </div>
        </div>
        <div class="card w-100"  style="background-color: var(--color-second);">
            <div class="card-body">
                <label class="text-white">PEND. FINNEGANS</label>
                <h4 class="fw-bold text-white" id="importePendFinnegans">-</h4>
            </div>
        </div>
        <div class="card w-100"  style="background-color: var(--color-second);">
            <div class="card-body">
                <label class="text-white">PEND. QR</label>
                <h4 class="fw-bold text-white" id="importePendQr">-</h4>
            </div>
        </div>
        <div class="card w-100"  style="background-color: var(--color-second);">
            <div class="card-body">
                <label class="text-white">SALDO CONTABLE</label>
                <h4 class="fw-bold text-white" id="importeSaldoContable">$0,00</h4>
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
            <div class="card-body">
                <table id="tbMovimientos" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
                    <thead>
                        <tr>
                            <th>ESTADO</th>
                            <th>FECHA</th>
                            <th>NRO COMP</th>
                            <th>OPERACIÓN</th>
                            <th>CONCEPTO</th>
                            <th>SUB-CONCEPTO</th>
                            <th>DETALLE</th>
                            <th>IMPORTE</th>
                            <th>SALDO ACUMULADO</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>