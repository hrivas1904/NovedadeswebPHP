@extends('layouts.app')

@section('title', 'Cruce copagos IPS')

@section('content')

<h3 class="tituloVista mb-1">REPORTE DE DEUDA POR COPAGO IPS</h3>
<small class="text-muted">Liquidación IPS (HAD's) cruzada contra cobranzas por caja, concepto "Facturación Copago (exento)".</small>

<div class="row g-3 mt-3 mb-2">
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines" style="color: var(--color-default);"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Liquidación IPS (Copagos)</label>
                        <label class="text-muted">Subí el Excel con una hoja por mes (columnas FIN, NOMBRE, PRACTICAS, INTERNAC., HAD's)</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFileLiqIps">
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines" style="color: var(--color-default);"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Comprobantes de caja</label>
                        <label class="text-muted">Subí el archivo de comprobantes de caja actualizado a la fecha</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFileComprobantes">
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines" style="color: var(--color-default);"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Listado de pacientes</label>
                        <label class="text-muted">Subí el listado (Excel o PDF) con nombre y teléfono/celular</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFilePacientes">
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3 d-none" id="kpisCruce">
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <label class="text-muted mb-1">Total liquidado IPS</label>
            <div class="fs-4 fw-bold" id="kpiLiquidado">$0</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <label class="text-muted mb-1">Total identificado cobrado</label>
            <div class="fs-4 fw-bold" id="kpiCobrado" style="color: var(--color-accent-green);">$0</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <label class="text-muted mb-1">Diferencia (a verificar)</label>
            <div class="fs-4 fw-bold text-danger" id="kpiDiferencia">$0</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <label class="text-muted mb-1">Pacientes pendientes</label>
            <div class="fs-4 fw-bold" id="kpiPendientes">0</div>
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <div class="d-flex align-items-center flex-wrap gap-3">

        <label class="mb-0 text-muted">
            Guardar el estado actual como reporte mensual:
        </label>

        <input
            type="month"
            class="form-control"
            id="mesSnapshot"
            value="{{ now()->format('Y-m') }}"
            style="width: auto;">

        <button
            type="button"
            class="btn btn-primary"
            id="btnGuardarSnapshot">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            Guardar snapshot
        </button>

    </div>
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">

    <div class="input-group">
        <span class="input-group-text">
            <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input type="text" class="form-control" placeholder="Buscar..." id="buscadorTablaCruce">
        <button type="button" class="btn btn-secondary btn-sm" id="btnLimpiarBuscador">
            <i class="fa-regular fa-trash-can"></i>
        </button>
    </div>

    <button
        type="button"
        class="btn btn-dark btn-sm btnFiltroCopago active"
        data-filtro="TODOS">
        Todos
    </button>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btnFiltroCopago"
        data-filtro="PENDIENTES">
        Pendientes
    </button>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btnFiltroCopago"
        data-filtro="NO COBRADO">
        No cobrado
    </button>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btnFiltroCopago"
        data-filtro="COBRO PARCIAL">
        Cobro parcial
    </button>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btnFiltroCopago"
        data-filtro="COBRADO">
        Cobrado
    </button>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btnFiltroCopago"
        data-filtro="RESUELTOS">
        Resueltos
    </button>

    <span class="small text-muted ms-auto" id="contadorCruce"></span>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table id="tablaCruce" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th></th>
                    <th>Paciente</th>
                    <th>FIN</th>
                    <th>Teléfono</th>
                    <th>Período(s)</th>
                    <th class="text-end">Liquidado</th>
                    <th class="text-end">Cobrado</th>
                    <th class="text-end">Diferencia</th>
                    <th>Estado</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.COPAGOS_IPS_ROUTES = {
        cargarLiquidacion: "{{ route('administracion.cargarLiquidacion') }}",
        cargarCaja: "{{ route('administracion.cargarCaja') }}",
        cargarPacientes: "{{ route('administracion.cargarPacientes') }}",
        cargarPacientesPdf: "{{ route('administracion.cargarPacientesPdf') }}",
        cruce: "{{ route('administracion.cruce') }}",
        guardarNota: "{{ route('administracion.guardarNota') }}",
        marcarResuelto: "{{ route('administracion.marcarResuelto') }}",
        guardarSnapshot: "{{ route('administracion.guardarSnapshot') }}",
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    if (window.pdfjsLib) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
    }
</script>
<script src="{{ asset('js/administracion/cobranzas/copagosIps.js') }}"></script>
@endpush