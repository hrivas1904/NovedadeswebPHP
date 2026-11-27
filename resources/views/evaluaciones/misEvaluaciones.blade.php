@extends('layouts.app')

@section('title', 'Mis evaluaciones')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i> Inicio
            </a>
        </li>
        <li class="breadcrumb-item"> Recursos Humanos</li>
        <li class="breadcrumb-item">Colaboradores</li>
        <li class="breadcrumb-item active" aria-current="page">Mis evaluaciones de desempeño</li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3">
    <h3 class="tituloVista mb-0">Mis evaluaciones de desempeño</h3>
</div>
@endsection

@push('modals')
@endpush

@push('scripts')
<script src="{{ asset('js/evaluaciones/index.js') }}"></script>
@endpush