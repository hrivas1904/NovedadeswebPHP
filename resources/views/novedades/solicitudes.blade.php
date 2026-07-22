@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
<div class="container-fluid">

    <div class="text-start mb-2">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="tituloVista mb-0">ADELANTOS DE SUELDOS</h3>
                <p class="mb-0 text-muted">Registro de solicitudes de adelanto de sueldo.</p>
            </div>
        </div>
    </div>

    <div class="row d-flex justify-content-start align-items-start">
        <div class="col-2 d-none d-xl-block">
            <div class="card" style="border-radius:15px;">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                            Filtros
                        </h5>
                        <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3">

                    <div class="filtro-box">
                        <div class="filtro-header" id="toggleEstado">
                            <span>Estados</span>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                        <div class="filtro-body" id="listaEstados">
                            <label class="filtro-item">
                                <input type="checkbox" class="check-estado" value="">
                                <span>TODAS</span>
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-estado" value="PENDIENTE" checked>
                                <span>PENDIENTES</span>
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-estado" value="APROBADA" checked>
                                <span>APROBADAS</span>
                            </label>
                            <label class="filtro-item">
                                <input type="checkbox" class="check-estado" value="RECHAZADA">
                                <span>RECHAZADAS</span>
                            </label>
                        </div>
                    </div>

                    @if (Auth::user()->rol === 'Administrador/a')
                    <div class="filtro-box">
                        <div class="filtro-header" id="toggleDepositado">
                            <span>Depósitos</span>
                            <i class="fa fa-chevron-down"></i>
                        </div>

                        <div class="filtro-body" id="listaSolicitudes">
                            <label class="filtro-item">
                                <input type="radio" name="filtroDeposito" class="check-deposito" value="">
                                <span>TODAS</span>
                            </label>

                            <label class="filtro-item">
                                <input type="radio" name="filtroDeposito" class="check-deposito" value="1">
                                <span>DEPOSITADOS</span>
                            </label>

                            <label class="filtro-item">
                                <input type="radio" name="filtroDeposito" class="check-deposito" value="0"
                                    checked>
                                <span>A DEPOSITAR</span>
                            </label>
                        </div>
                    </div>
                    @endif
                    <div class="col-12">
                        <button id="btnLimpiarFiltros" class="btn btn-secondary w-100">
                            <i class="fa fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-10 col-xxl-10">
            <div class="row align-items-end g-3 mb-3">
                <div class="col-6 col-sm-6 col-lg-4 col-xl-2">
                    <button type="button" id="btnAbrirModalSolicitud" class="btn btn-primary w-100">
                        Nueva solicitud
                    </button>
                </div>

                <div class="col-6 col-sm-6 col-lg-4 col-xl-2 d-none d-md-block">
                    <input type="text" id="fechaDesde" class="form-control w-100" placeholder="Desde">
                </div>

                <div class="col-6 col-sm-6 col-lg-4 col-xl-2 d-none d-md-block">
                    <input type="text" id="fechaHasta" class="form-control w-100" placeholder="Hasta">
                </div>

                @if (Auth::user()->rol === 'Administrador/a')
                <div class="col-3 col-sm-3 col-lg-2 col-xl-1 d-none d-md-block">
                    <button type="button" id="btnDepositarAdelantos" class="btn btn-primary w-100">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Depositar
                    </button>
                </div>
                <div class="col-3 col-sm-3 col-lg-2 col-xl-1 d-none d-md-block">
                    <button type="button" id="btnAprobarSolicitudes" class="btn btn-primary w-100">
                        <i class="fa-solid fa-square-check"></i> Aprobar
                    </button>
                </div>
                <div class="col-3 col-sm-3 col-lg-2 col-xl-1 d-none d-md-block">
                    <button type="button" id="btnRechazarAdelantos" class="btn btn-danger w-100">
                        <i class="fa-solid fa-square-xmark"></i> Rechazar
                    </button>
                </div>
                @endif

            </div>
            <div class="card" style="border-radius:15px;">
                <div class="my-2">
                </div>
                <div class="table-responsive px-2">
                    <table id="tb_solicitudes" class="table table-bordered table-hover align-middle nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>FECHA</th>
                                <th>LEGAJO</th>
                                <th>CUIL</th>
                                <th>COLABORADOR</th>
                                <th>ÁREA</th>
                                <th>CUENTA</th>
                                <th>CBU</th>
                                <th>IMPORTE</th>
                                <th>IMPORTE</th>
                                <th>COMPROBANTE</th>
                                <th>BANCO</th>
                                <th>OBSERVACIONES</th>
                                <th>ESTADO</th>
                                <th class="text-end">
                                    <div class="d-flex align-items-start justify-content-center gap-2">
                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        @if (Auth::user()->rol === 'Administrador/a')
                        <tfoot>
                            <tr>
                                <th colspan="8" style="text-align:right">Total Monto:</th>
                                <th id="total_adelanto_display"></th>
                                <th colspan="5"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalRegSolicitud" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Registrar Solicitud de Adelanto de Sueldo
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalSolicitud()"></button>
            </div>

            <div class="modal-body">
                <form id="formCargaSolicitud" action="{{ route('novedades.store') }}" method="POST">

                    <div class="section-divider mb-3">
                        <span>Información general</span>
                    </div>

                    <div class="empleado-box p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <label class="form-label">Fecha de registro</label>
                                <input type="text" class="form-control" name="fechaRegistro"
                                    value="{{ now()->format('d-m-Y') }}" readonly>
                            </div>
                            <input type="hidden" class="form-control" value="{{ Auth::user()->name }}"
                                name="registrante" readonly>
                            <input type="hidden" class="form-control" name="legajo" id="inputLegajo" required
                                value="{{ auth()->user()->legajo }}" readonly>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <label class="form-label">Tipo de solicitud</label>
                                <input class="form-control" name="tipoSolicitud" required readonly
                                    value="Adelanto de Sueldo"></input>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider mb-3">
                        <span>Datos de la solicitud</span>
                    </div>

                    <div class="empleado-box p-3">
                        <div class="row g-3">

                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <label class="form-label">Monto</label>
                                <input class="form-control" id="inputMonto" style="text-align: end;"
                                    required></input>
                                <input class="form-control" id="inputMontoEnviar" name="monto" hidden
                                    required></input>
                            </div>
                            <div class="col-lg-9 col-md-9 col-sm-12">
                                <label class="form-label">Descripción del monto</label>
                                <input class="form-control" id="inputDescripMonto" readonly></input>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <label class="form-label">Observaciones</label>
                                <input class="form-control" id="inputTipo" name="observ"></input>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalSolicitud()">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formCargaSolicitud" id="btnRegistrarNovedad">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Registrar solicitud
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/novedades/solicitudes.js') }}"></script>
<script>
    const USER_LEGAJO = {{auth() -> user() -> legajo ?? 'null'}};
    const USER_ROLE = "{{ auth()->user()->rol ?? 'null' }}";
</script>
@endpush