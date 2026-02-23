@extends('layouts.app')

@section('title', 'Control de Novedades')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h3 class="pill-heading tituloVista">CONTROL DE NOVEDADES</h3>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row d-flex">
                    <div class="col-6 d-flex justify-content-start align-items-end gap-2">

                        <div class="col-md-2">
                            <label class="form-label">Desde</label>
                            <input type="date" id="filtroDesde" class="form-control" />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Hasta</label>
                            <input type="date" id="filtroHasta" class="form-control" />
                        </div>

                        <select id="area" name="area" class="form-select js-select-area" style="width: auto;"
                            {{ Auth::user()->rol !== 'Administrador/a' ? 'hidden' : '' }}>
                        </select>

                        <select id="idNovedad" name="idNovedad" class="form-select js-select-novedadFiltro" style="width: 300px;">
                        </select>

                        @if (Auth::user()->rol !== 'Administrador/a')
                            <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                        @endif

                        <select id="paraFinnegans" name="paraFinnegans" class="form-select js-select-novedadFinnegans" style="width: auto;">
                            <option value="" disabled selected>Seleccion tipo</option>
                            <option value="">Todas</option>
                            <option value="0">Informativas</option>
                            <option value="1">Para Finnegans</option>
                        </select>

                        <button type="button" id="btnLimpiarFiltros" class="btn-secundario d-flex align-items-center gap-1">
                            <i class="fa-solid fa-eraser"></i>Limpiar
                        </button>

                        <script>
                            const USER_ROLE = "{{ Auth::user()->rol }}";
                            const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                        </script>
                    </div>

                    <div class="col-6 text-end">

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <div class="card-body">
                    <table id="tb_control" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>FECHA</th>
                                <th>AREA</th>
                                <th>REGISTRANTE</th>
                                <th>COLABORADOR</th>
                                <th>LEGAJO</th>
                                <th>CODIGO</th>
                                <th>NOVEDAD</th>
                                <th>CENTROCOSTO</th>
                                <th>CANTIDAD</th>
                                <th>VALOR2</th>
                                <th>FECHAAPLICACION</th>
                                <th>EMPRESA</th>
                                <th>DESDE</th>
                                <th>HASTA</th>
                                <th>VALOR</th>
                                <th>DESCRIPCION</th>
                                <th>AÑO</th>
                                <th>LEGAJO</th>
                                <th>TIPO</th>
                                <th></th>
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

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-1">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloLegajo">
                        <i class="fa-solid fa-id-card me-2"></i> Detalle de Novedad
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalLegajo()"></button>
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
                                            <input type="text" id="inputRegistro" class="form-control" readonly>
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
                                            <input type="text" id="inputFechaAplicacion" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha desde</label>
                                            <input type="text" id="inputFechaDesde" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha hasta</label>
                                            <input type="text" id="inputFechaHasta" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Valor</label>
                                            <input type="text" id="inputValor" class="form-control" readonly>
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
                                            <input type="text" id="inputDescripcion" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2 d-none" id="divInfoVacaciones">
                                        <div class="col-lg-3">
                                            <label class="form-label">Tipo de Vacaciones</label>
                                            <input type="text" id="inputTipoVacaciones" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Año</label>
                                            <input type="text" id="inputAnnioVacaciones" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2 d-none" id="divInfoAtencionMedica">
                                        <div class="col-lg-2">
                                            <label class="form-label">N° Atención</label>
                                            <input type="text" id="inputNumAtencion" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Paciente</label>
                                            <input type="text" id="inputPacienteAtencion" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Concepto</label>
                                            <input type="text" id="inputConcepto" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Cuotas</label>
                                            <input type="text" id="inputCuotas" class="form-control" readonly required title="Horas Diarias">
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Importe cuotas</label>
                                            <input type="text" id="inputImporteCuotas" class="form-control" readonly required title="Horas Diarias">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="cerrarModalLegajo()">
                        Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/novedades/controlNovedades.js') }}"></script>
@endpush
