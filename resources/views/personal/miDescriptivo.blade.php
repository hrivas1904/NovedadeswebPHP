@extends('layouts.app')

@section('title', 'Mi Descriptivo')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>Inicio
            </a>
        </li>
        <li class="breadcrumb-item">Recursos Humanos</li>
        <li class="breadcrumb-item">Colaboradores</li>
        <li class="breadcrumb-item active" aria-current="page">Mi descriptivo</li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3">
    <h3 class="tituloVista mb-0">Mi descriptivo</h3>
    <h5 class="text-muted fw-bold" id="lblNombreRolDescriptivo">Nombre rol descriptivo</h5>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Identificación del puesto</h6>
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="text-muted">Área</label>
                    <input type="text" class="form-control" id="inputArea" readonly>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="text-muted">Sector/servicio</label>
                    <input type="text" class="form-control" id="inputSector" readonly>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="text-muted">Reporta a</label>
                    <input type="text" class="form-control" id="inputReporta" readonly>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="text-muted">Supervidor directo</label>
                    <input type="text" class="form-control" id="inputSupervisor" readonly>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="text-muted">Supervisa/personal a cargo</label>
                    <input type="text" class="form-control" id="inputSupervisa" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Propósito del puesto</h6>
            <input type="text" class="form-control" id="inputProposito" readonly>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Principales tareas y responsabilidades</h6>
            <input type="text" class="form-control" id="inputTareas" readonly>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Contexto del puesto</h6>
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <label class="text-muted">Relaciones internas</label>
                    <input type="text" class="form-control" id="inputRelInternas" readonly>
                </div>
                <div class="col-12 col-xl-6">
                    <label class="text-muted">Relaciones externas</label>
                    <input type="text" class="form-control" id="inputRelExternas" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Perfil del puesto</h6>
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <label class="text-muted">Formación</label>
                    <input type="text" class="form-control" id="inputFormacion" readonly>
                </div>
                <div class="col-12 col-xl-6">
                    <label class="text-muted">Experiencia</label>
                    <input type="text" class="form-control" id="inputExperiencia" readonly>
                </div>
                <div class="col-12 col-xl-6">
                    <label class="text-muted">Conocimientos</label>
                    <input type="text" class="form-control" id="inputConocimientos" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Competencias requeridas</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="text-muted">Competencias genéricas</label>
                    <input type="text" class="form-control" id="inputCompGenericas" readonly>
                </div>
                <div class="col-12">
                    <label class="text-muted">Competencias específicas</label>
                    <input type="text" class="form-control" id="inputCompEspecificas" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Compromiso</h6>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="text-muted">Capacitación continua</label>
                    <input type="text" class="form-control" id="inputCapacitacion" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted">Normas de calidad (ISO 9001)</label>
                    <input type="text" class="form-control" id="inputNormas" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted">Compromiso ambientas</label>
                    <input type="text" class="form-control" id="inputCompromiso" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted">Seguridad y salud ocupacional</label>
                    <input type="text" class="form-control" id="inputSeguridadSalud" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted">Ética y confidencialidad</label>
                    <input type="text" class="form-control" id="inputEtica" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h6 class="fw-bold text-muted">Contribución al Hospital</h6>
            <input type="text" class="form-control" id="inputContribucion" readonly>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush