@extends('layouts.app')

@section('title', 'Evaluaciones de Desempeño')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i> Inicio
            </a>
        </li>
        <li class="breadcrumb-item"> Recursos Humanos</li>
        <li class="breadcrumb-item">Colaboradores</li>
        <li class="breadcrumb-item active" aria-current="page">Evaluaciones de desempeño</li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="tituloVista mb-0">Evaluaciones de desempeño</h3>
        <button type="button" class="btn btn-primary" id="btnNuevaEvaluacion">Nueva EDD</button>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Porcentaje de avance</label>
                <h4 class="fw-bold" style="color: var(--color-default);" id="lblPorcAvance">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Completas</label>
                <h4 class="fw-bold" style="color: var(--color-default);" id="lblCompletas">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">En curso</label>
                <h4 class="fw-bold" style="color: var(--color-default);" id="lblEnCurso">-</h4>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card p-2 text-center">
                <label class="text-muted">Pendientes</label>
                <h4 class="fw-bold" style="color: var(--color-default);" id="lblPendientes">-</h4>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3">
        <select class="form-select" id="selectorFiltroPeriodoEdd">
            <option value="">Seleccione periodo</option>
        </select>
        <select class="form-select" id="selectorFiltroAreaEdd">
            <option value="">Seleccione area</option>
        </select>
        <select class="form-select" id="selectorFiltroServicioEdd">
            <option value="">Seleccione servicio</option>
        </select>
        <select class="form-select" id="selectorFiltroEstadoEdd">
            <option value="">Seleccione estado</option>
        </select>
        <button class="btn btn-secondary" id="verGrupos">Grupos</button>
        <button class="btn btn-outline-primary" id="Exportar"><i class="fa-solid fa-download"></i> Exportar</button>
    </div>

    <div class="card p-1">
        <div class="table-card">
            <table class="table table-striped table-hover" id="tbEvaluaciones">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>COLABORADOR</th>
                        <th>ÁREA</th>
                        <th>SERVICIO</th>
                        <th>EVALUADOR</th>
                        <th>AUTOEVALUACIÓN</th>
                        <th>FECHA</th>
                        <th>PROMEDIO</th>
                        <th>EVALUACIÓN</th>
                        <th>FECHA</th>
                        <th>PROMEDIO</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@endsection

@push('modals')
@endpush

@push('scripts')
<script src="{{ asset('js/evaluaciones/index.js') }}"></script>
@endpush