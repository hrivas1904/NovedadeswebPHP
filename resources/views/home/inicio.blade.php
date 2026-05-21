@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@section('content')

    <div class="container-md">
        <!--<div class="p-3 d-flex flex-row align-items-center justify-content-center text-center gap-3 my-3">
                                    <div>
                                        <h1 class="mb-0" style="color: var(--color-default);">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h1>
                                    </div>
                                </div>-->

        <div class="d-flex align-items-start gap-3 my-3">
            <div class="icon-box">
                <i class="fa-regular fa-user"></i>
            </div>
            <div>
                <h3 class="tituloVista mb-0">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h3>
                <p class="mb-0 text-muted">Aquí encontrarás avisos y novedades importantes.</p>
            </div>
        </div>

        <div class="card p-4">
            @if (Auth::user()->rol == 'Administrador/a')
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card p-2">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <h6 style="color: var(--color-default)" class="mb-0">COLABORADORES ACTIVOS</h6>
                                    <h2 style="color: var(--color-default)" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card p-2">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <h6 style="color: var(--color-default)" class="mb-0">NOVEDADES DEL DÍA</h6>
                                    <h2 style="color: var(--color-default)" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card p-2">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h6 style="color: var(--color-default)" class="mb-0">ADELANTOS PENDIENTES</h6>
                                    <h2 style="color: var(--color-default)" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card p-2">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div>
                                    <h6 style="color: var(--color-default)" class="mb-0">TICKETS ABIERTOS</h6>
                                    <h2 style="color: var(--color-default)" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

            <div class="row d-flex">
                <div class="col-xl-6 col-12">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div id="listaNotificaciones" class="contenedor-scroll">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card p-2">
                        <div class="card-header">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-list"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mt-1" style="color: var(--color-default)">MI RESÚMEN</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-start my-2">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis novedades</h6>
                                    <p class="text-muted">0</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-center my-2">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis solicitudes</h6>
                                    <p class="text-muted">0</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-center my-2">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis tickets</h6>
                                    <p class="text-muted">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card p-2">
                        <div class="card-header">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mt-1" style="color: var(--color-default)">FERIADOS</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            
                        </div>
                        <div class="card-footer">

                        </div>
                    </div>
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
