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
    <h3 class="tituloVista mb-3">Gestión de colaboradres</h3>
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" onclick="abrirModal()">
        Nuevo Link
    </button>
    @endif
</div>

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="icono-link" style="color: blue">
                        <i class="fs-2 fa-solid fa-user"></i>
                    </div>
                    <label class="text-muted fw-bold">Estado</label>
                </div>
                <div class="d-flex flex-column gap-3">
                    <h5 class="fw-bold">Titulo</h5>
                    <p class="">
                        Descripción
                    </p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-secondary">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Abrir artefacto
                    </button>
                    <div class="d-flex">
                        <button type="button" class="btn" title="Editar link">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button type="button" class="btn" title="Ocultar link">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                        <button type="button" class="btn" title="Eliminar link">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection