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
                <div class="calendar-header d-flex justify-content-between align-items-center p-2 rounded">
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
                        <button type="button" id="btnCrearEvento" class="btn btn-primary w-100">
                            <i class="fa-solid fa-list-check"></i> Nuevo evento
                        </button>
                    </div>

                    <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                        <button type="button" id="btnModalReporte" class="btn btn-secondary w-100">
                            <i class="fa-solid fa-chart-bar"></i> Reporte
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
    data-bs-backdrop="static"" data-bs-backdrop=" static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form id="formRegistrarEvento">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Crear nuevo evento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalTarea()"></button>
                </div>
                <div class="modal-body">
                    <div class="section-divider mb-3">
                        <span>Información general</span>
                    </div>
                    <div class="empleado-box p-3 mb-4">
                        <div class="row d-flex">
                            <div class="col-lg-1">
                                <label for="fechaActual" class="form-label">Fecha</label>
                                <input id="fechaHoy" class="form-control" type="text" value="{{ Auth::user()->name }}" readonly>
                            </div>
                            <div class="col-lg-2">
                                <label for="registrante" class="form-label">Registrante</label>
                                <input id="registrante" name="registrante" class="form-control" type="text" value="{{ Auth::user()->name }}" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider mb-3">
                        <span>Detalle del evento</span>
                    </div>

                    <div class="empleado-box p-3 mb-4">
                        <div class="row g-3">
                            <table id="tbDetalleEventos" class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>LEGAJO</th>
                                        <th>COLABORADOR</th>
                                        <th>TURNO</th>
                                        <th>CAJA</th>
                                        <th>DESDE</th>
                                        <th>HASTA</th>
                                        <th>HS</th>
                                        <th>OBSERVACIONES</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <button type="button" id="btnAgregarDetalle" class="btn btn-primary">
                            <i class="fa-solid fa-square-plus"></i> Agregar
                        </button>
                    </div>

                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCerrarModalEvento" onclick="cerrarModalTarea()">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEvento">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExportarReporte" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
    data-bs-backdrop="static"" data-bs-backdrop=" static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Generar Reporte Calendario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalReporte()"></button>
            </div>
            <div class="modal-body">
                <div class="section-divider mb-3">
                    <span>Seleccione periodo de reporte</span>
                </div>
                <div class="empleado-box p-3 mb-4">
                    <div class="row d-flex">
                        <div class="col-6">
                            <label for="fechaDesde" class="form-label-sm text-muted">Desde</label>
                            <input id="fechaDesde" class="form-control" type="text">
                        </div>
                        <div class="col-6">
                            <label for="fechaHasta" class="form-label-sm text-muted">Hasta</label>
                            <input id="fechaHasta" class="form-control" type="text">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalReporte()">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGenerarReporte">Generar reporte</button>
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