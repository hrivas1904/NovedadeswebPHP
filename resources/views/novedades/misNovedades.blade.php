@extends('layouts.app')

@section('title', 'Mis Novedades')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-box">
                    <img src="{{ asset('img/icons/novedades-logo.png') }}" style="height: 32px;" alt="Logo novedades">
                </div>
                <div>
                    <h3 class="tituloVista mb-0">MIS NOVEDADES</h3>
                    <p class="mb-0 text-muted">En esta sección se encuentran sus novedades mensuales.</p>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row align-items-end g-3">
                    <div class="col-6 col-sm-12 col-md-6 col-lg-4 col-xl-auto">
                        <input type="text" id="filtroDesde" class="form-control mx-1" placeholder="Desde" />
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-4 col-xl-auto">
                        <input type="text" id="filtroHasta" class="form-control mx-1" placeholder="Hasta" />
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                        <select id="idNovedad" name="idNovedad" class="form-select js-select-novedadFiltro w-100">
                        </select>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                        <select id="liquidada" name="liquidada" class="form-select w-100">
                            <option value="0" selected>A LIQUIDAR</option>
                            <option value="1">LIQUIDADAS</option>
                        </select>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-4 col-xl-auto">
                        <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary w-100">
                            <i class="fa-solid fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <div class="card-body">
                    <table id="tb_control" class="table table-bordered table-hover align-middle nowrap">
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
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
