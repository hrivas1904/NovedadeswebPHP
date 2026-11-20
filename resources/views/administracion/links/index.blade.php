@extends('layouts.app')

@section('title', 'Links de Importación')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i> Inicio
            </a>
        </li>
        <li class="breadcrumb-item">
            Administración
        </li>
        <li class="breadcrumb-item">
            Links de importación
        </li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center">
    <h3 class="tituloVista mb-3">Links de importación</h3>
    @if (Auth::user()->rol === 'Administrador/a' || Auth::user()->id === 6)
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaLink">
        Nuevo link
    </button>
    @endif
</div>

<div class="d-flex flex-column gap-3">
    <select id="selectorEstado" class="form-select d-none"></select>

    <div class="row g-3" id="contenedorLinks">
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalAltaLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form id="formAltaLink">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Nuevo link</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="label-control text-muted" for="inputNombreArtefacto">Nombre del artefacto</label>
                            <input class="form-control" type="text" name="nombreArtefacto" id="inputNombreArtefacto" required>
                        </div>
                        <div class="col-12 col-md-6 col-xl-8">
                            <label class="label-control text-muted" for="inputDescripcionArtefacto">Descripción del artefacto</label>
                            <input class="form-control" type="text" name="descripcionArtefacto" id="inputDescripcionArtefacto" required>
                        </div>
                        <div class="col-12 col-md-8 col-xl-8">
                            <label class="label-control text-muted" for="inputLinkArtefacto">Link del artefacto</label>
                            <input class="form-control" type="text" name="linkArtefacto" id="inputLinkArtefacto" required>
                        </div>
                        <div class="col-12 col-md-4 col-xl-4">
                            <label class="form-label text-muted">
                                Ícono del artefacto
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i id="previewIconoArtefacto" class="fa-solid fa-link fs-5"></i>
                                </span>

                                <button class="btn btn-outline-primary btnSeleccionarIcono" type="button" id="btnSeleccionarIcono">
                                    Seleccionar ícono
                                </button>
                            </div>

                            <input type="hidden" name="iconoArtefacto" id="inputIconoArtefacto" value="fa-solid fa-link fs-5">

                            <div id="contenedorSelectorIconos" class="border rounded p-3 mt-2 d-none">
                                <div class="row g-2" id="galeriaIconos"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnCancelar" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush

@php
$userId = Auth::id();
$puedeEliminarLinks = Auth::user()->rol === 'Administrador/a' || $userId === 6;
@endphp

@push('scripts')
<script>
    const PUEDE_ELIMINAR_LINKS = @js($puedeEliminarLinks);
</script>
<script src="{{ asset('js/administracion/links/bmLinks.js') }}"></script>
<script src="{{ asset('js/administracion/links/index.js') }}"></script>
@endpush