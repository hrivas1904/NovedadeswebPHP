@extends('layouts.app')

@section('title', 'Mis Novedades')

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
        <li class="breadcrumb-item active" aria-current="page">Mis novedades</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="tituloVista mb-0">Mis novedades</h3>
        <p class="mb-0 text-muted">En esta sección se encuentran sus novedades mensuales.</p>
    </div>
</div>

<div class="card p-2">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="input-group filtro-fecha">
                <span class="input-group-text">
                    <i class="fa-regular fa-calendar"></i>
                </span>
                <input type="text" id="filtroDesde" class="form-control" placeholder="Desde" aria-label="Fecha desde">
            </div>

            <div class="input-group filtro-fecha">
                <span class="input-group-text">
                    <i class="fa-regular fa-calendar"></i>
                </span>

                <input type="text" id="filtroHasta" class="form-control" placeholder="Hasta" aria-label="Fecha hasta">
            </div>

            <div class="d-none">
                <select id="idNovedad" name="idNovedad" class="form-select js-select-novedadFiltro">
                </select>
            </div>

            <button type="button" id="btnLimpiarFiltros" class="btn btn-outline-secondary text-nowrap">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Limpiar filtros
            </button>

        </div>
        <div class="table-responsive table-card">
            <table id="tb_control" class="table table-striped align-middle nowrap">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>FECHA</th>
                        <th>REGISTRANTE</th>
                        <th>CODIGO</th>
                        <th>NOVEDAD</th>
                        <th>COLABORADOR</th>
                        <th>DESDE</th>
                        <th>HASTA</th>
                        <th>VALOR</th>
                        <th>DESCRIPCION</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalDetalleNovedad" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
    data-bs-backdrop="static">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content p-1">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloLegajo">
                    <i class="fa-solid fa-id-card me-2"></i> Detalle de Novedad
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalDetalleNovedad()"></button>
            </div>

            <div class="modal-body">
                <form id="formDetalleNovedad">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
                            <div class="section-divider mb-3">
                                <span>Información general</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">Registro N°</label>
                                        <input type="text" id="inputRegistro" name="idRegistro" class="form-control"
                                            readonly>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Fecha de registro</label>
                                        <input type="text" id="inputFechaReg" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Registrante</label>
                                        <input type="text" id="inputRegistrante" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="section-divider mb-3">
                                <span>Datos Personales</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-1">
                                        <label class="form-label">Legajo</label>
                                        <input type="text" id="inputLegajo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Colaborador</label>
                                        <input type="text" id="inputColaborador" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Estado</label>
                                        <input type="text" id="inputEstado" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">DNI</label>
                                        <input type="text" id="inputDni" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Área</label>
                                        <input type="text" id="inputArea" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Información de la novedad</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha aplicación</label>
                                        <input type="text" id="inputFechaAplicacion" class="form-control"
                                            readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha desde</label>
                                        <input type="date" id="inputFechaDesde" name="fechaDesde"
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha hasta</label>
                                        <input type="date" id="inputFechaHasta" name="fechaHasta"
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Valor</label>
                                        <input type="text" class="d-none" id="tipoValor"></input>
                                        <input type="text" id="inputValor" name="duracion"
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Código</label>
                                        <input type="text" id="inputCodigo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Novedad</label>
                                        <input type="text" id="inputNovedad" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Descripción</label>
                                        <input type="text" id="inputDescripcion" name="descripcion"
                                            class="form-control editable" readonly>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2 d-none" id="divInfoVacaciones">
                                    <div class="col-lg-3">
                                        <label class="form-label">Tipo de Vacaciones</label>
                                        <input type="text" id="inputTipoVacaciones" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Año</label>
                                        <input type="text" id="inputAnnioVacaciones" name="annio"
                                            class="form-control editable" readonly>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2 d-none" id="divInfoAtencionMedica">
                                    <div class="col-lg-2">
                                        <label class="form-label">N° Atención</label>
                                        <input type="text" id="inputNumAtencion" name='nAtencion'
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Paciente</label>
                                        <input type="text" id="inputPacienteAtencion" name="paciente"
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Concepto</label>
                                        <input type="text" id="inputConcepto" name="concepto"
                                            class="form-control editable" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Cuotas</label>
                                        <input type="text" id="inputCuotas" name="cuotas"
                                            class="form-control editable" readonly required title="Horas Diarias">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Importe cuotas</label>
                                        <input type="text" id="inputImporteCuotas" class="form-control" readonly
                                            required title="Horas Diarias">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                @if (Auth::user()->rol !== 'Colaborador/a')
                <button class="btn btn-secondary" id="btnHabilitarEdicion">
                    Editar
                </button>
                @endif
                <button class="btn btn-primary d-none" id="btnGuardarCambios">
                    Actualizar
                </button>
                <button class="btn btn-primary" onclick="cerrarModalDetalleNovedad()">
                    Atrás
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/novedades/misNovedades.js') }}"></script>
<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
    const USER_AREA_ID = "{{ Auth::user()->area_id }}";
    const USER_NAME = "{{ Auth::user()->username }}";
</script>
@endpush