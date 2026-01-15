@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between ms-auto align-item-end gap-3 mb-2">
        <h3 class="pill-heading tituloVista">MESA DE AYUDA</h3>
    </div>

    <div id="publicarNotificacion" class="row mb-4">
        <div class="col-12">
            <div class="empleado-box p-3">
                <input class="form-control">
                <div class="d-flex justify-content-end mt-3 gap-3">
                    <button type="button" id='btnCancelarTicket' class="btn-secundario">
                        Cancelar
                    </button>
                    <button type="button" id='btnEmitirTicket' class="btn-primario">
                        <i class="fa-solid fa-receipt"></i>
                        Emitir ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="listaNotificación" class="row mb-4">
        <div class="col-12">
            <div class="empleado-box p-3">

            </div>
        </div>
    </div>
</div>


@endsection