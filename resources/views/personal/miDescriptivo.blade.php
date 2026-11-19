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
    <h5 class="text-muted fw-bold mb-0" id="lblNombreRolDescriptivo"></h5>

    {{-- estado: sin puesto / pendiente / aceptado --}}
    <div id="divEstadoMiDescriptivo"></div>

    {{-- contenido: se muestra solo si hay descriptivo publicado --}}
    <div class="d-flex flex-column gap-3 d-none" id="divContenidoMiDescriptivo">

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Identificación del puesto</h6>
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
                        <label class="text-muted">Supervisor directo</label>
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
                <h6 class="fw-bold text-muted mb-0">Propósito del puesto</h6>
                <textarea class="form-control texto-descriptivo" id="inputProposito" readonly></textarea>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Principales tareas y responsabilidades</h6>
                <textarea class="form-control texto-descriptivo" id="inputTareas" readonly></textarea>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Contexto del puesto</h6>
                <div class="row g-3">
                    <div class="col-12 col-xl-6">
                        <label class="text-muted">Relaciones internas</label>
                        <textarea class="form-control texto-descriptivo" id="inputRelInternas" readonly></textarea>
                    </div>
                    <div class="col-12 col-xl-6">
                        <label class="text-muted">Relaciones externas</label>
                        <textarea class="form-control texto-descriptivo" id="inputRelExternas" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Perfil del puesto</h6>
                <div class="row g-3">
                    <div class="col-12 col-xl-4">
                        <label class="text-muted">Formación</label>
                        <textarea class="form-control texto-descriptivo" id="inputFormacion" readonly></textarea>
                    </div>
                    <div class="col-12 col-xl-4">
                        <label class="text-muted">Experiencia</label>
                        <textarea class="form-control texto-descriptivo" id="inputExperiencia" readonly></textarea>
                    </div>
                    <div class="col-12 col-xl-4">
                        <label class="text-muted">Conocimientos</label>
                        <textarea class="form-control texto-descriptivo" id="inputConocimientos" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Competencias requeridas</h6>
                <div class="row g-3">
                    <div class="col-12 col-xl-6">
                        <label class="text-muted mb-1">Competencias genéricas</label>
                        <div class="list-group" id="listaMiCompGenericas"></div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <label class="text-muted mb-1">Competencias específicas</label>
                        <div class="list-group" id="listaMiCompEspecificas"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Compromiso</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="text-muted">Capacitación continua</label>
                        <textarea class="form-control texto-descriptivo" id="inputCapacitacion" readonly></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="text-muted">Normas de calidad (ISO 9001)</label>
                        <textarea class="form-control texto-descriptivo" id="inputNormas" readonly></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="text-muted">Compromiso ambiental</label>
                        <textarea class="form-control texto-descriptivo" id="inputCompromiso" readonly></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="text-muted">Seguridad y salud ocupacional</label>
                        <textarea class="form-control texto-descriptivo" id="inputSeguridadSalud" readonly></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="text-muted">Ética y confidencialidad</label>
                        <textarea class="form-control texto-descriptivo" id="inputEtica" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <h6 class="fw-bold text-muted mb-0">Contribución al Hospital</h6>
                <textarea class="form-control texto-descriptivo" id="inputContribucion" readonly></textarea>
            </div>
        </div>

        {{-- aceptación: solo si está pendiente --}}
        <div class="d-flex justify-content-end d-none" id="divAceptarMiDescriptivo">
            <button type="button" class="btn btn-primary" id="btnAceptarMiDescriptivo">
                <i class="fa-solid fa-file-signature me-1"></i>Leí y acepto mi descriptivo
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .texto-descriptivo {
        resize: none;
        overflow: hidden;
        background-color: var(--bs-body-bg);
    }

    .texto-descriptivo:placeholder-shown {
        font-style: italic;
    }
</style>
<script src="{{ asset('js/descriptivos/miDescriptivo.js') }}"></script>
@endpush