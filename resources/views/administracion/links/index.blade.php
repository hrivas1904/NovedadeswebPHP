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
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaLink">
        Nuevo link
    </button>
    @endif
</div>

<div class="d-flex flex-column gap-3">
    <select id="selectorEstado" class="form-select"></select>

    <div class="row g-3" id="contenedorLinks">
    </div>
</div>
@endsection

@push('modals')
<div class="modal modal-fade" id="modalAltaLink" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content>
            <div class=" modal-header">

        </div>
        <div class="modal-body">

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/administracion/links/bmLinks.js') }}"></script>
<script src="{{ asset('js/administracion/links/index.js') }}"></script>
@endpush
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
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaLink">
        Nuevo link
    </button>
    @endif
</div>

<div class="d-flex flex-column gap-3">
    <select id="selectorEstado" class="form-select"></select>

    <div class="row g-3" id="contenedorLinks">
    </div>
</div>
@endsection

@push('modals')
<div class="modal modal-fade" id="modalAltaLink" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloLegajo">
                    <i class="fa-solid fa-id-card me-2"></i> Legajo del colaborador
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalLegajo()"></button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/administracion/links/bmLinks.js') }}"></script>
<script src="{{ asset('js/administracion/links/index.js') }}"></script>
@endpush