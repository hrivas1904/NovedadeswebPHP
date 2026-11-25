@extends('layouts.app')

@section('title', 'Evaluaciones de Desempeño')

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
        <li class="breadcrumb-item active" aria-current="page">Evaluaciones de desempeño</li>
    </ol>
</nav>
@endsection

@push('modals')
@endpush

@push('scripts')
@endpush