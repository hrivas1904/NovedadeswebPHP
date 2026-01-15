@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@section('content')

<div class="container-md">
    <div class="d-flex justify-content-center p-5">
        <h1 style="color: #1e293b;">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h1>
    </div>

    <div class="d-flex justify-content-between ms-auto align-item-end gap-3 mb-2">
        <h3 class="pill-heading tituloVista">NOTIFICACIONES</h3>
    </div>

    <div id="publicarNotificacion" class="row mb-4">
        <div class="col-12">
            <div class="empleado-box p-3">
                <input class="form-control">
                <div class="d-flex justify-content-end mt-3 gap-3">
                    <button type="button" id='btnCancelarRedactarComunicado' class="btn-secundario">
                        Cancelar
                    </button>
                    <button type="button" id='btnRedactarComunicado' class="btn-primario">
                        <i class="fa-solid fa-bullhorn"></i>
                        Publicar
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