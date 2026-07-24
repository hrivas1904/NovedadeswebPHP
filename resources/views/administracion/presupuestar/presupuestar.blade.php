@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">PRESUPUESTAR</h3>

    <div class="card p-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">1. COBRANZAS DESDE GECLISA (IVA
            VENTAS)</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">Copiá el IVA Ventas de Geclisa y pegalo
            acá.</label>
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá el contenido del IVA Ventas de Geclisa..." rows="3"></textarea>

        <label class="mt-2" id="msgGeclisa"></label>

        <div id="previewGeclisaWrapper" style="display:none;">
            <div class="table-responsive mt-2">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Razón social</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody id="previewGeclisaBody"></tbody>
                </table>
            </div>
            <button type="button" id="btnConfirmarGeclisa" class="btn btn-primary btn-sm">
                Importar <span id="cantidadGeclisa">0</span> cobranzas
            </button>
        </div>
    </div>

    <div class="card p-3 my-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">2. PAGOS A PROVEEDORES DESDE
            FINNEGANS (CUENTAS A PAGAR)</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">Pegá el reporte de cuentas a pagar.</label>
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá el contenido de Cuentas a Pagar de Finnegans..." rows="3"></textarea>

        <label class="mt-2" id="msgFinnegans"></label>

        <div id="previewFinnegansWrapper" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                <span class="text-muted small"><span id="cantidadFinnegans">0</span> pagos</span>
                <button type="button" id="btnConfirmarFinnegans" class="btn btn-primary btn-sm">
                    Importar <span id="cantidadFinnegans2">0</span> pagos
                </button>
            </div>
            <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Vto.</th>
                            <th>Proveedor</th>
                            <th>Concepto</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody id="previewFinnegansBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">3. LÍNEAS RECURRENTES</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <div class="row m-1 p-2 g-3 align-items-end"
            style="
                    --color-second-rgb: 0, 137, 199;
                    background-color: rgba(var(--color-second-rgb), 0.15);
                    border: 2px solid var(--color-default);
                    border-radius: 10px;
                    color: var(--color-default);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                ">
            <div class="col-xl-2 col-md-4 col-12">
                <select class="form-select" id="selBancoRecurrente">
                    <option value="MACRO">MACRO</option>
                    <option value="NACION">NACIÓN</option>
                    <option value="FRANCES (986)">FRANCÉS (986)</option>
                    <option value="FRANCES (1001)">FRANCÉS (1001)</option>
                    <option value="CAJA">CAJA</option>
                </select>
            </div>

            <div class="col-xl-1 col-md-4 col-12">
                <select class="form-select" id="selSeccionRecurrente">
                    <option value="3 EGRESOS" selected>3 EGRESOS</option>
                    <option value="2 INGRESOS">2 INGRESOS</option>
                </select>
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <select class="form-select" id="selConceptoRecurrente"></select>
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" id="inputSubconceptoRecurrente" placeholder="Sub-concepto">
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" id="inputDetalleRecurrente" placeholder="Descripción">
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" id="inputImporteRecurrente" placeholder="Importe">
            </div>

            <div class="col-xl-1 col-md-4 col-12">
                <button id="btnAgregarRowRecurrente" type="button" class="btn btn-secondary btn-sm w-100">
                    <i class="fa-solid fa-plus"></i> Agregar
                </button>
            </div>
        </div>
        <div id="wrapperRecurrentes" style="display:none;" class="mt-3">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Concepto</th>
                            <th>Descripción</th>
                            <th class="text-end">Importe</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="bodyRecurrentes"></tbody>
                </table>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><span id="cantidadRecurrentes">0</span> líneas</span>
                <input type="month" id="inputMesAplicarRecurrentes" class="form-control form-control-sm" style="width:150px;">
                <button type="button" id="btnAplicarRecurrentes" class="btn btn-primary btn-sm">↓ Aplicar al mes</button>
            </div>
        </div>
        <label class="text-muted mt-3" id="msgSinRecurrentes">Sin líneas recurrentes cargadas.</label>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const PRESUPUESTAR_ROUTES = {
        previewGeclisa: @json(route('administracion.presupuestar.geclisa.preview')),
        confirmarGeclisa: @json(route('administracion.presupuestar.geclisa.confirmar')),
        previewFinnegans: @json(route('administracion.presupuestar.finnegans.preview')),
        confirmarFinnegans: @json(route('administracion.presupuestar.finnegans.confirmar')),
        recurrentesListar: @json(route('administracion.presupuestar.recurrentes.listar')),
        recurrentesGuardar: @json(route('administracion.presupuestar.recurrentes.guardar')),
        recurrentesEliminar: @json(route('administracion.presupuestar.recurrentes.eliminar', ':id')),
        recurrentesAplicar: @json(route('administracion.presupuestar.recurrentes.aplicar')),
    };
    const CONCEPTOS_CATALOGO = @json($conceptos);
</script>
<script src="{{ asset('js/administracion/presupuestar/ivaGeclisa.js') }}"></script>
<script src="{{ asset('js/administracion/presupuestar/pagoProveedores.js') }}"></script>
<script src="{{ asset('js/administracion/presupuestar/lineasRecurrentes.js') }}"></script>
@endpush