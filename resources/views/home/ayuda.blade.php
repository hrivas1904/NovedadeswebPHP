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
            <div class="row d-flex gap-3">
                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 my-2">
                    <select id="selectorTipoTicketArea" name="area" class="form-select w-100">
                        <option selected disabled value="">Seleccione área</option>
                        <option value="RRHH">ÁREA RRHH</option>
                        <option value="SISTEMAS">ÁREA SISTEMAS</option>
                    </select>
                </div>
                <div id="divTipoTicketRecursos" class="col-sm-12 col-md-6 col-lg-4 col-xl-3 my-2 d-none">
                    <select id="selectorTipoTicketRecursos" class="form-select w-100">
                        <option selected disabled value="">Seleccione motivo del ticket</option>
                        <option value="Error de datos personales">Error de datos personales</option>
                        <option value="Error en el registro personal de novedades">Error en el registro personal de novedades</option>
                        <option value="Error en la carga de novedades">Error en la carga de novedades</option>
                        <option value="Error en el registro de solicitud de adelanto">Error en el registro de solicitud de adelanto</option>
                        <option value="El sistema está lento">El sistema RRHH está lento</option>
                        <option value="Sugerencia de mejora">Sugerencia de mejora</option>
                    </select>
                </div>
                <div id="divTipoTicketSistemas" class="col-sm-12 col-md-6 col-lg-4 col-xl-3 my-2 d-none">
                    <select id="selectorTipoTicketSistemas" class="form-select w-100">
                        <option selected disabled value="">Seleccione motivo del ticket</option>
                        <option value="Sin conexión a internet">Sin conexión a internet</option>
                        <option value="La conexión a internet está lenta">La conexión a internet está lenta</option>
                        <option value="La compuntadora no enciende o está lenta">La compuntadora no enciende o está lenta</option>
                        <option value="Problemas con el mouse, teclado y/o monitor">Problemas con el mouse, teclado y/o monitor</option>
                        <option value="Problemas con la impresora">Problemas con la impresora</option>
                        <option value="No puedo enviar/recibir correos">No puedo enviar/recibir correos</option>
                    </select>
                </div>
                <input hidden name="tipo" id="inputTipoTicket"></input>
            </div>
            <textarea id="inputDescripcion" class="form-control" name="descripcion"></textarea>
            <div class="d-flex justify-content-end mt-3 gap-3">
                <button type="button" id='btnCancelarTicket' class="btn btn-secondary" onclick="limpiarFormulario()">
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
                            <th>ASUNTO</th>
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

@push('modals')
<div class="modal fade" id="modalChatTicket" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Chat del Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                <!-- Contenedor chat -->
                <div id="chatContainer" class="p-3" style="height: 400px; overflow-y: auto; background:#f5f5f5;">
                </div>

            </div>

            <div class="modal-footer">
                <div class="input-group w-100">
                    <input type="text" id="inputMensaje" class="form-control" placeholder="Escriba un mensaje...">
                    <button id="btnEnviarMensaje" class="btn btn-primary">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/home/tickets.js') }}"></script>
<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
    const USER_ID = "{{ Auth::user()->id }}";
</script>
@endpush