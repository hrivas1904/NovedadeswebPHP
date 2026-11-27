@extends('layouts.app')

@section('title', 'Evaluaciones de Desempeño')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house me-1"></i> Inicio</a>
        </li>
        <li class="breadcrumb-item">Recursos Humanos</li>
        <li class="breadcrumb-item">Colaboradores</li>
        <li class="breadcrumb-item active" aria-current="page">Evaluaciones de desempeño</li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="tituloVista mb-0">Evaluaciones de desempeño</h3>
        @if ($esAdmin)
            <button type="button" class="btn btn-primary" id="btnGenerarPeriodo">
                <i class="fa-solid fa-gears me-1"></i> Generar EDD del período
            </button>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Porcentaje de avance</label>
                <h4 class="fw-bold mb-0" style="color: var(--color-default);" id="lblPorcAvance">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Completas</label>
                <h4 class="fw-bold mb-0" style="color: var(--color-default);" id="lblCompletas">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">En curso</label>
                <h4 class="fw-bold mb-0" style="color: var(--color-default);" id="lblEnCurso">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Pendientes</label>
                <h4 class="fw-bold mb-0" style="color: var(--color-default);" id="lblPendientes">-</h4>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <select class="form-select w-auto" id="selectorFiltroPeriodoEdd">
            <option value="">Todos los períodos</option>
        </select>
        <select class="form-select w-auto" id="selectorFiltroAreaEdd">
            <option value="">Todas las áreas</option>
        </select>
        <select class="form-select w-auto" id="selectorFiltroServicioEdd">
            <option value="">Todos los servicios</option>
        </select>
        <select class="form-select w-auto" id="selectorFiltroEstadoEdd">
            <option value="">Todos los estados</option>
        </select>
        {{-- <button class="btn btn-outline-primary" id="btnExportar"><i class="fa-solid fa-download"></i> Exportar</button> --}}
    </div>

    <div class="card p-1">
        <div class="table-card">
            <table class="table table-striped table-hover align-middle w-100" id="tbEvaluaciones">
                <thead>
                    <tr>
                        <th rowspan="2">ID</th>
                        <th rowspan="2">COLABORADOR</th>
                        <th rowspan="2">ÁREA</th>
                        <th rowspan="2">SERVICIO</th>
                        <th rowspan="2">EVALUADOR</th>
                        <th colspan="2" class="text-center">AUTOEVALUACIÓN</th>
                        <th colspan="2" class="text-center">EVALUACIÓN</th>
                        <th rowspan="2">ESTADO</th>
                        <th rowspan="2">ACCIONES</th>
                    </tr>
                    <tr>
                        <th>FECHA</th>
                        <th>PROMEDIO</th>
                        <th>FECHA</th>
                        <th>PROMEDIO</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
@if ($esAdmin)
<div class="modal fade" id="modalGenerarPeriodo" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar evaluaciones del período</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <p class="text-muted mb-0">
                    Se crea una EDD por cada colaborador en relación de dependencia que tenga puesto, descriptivo firmado
                    y superior en el organigrama. Se puede volver a correr: solo crea las que faltan.
                </p>
                <div class="d-flex gap-2 align-items-end">
                    <div>
                        <label class="label-control" for="selectPeriodoGenerar">Período</label>
                        <select class="form-select" id="selectPeriodoGenerar"></select>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnConfirmarGenerar">Generar</button>
                </div>

                <div id="resultadoGeneracion" class="d-none d-flex flex-column gap-2">
                    <div class="d-flex flex-wrap gap-2" id="resumenGeneracion"></div>
                    <div class="table-card" style="max-height: 50vh; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr><th>COLABORADOR</th><th>ÁREA</th><th>PUESTO</th><th>RESULTADO</th><th>DETALLE</th></tr>
                            </thead>
                            <tbody id="tbodyResultadoGeneracion"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCambiarEvaluador" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar evaluador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <div><span class="text-muted">Evaluado:</span> <span class="fw-bold" id="lblEvaluadoModal"></span></div>
                <label class="label-control" for="selectEvaluador">Evaluador</label>
                <select class="form-select" id="selectEvaluador"></select>
                <div class="alert alert-warning py-2 mb-0 d-none" id="avisoDescartaBorrador">
                    El evaluador actual ya empezó a cargar su evaluación: al cambiarlo, ese borrador se descarta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEvaluador">Asignar</button>
            </div>
        </div>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
    const EDD_PANEL = {
        esAdmin: @json($esAdmin),
        estados: @json(config('edd.estados')),
        routes: {
            listar:     @json(route('rrhh.edd.panel.listar')),
            generar:    @json(route('rrhh.edd.generar')),
            formulario: @json(route('rrhh.edd.formularioEvaluacion', ':id')),
            candidatos: @json(route('rrhh.edd.candidatos', ':id')),
            asignar:    @json(route('rrhh.edd.asignarEvaluador', ':id')),
        },
    };
</script>
<script src="{{ asset('js/evaluaciones/index.js') }}"></script>
@endpush