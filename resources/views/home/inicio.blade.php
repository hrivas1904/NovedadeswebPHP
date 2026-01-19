@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@section('content')

    <div class="container-md">
        <div class="d-flex justify-content-center py-3">
            <h1 style="color: #1e293b;">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h1>
        </div>

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">NOTIFICACIONES</h3>
        </div>

        @if(Auth::user()->rol == 'Administrador/a')
        <div id="publicarNotificacion" class="row mb-4">
            <div class="col-12">
                <div class="empleado-box p-3">
                    <input id="txtNotificacionTitulo" class="form-control mb-2" placeholder="Escriba el asunto...">
                    <textarea id="txtNotificacion" class="form-control" placeholder="Escriba el comunicado..."></textarea>
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
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <div id="listaNotificaciones" class="empleado-box p-3">
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/home/mensajes.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
