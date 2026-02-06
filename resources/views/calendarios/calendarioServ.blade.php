@extends('layouts.app')

@section('title', 'Calendario de Servicios')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 mt-2 mb-0">
            <h3 class="pill-heading tituloVista">CALENDARIO DE SERVICIOS
            </h3>
        </div>

        <div class="empleado-box p-2">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <div class="calendar-header d-flex justify-content-between align-items-center bg-light p-2 rounded">
                        <button class="btn btn-tertiary" id="btnPrevMes">◀</button>
                        <h5 id="tituloMes" class="mb-0"></h5>
                        <button class="btn btn-tertiary" id="btnNextMes">▶</button>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-2 justify-content-lg-end align-items-center">
                        <div class="col-xl-4 col-lg-4 col-md-12">
                            <input id="nombreArea" class="form-control" readonly value="Servicios" disabled>
                            <input id="idArea" hidden readonly value="15">
                        </div>

                        <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                            <button type="button" id="btnCrearEvento" class="btn btn-primario w-100">
                                <i class="fa-solid fa-list-check"></i> Nuevo evento
                            </button>
                        </div>

                        <div class="col-xl-auto col-lg-auto col-md-4 col-3">
                            <button type="button" id="btnExportarPdf" class="btn btn-peligro w-100">
                                <i class="fa-solid fa-file-pdf"></i>
                            </button>
                        </div>

                        <div class="col-xl-auto col-lg-auto col-md-4 col-3">
                            <button type="button" id="btnExportarImagen" class="btn btn-terciario w-100">
                                <i class="fa-solid fa-image"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="calendarGrid" class="calendar-grid m-1">
            </div>
        </div>
    </div>

@endsection

@push('modals')
    <div class="modal fade" id="modalNuevaTarea" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
        data-bs-backdrop="static"" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formRegistrarEvento">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Crear nuevo evento</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="section-divider mb-3">
                            <span>Periodo del evento</span>
                        </div>
                        <div class="empleado-box p-3 mb-4">
                            <div class="row d-flex gap-3">
                                <div class="col-lg-3">
                                    <label for="fechaDesde" class="form-label">Desde</label>
                                    <input id="inputFechaDesde" name="fechaDesde" class="form-control" type="date"
                                        required>
                                </div>
                                <div class="col-lg-3">
                                    <label for="fechaHasta" class="form-label">Hasta</label>
                                    <input id="inputFechaHasta" name="fechaHasta" class="form-control" type="date"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Configuración del evento</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-lg-5">
                                    <label for="selectColaborador" class="form-label">Colaborador</label>
                                    <select id="selectColab" name="selectColab" class="form-select selectjs" required>
                                        <option value="">Seleccionar colaborador</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <label for="inputLegajo" class="form-label">Legajo</label>
                                    <input id="inputLegajo" name="legajo" class="form-control" type="text" readonly>
                                </div>
                                <div class="col-lg-4">
                                    <label for="inputServicio" class="form-label">Servicio</label>
                                    <input id="inputServicio" name="servicio" class="form-control" type="text"
                                        readonly>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-lg-4">
                                    <label for="selectTurnoEvento" class="form-label">Seleccione turno</label>
                                    <select id="selectTurnoEvento" name="turno" class="form-select" required>
                                        <option value="">Seleccione turno</option>
                                        <option value="Mañana">Mañana</option>
                                        <option value="Tarde">Tarde</option>
                                        <option value="Noche">Noche</option>
                                        <option value="Refuerzo">Refuerzo</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 d-flex align-items-end gap-2">
                                    <label class="form-label" for="selectCaja">Caja</label>
                                    <select id="selectCaja" name="caja" class="form-select" required>
                                        <option value="0">NO</option>
                                        <option value="1">SI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn-secundario" id="btnCerrarModalEvento">Cancelar</button>
                    <button type="button" class="btn-primario" id="btnGuardarEvento">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/calendario/calendarioServicio.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
