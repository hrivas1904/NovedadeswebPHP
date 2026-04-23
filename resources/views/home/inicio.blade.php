@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@section('content')

<div class="container-md">
    <div class="">
        <div class="d-inline-flex align-items-start gap-3 my-3">
            <div class="icon-box-title">
                <img src="{{ asset('img/hand-wave.svg') }}" style="height: 60px;" alt="Saludo">
            </div>
            <div>
                <h1 class="mb-0" style="color: var(--color-default);">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h1>
                <p class="text-muted">Bienvenido/a</p>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-start gap-3 my-3">
        <div class="icon-box">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div>
            <h3 class="tituloVista mb-0">AVISOS</h3>
            <p class="mb-0 text-muted">Aquí encontrarás avisos y novedades importantes.</p>
        </div>
    </div>

    @if(Auth::user()->rol == 'Administrador/a')
    <div id="publicarNotificacion" class="row mb-4">
        <div class="col-12">
            <div class="card p-3">
                <div class="d-flex align-items-start gap-3 my-3">
                    <div class="icon-box" style="background: var(--color-default); color: var(--bg-card);">
                        <i class="fa-solid fa-pen"></i>
                    </div>
                    <div>
                        <h3 class="tituloVista mb-0">Publicar nuevo aviso</h3>
                        <p class="mb-0 text-muted">Escribí el asunto y el contenido del aviso.</p>
                    </div>
                </div>
                <input id="txtNotificacionTitulo" class="form-control mb-2" placeholder="Escriba el asunto...">
                <textarea id="txtNotificacion" class="form-control" placeholder="Escriba el comunicado..."></textarea>
                <div class="d-flex justify-content-end mt-3 gap-3">
                    <button type="button" id='btnCancelarRedactarComunicado' class="btn btn-secondary">
                        Cancelar
                    </button>
                    <button type="button" id='btnRedactarComunicado' class="btn btn-primary">
                        <i class="fa-solid fa-bullhorn"></i>
                        Publicar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="section-divider text-center mb-3">
        <span>Avisos Publicados</span>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div id="listaNotificaciones" class="card p-3">
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