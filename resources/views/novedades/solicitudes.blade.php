@extends('layouts.app')

@section('title', 'Adelantos de sueldo')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>Inicio
            </a>
        </li>
        <li class="breadcrumb-item">Recursos Humanos</li>
        <li class="breadcrumb-item">Novedades</li>
        <li class="breadcrumb-item active" aria-current="page">Adelantos de sueldo</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="tituloVista mb-0">Adelantos de sueldos</h3>
    <button type="button" id="btnAbrirModalSolicitud" class="btn btn-primary">
        Nueva solicitud
    </button>
</div>

<div class="row d-flex justify-content-start align-items-start">
    <div class="col-2 d-none d-xl-block">
        <div class="card">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>

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
                    <button id="btnLimpiarFiltros" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate"></i>
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-10 col-xxl-10">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-3">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                            <input type="text" id="fechaDesde" class="form-control" placeholder="Desde">
                        </div>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                            <input type="text" id="fechaHasta" class="form-control" placeholder="Hasta">
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Buscar solicitudes..." id="inputSearchSolicitudes">
                        </div>
                        @if (Auth::user()->rol === 'Administrador/a')
                        <button type="button" id="btnDepositarAdelantos" class="btn btn-sm btn-secondary">
                            Depositar
                        </button>
                        <button type="button" id="btnAprobarSolicitudes" class="btn btn-sm btn-outline-primary">
                            Aprobar
                        </button>
                        <button type="button" id="btnRechazarAdelantos" class="btn btn-sm btn-outline-danger">
                            Rechazar
                        </button>
                        <button type="button" id="btnExportarExcel" class="btn btn-outline-primary" data-url="{{ route('rrhh.solicitudes.exportarExcel') }}">
                            <i class="fa-solid fa-download"></i> Exportar
                        </button>
                        @endif
                    </div>
                </div>
                <div class="table-responsive table-card">
                    <table id="tb_solicitudes" class="table table-striped table-hover align-middle">
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
                                <th colspan="8" style="text-align:right">Importe total:</th>
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
                <form id="formCargaSolicitud">
                    <div class="d-flex flex-column gap-3">
                        <div class="card p-3">
                            <div class="d-flex flex-column gap-3">
                                <label class="fw-bold text-muted">
                                    Información general
                                </label>
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
                        </div>

                        <div class="card p-3">
                            <div class="d-flex flex-column gap-3">
                                <label class="fw-bold text-muted">
                                    Datos de la solicitud
                                </label>
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">Monto</label>
                                        <input class="form-control" id="inputMonto" style="text-align: end;" required></input>
                                        <input class="form-control" id="inputMontoEnviar" name="monto" hidden required></input>
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
    const USER_LEGAJO = "{{auth()->user()->legajo ?? 'null'}}";
    const USER_ROLE = "{{ auth()->user()->rol ?? 'null' }}";
</script>
@endpush