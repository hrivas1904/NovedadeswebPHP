@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

    <div class="container-fluid">
        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 mb-2">
            <h3 class="pill-heading tituloVista">MESA DE AYUDA</h3>
        </div>

        <div class="text-start mb-4">
            <h5 style="color: #64748b">
                Desde aquí podés comunicarte con el área administrativa para realizar consultas, reportar inconvenientes o
                enviar sugerencias!
            </h5>
        </div>

        <div id="publicarTicket" class="row mb-4">
            <div class="col-12">
                <div class="empleado-box p-3">
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 my-2">
                        <select name="tipo" id="selectorTipoTicket" class="form-select w-100">
                            <option selected disabled value="">Seleccion motivo del ticket</option>
                            <option value="Error de datos personales">Error de datos personales</option>
                            <option value="Error en el registro personal de novedades">Error en el registro personal de
                                novedades</option>
                            <option value="Error en la carga de novedades">Error en la carga de novedades</option>
                            <option value="Error en el registro de solicitud de adelanto">Error en el registro de solicitud
                                de adelanto</option>
                            <option value="El sistema está lento">El sistema está lento</option>
                            <option value="Sugerencia de mejora">Sugerencia de mejora</option>
                        </select>
                    </div>
                    <input class="form-control" name="descripcion">
                    <div class="d-flex justify-content-end mt-3 gap-3">
                        <button type="button" id='btnCancelarTicket' class="btn btn-secondary">
                            Cancelar
                        </button>
                        <button type="button" id='btnEmitirTicket' class="btn-primary btn">
                            <i class="fa-solid fa-receipt"></i>
                            Enviar ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <h5>Historial de Consultas</h5>

        <div id="listaTickets" class="row mb-4">
            <div class="col-12">
                <div class="empleado-box p-3">
                    <div class="table-responsive">
                        <table id="tablaTickets" class="table table-striped table-bordered table-hover align-middle nowrap">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>FECHA</th>
                                    <th>COLABORADOR</th>
                                    <th>TIPO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>ESTADO</th>
                                    <th>FECHA RESOL</th>
                                    <th>RESUELTO POR</th>
                                    @if (Auth::user()->rol === 'Administrador/a')
                                        <th>ACCIONES</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/home/tickets.js') }}"></script>
    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
