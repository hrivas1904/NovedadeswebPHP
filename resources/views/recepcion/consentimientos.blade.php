@extends('layouts.app')

@section('title', 'Consentimientos')

@section('content')
<div class="container-fluid d-flex flex-column gap-3">
    <h3 class="tituloVista mb-0">CONSENTIMIENTOS INFORMADOS</h3>

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-end">
        <div class="d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label for="fechaDesde" class="form-label small text-muted fw-bold mb-1">Ingreso desde</label>
                <input type="date" class="form-control" id="fechaDesde" style="width: 11rem;">
            </div>
            <div>
                <label for="fechaHasta" class="form-label small text-muted fw-bold mb-1">Ingreso hasta</label>
                <input type="date" class="form-control" id="fechaHasta" style="width: 11rem;">
            </div>
            <div>
                <select class="form-select w-100" id="selectorObraSocial">
                    <option value="">Seleccione obra social</option>
                </select>
            </div>
            <div class="btn-group" role="group" aria-label="Filtrar por estado" id="filtroEstado">
                <button type="button" class="btn btn-outline-primary text-nowrap active"
                    data-estado="todos">Todos</button>
                <button type="button" class="btn btn-outline-primary text-nowrap"
                    data-estado="pendientes">Pendientes</button>
                <button type="button" class="btn btn-outline-primary text-nowrap" data-estado="sinfirma">Sin
                    firmar</button>
                <button type="button" class="btn btn-outline-primary text-nowrap"
                    data-estado="completos">Completos</button>
            </div>
            <button type="button" class="btn btn-outline-secondary text-nowrap" id="btnLimpiarFiltros">
                <i class="fa-solid fa-filter-circle-xmark me-1"></i> Limpiar filtros
            </button>
        </div>
        <div>
            <label for="inputFileConsentimientos" class="form-label small text-muted fw-bold mb-1">Listado de internado
                (PDF de Geclisa)</label>
            <input type="file" class="form-control" id="inputFileConsentimientos" accept=".pdf,application/pdf">
        </div>
    </div>

    <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-5">
        <div class="col">
            <div class="card p-2">
                <div class="d-flex flex-column text-center">
                    <h6 class="text-muted fw-bold">
                        Pacientes
                    </h6>
                    <h3 class="fw-bold" id="lblTotalPacientes">-</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card p-2">
                <div class="d-flex flex-column text-center">
                    <h6 class="text-muted fw-bold">
                        Firmado por pacientes
                    </h6>
                    <h3 class="fw-bold" id="lblTotalFirmadoPacientes">-</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card p-2">
                <div class="d-flex flex-column text-center">
                    <h6 class="text-muted fw-bold">
                        Firmado por médicos
                    </h6>
                    <h3 class="fw-bold" id="lblTotalFirmadoMedicos">-</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card p-2">
                <div class="d-flex flex-column text-center">
                    <h6 class="text-muted fw-bold">
                        Consentimientos subidos
                    </h6>
                    <h3 class="fw-bold" id="lblTotalConsentSubidos">-</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card p-2">
                <div class="d-flex flex-column text-center">
                    <h6 class="text-muted fw-bold">
                        Pendientes
                    </h6>
                    <h3 class="fw-bold" id="lblTotalPendientes">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-1">
        <table id="tbConsentimientos" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ATENCIÓN N°</th>
                    <th>PACIENTE</th>
                    <th>OBRA SOCIAL</th>
                    <th>FECHA INGRESO</th>
                    <th>HORA INGRESO</th>
                    <th>MÉDICO</th>
                    <th>FIRMADO PACIENTE</th>
                    <th>FIRMADO MÉDICO</th>
                    <th>SUBIDO</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const RECEPCION_ROUTES = {
        listarAtenciones: "{{ route('recepcion.consentimientos.listar') }}",
        importarAtenciones: "{{ route('recepcion.consentimientos.importar') }}",
        marcarAtencion: "{{ route('recepcion.consentimientos.marcar') }}"
    };
    const USUARIO_NOMBRE = @json(data_get(Auth::user(), 'name'));
</script>
<script src="/js/recepcion/consentimientos.js"></script>
@endpush