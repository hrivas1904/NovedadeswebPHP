@extends('layouts.app')

@section('title', 'Descriptivos de puesto')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">

        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>
                Inicio
            </a>
        </li>
        <li class="breadcrumb-item">
            Recursos Humanos
        </li>
        <li class="breadcrumb-item">
            Colaboradores
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Descriptivos de puesto
        </li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center">
    <h3 class="tituloVista mb-3">Descriptivos de puestos</h3>
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaDescriptivo">
        Nuevo descriptivo
    </button>
    @endif
</div>

<div class="row d-flex justify-content-start align-items-start">
    <div class="col-2 d-none d-xl-block">
        <div class="card h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>

                @if (Auth::user()->rol === 'Administrador/a' || Auth::user()->rol === 'Colaborador/a L2')
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleAreas">
                        <span>Áreas</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaAreas">
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-arrows-rotate"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-10 col-xxl-10">
        <div class="card p-2">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group buscador-personal">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" id="searchPersonal" class="form-control" placeholder="Buscar colaborador...">
                        <button class="btn btn-secondary" id="btnClearSearch" type="button">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <button type="button" id="btnExportExcel" class="btn btn-outline-primary d-none d-md-inline-flex align-items-center">
                        Exportar
                    </button>
                </div>
                <div class="table-responsive table-card">
                    <table id="tb_descriptivos" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>DESCRIPTIVO</th>
                                <th>ÁREA/RESPONSABLE</th>
                                <th>VERSIÓN</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
<div class="modal fade" id="modalAltaDescriptivo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <form id="formAltaDescriptivo" class="modal-content p-2">
            @csrf
            <div class="modal-header">
                <div class="d-flex flex-column">
                    <h5 class="modal-title mb-0">Nuevo descriptivo de puesto</h5>
                    <small class="text-muted">Se guarda como borrador (versión 1)</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">

                    <!-- IDENTIFICACIÓN -->
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">IDENTIFICACIÓN DEL PUESTO</label>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Puesto de trabajo</label>
                                    <select class="form-select" id="selectPuestoDescriptivo" name="codigoPuesto" required>
                                        <option value="">Seleccionar puesto</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Nombre del puesto</label>
                                    <input type="text" class="form-control" id="inputNombrePuestoDescriptivo" name="nombrePuesto" maxlength="512" required>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="text-muted">Área</label>
                                    <select class="form-select" id="selectAreaDescriptivo" name="idArea" required>
                                        <option value="">Seleccionar área</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="text-muted">Sector/servicio</label>
                                    <select class="form-select" id="selectServicioDescriptivo" name="idServicio" required>
                                        <option value="">Seleccionar servicio</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Reporta a</label>
                                    <input type="text" class="form-control" name="reportaA" maxlength="512">
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Supervisor directo</label>
                                    <input type="text" class="form-control" name="supervisorDirecto" maxlength="512">
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Supervisa/personal a cargo</label>
                                    <input type="text" class="form-control" name="supervisaA" maxlength="512">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROPÓSITO Y TAREAS -->
                    <div class="col-12 col-xl-6">
                        <div class="card p-3 h-100">
                            <div class="d-flex flex-column gap-3">
                                <label class="label-control text-muted fw-bold">PROPÓSITO DEL PUESTO</label>
                                <textarea class="form-control" name="propositoPuesto" rows="6"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card p-3 h-100">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="label-control text-muted fw-bold">PRINCIPALES TAREAS Y RESPONSABILIDADES</label>
                                    <small class="text-muted">Una por línea</small>
                                </div>
                                <textarea class="form-control" name="tareas" rows="6"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- CONTEXTO -->
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">CONTEXTO DEL PUESTO</label>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Relaciones internas</label>
                                    <textarea class="form-control" name="relacionesInternas" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Relaciones externas</label>
                                    <textarea class="form-control" name="relacionesExternas" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PERFIL -->
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">PERFIL DEL PUESTO</label>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Formación</label>
                                    <textarea class="form-control" name="formacion" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Experiencia</label>
                                    <textarea class="form-control" name="experiencia" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Conocimientos</label>
                                    <textarea class="form-control" name="conocimientos" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COMPETENCIAS GENÉRICAS -->
                    <div class="col-12 col-xl-6">
                        <div class="card p-3 h-100">
                            <div class="row g-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="label-control text-muted fw-bold">COMPETENCIAS GENÉRICAS</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-agregar-competencia"
                                        data-tipo="ORGANIZACIONAL" data-destino="#divCompetenciasGenericas">
                                        Agregar competencia
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3 my-1" id="divCompetenciasGenericas"></div>
                        </div>
                    </div>

                    <!-- COMPETENCIAS ESPECÍFICAS -->
                    <div class="col-12 col-xl-6">
                        <div class="card p-3 h-100">
                            <div class="row g-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="label-control text-muted fw-bold">COMPETENCIAS ESPECÍFICAS</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-agregar-competencia"
                                        data-tipo="FUNCIONAL" data-destino="#divCompetenciasEspecificas">
                                        Agregar competencia
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3 my-1" id="divCompetenciasEspecificas"></div>
                        </div>
                    </div>

                    <!-- COMPROMISO -->
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">COMPROMISO</label>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Capacitación continua</label>
                                    <textarea class="form-control" name="capacitacionContinua" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Normas de calidad (ISO 9001)</label>
                                    <textarea class="form-control" name="normasCalidad" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Compromiso ambiental</label>
                                    <textarea class="form-control" name="compromisoAmbiental" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Seguridad y salud ocupacional</label>
                                    <textarea class="form-control" name="seguridadSaludOcupacional" rows="3"></textarea>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Ética y confidencialidad</label>
                                    <textarea class="form-control" name="eticaConfidencialidad" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTRIBUCIÓN -->
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="d-flex flex-column gap-3">
                                <label class="label-control text-muted fw-bold">CONTRIBUCIÓN AL HOSPITAL</label>
                                <textarea class="form-control" name="contribucion" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar borrador</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalVerDescriptivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">
            <div class="modal-header">
                <div class="d-flex flex-column gap-1">
                    <h5 class="modal-title mb-0" data-campo="nombre_puesto">Descriptivo de puesto</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">v<span data-campo="version"></span></span>
                        <span class="badge" id="badgeEstadoDescriptivo"></span>
                        <small class="text-muted" data-campo="codigo_puesto"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-column gap-3">

                    <div class="card p-3">
                        <div class="d-flex flex-column gap-3">
                            <h6 class="fw-bold text-muted mb-0">Identificación del puesto</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="text-muted">Área</label>
                                    <div class="form-control bg-light campo-desc" data-campo="area"></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="text-muted">Sector/servicio</label>
                                    <div class="form-control bg-light campo-desc" data-campo="servicio"></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="text-muted">Reporta a</label>
                                    <div class="form-control bg-light campo-desc" data-campo="reporta_a"></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="text-muted">Supervisor directo</label>
                                    <div class="form-control bg-light campo-desc" data-campo="supervisor_directo"></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label class="text-muted">Supervisa/personal a cargo</label>
                                    <div class="form-control bg-light campo-desc" data-campo="supervisa_a"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-3">
                        <div class="d-flex flex-column gap-3">
                            <h6 class="fw-bold text-muted mb-0">Propósito del puesto</h6>
                            <div class="form-control bg-light campo-desc" data-campo="proposito_puesto"></div>
                        </div>
                    </div>

                    <div class="card p-3">
                        <div class="d-flex flex-column gap-3">
                            <h6 class="fw-bold text-muted mb-0">Principales tareas y responsabilidades</h6>
                            <div class="form-control bg-light campo-desc" data-campo="tareas"></div>
                        </div>
                    </div>

                    <div class="card p-3">
                        <div class="d-flex flex-column gap-3">
                            <h6 class="fw-bold text-muted mb-0">Contexto del puesto</h6>
                            <div class="row g-3">
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Relaciones internas</label>
                                    <div class="form-control bg-light campo-desc" data-campo="relaciones_internas"></div>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted">Relaciones externas</label>
                                    <div class="form-control bg-light campo-desc" data-campo="relaciones_externas"></div>
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
                                    <div class="form-control bg-light campo-desc" data-campo="formacion"></div>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Experiencia</label>
                                    <div class="form-control bg-light campo-desc" data-campo="experiencia"></div>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Conocimientos</label>
                                    <div class="form-control bg-light campo-desc" data-campo="conocimientos"></div>
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
                                    <div class="list-group" id="listaCompGenericas"></div>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="text-muted mb-1">Competencias específicas</label>
                                    <div class="list-group" id="listaCompEspecificas"></div>
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
                                    <div class="form-control bg-light campo-desc" data-campo="capacitacion_continua"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="text-muted">Normas de calidad (ISO 9001)</label>
                                    <div class="form-control bg-light campo-desc" data-campo="normas_calidad"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="text-muted">Compromiso ambiental</label>
                                    <div class="form-control bg-light campo-desc" data-campo="compromiso_ambiental"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="text-muted">Seguridad y salud ocupacional</label>
                                    <div class="form-control bg-light campo-desc" data-campo="seguridad_salud_ocupacional"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="text-muted">Ética y confidencialidad</label>
                                    <div class="form-control bg-light campo-desc" data-campo="etica_confidencialidad"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-3">
                        <div class="d-flex flex-column gap-3">
                            <h6 class="fw-bold text-muted mb-0">Contribución al Hospital</h6>
                            <div class="form-control bg-light campo-desc" data-campo="contribucion"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted" id="lblAuditoriaDescriptivo"></small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/personal/selectores.js') }}"></script>
<script src="{{ asset('js/descriptivos/competencias.js') }}"></script>
<script src="{{ asset('js/descriptivos/altaDescriptivos.js') }}"></script>
<script src="{{ asset('js/descriptivos/index.js') }}"></script>
@endpush