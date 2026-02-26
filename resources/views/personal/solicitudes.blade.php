@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
    <div class="container-fluid">

        <div class="text-start mb-4">
            <h3 class="pill-heading tituloVista">SOLICITUD DE ADELANTO DE SUELDO</h3>
        </div>

        <button type="button" id="btnAbrirModalSolicitud" class="btn-primario mb-3"> Nueva solicitud </button>

        <div class="card" style="border-radius:15px;">
            <div class="my-2">
            </div>
            @if (Auth::user()->rol === 'Administrador/a')
                <div class="table-responsive px-2">
                    <table id="tb_solicitudes" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>FECHA</th>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>ÁREA</th>
                                <th>MONTO</th>
                                <th>OBSERVACIONES</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endif
        </div>

    </div>
@endsection

@push('modals')
    <div class="modal fade" id="modalRegSolicitud" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
        data-bs-backdrop="static">

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

                                <div class="col-lg-5 col-md-8 col-sm-12">
                                    <label class="form-label">Registrante</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}"
                                        name="registrante" readonly>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="form-label">Rol</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->rol }}" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Información del colaborador</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-lg-2 col-md-3 col-sm-12">
                                    <label class="form-label">Legajo</label>
                                    <input type="number" class="form-control" name="legajo" id="inputLegajo" required
                                        value="{{ auth()->user()->legajo }}" readonly>
                                </div>

                                <div class="col-lg-6 col-md-9 col-sm-12">
                                    <label class="form-label">Colaborador</label>
                                    <input type="text" class="form-control" name="colaborador" id="inputColaborador"
                                        required readonly>
                                </div>

                                <div class="col-lg-4 col-md-7 col-sm-12">
                                    <label class="form-label">Servicio</label>
                                    <input type="text" class="form-control" name="servicio" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Datos de la solicitud</span>
                        </div>

                        <div class="empleado-box p-3">
                            <div class="row g-3">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">Tipo de solicitud</label>
                                    <input class="form-control" name="tipoSolicitud" readonly value="Adelanto de Sueldo"></input>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">Monto</label>
                                    <input class="form-control" id="inputMonto" style="text-align: end;"></input>
                                    <input class="form-control" id="inputMontoEnviar" name="monto" hidden></input>
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
                    <button type="button" class="btn btn-secundario" onclick="cerrarModalSolicitud()">Cancelar</button>
                    <button type="submit" class="btn btn-primario" form="formCargaSolicitud" id="btnRegistrarNovedad">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Registrar novedad
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/personal/solicitudes.js') }}"></script>
    <script>
        const USER_LEGAJO = {{ auth()->user()->legajo ?? 'null' }};
        const USER_ROLE = {{ auth()->user()->rol ?? 'null' }};
    </script>
@endpush
