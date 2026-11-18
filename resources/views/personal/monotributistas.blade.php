@extends('layouts.app')

@section('title', 'Monotributistas')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">

        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>
                Inicio
            </a>
        </li>
        <li class="breadcrumb-item">
            Recursos Humanos
        </li>
        <li class="breadcrumb-item">
            Colaboradores
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Monotributistas
        </li>

    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center">
    <h3 class="tituloVista mb-3">Monotributistas</h3>
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaColaborador">
        Nuevo Colaborador
    </button>
    @endif
</div>

<div class="row d-flex justify-content-start align-items-start">
    <div class="col-2 d-none d-xl-block">
        <div class="card h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>

                @if (Auth::user()->rol === 'Administrador/a' || Auth::user()->rol === 'Colaborador/a L2')
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleAreas">
                        <span>Áreas</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaAreas">
                    </div>
                </div>
                @endif
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleCateg">
                        <span>Categorías</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaCateg">
                    </div>
                </div>
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleConvenios">
                        <span>Convenios</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaConvenios">
                    </div>
                </div>
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleEstados">
                        <span>Estados</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body" id="listaEstados">
                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="">
                            TODOS
                        </label>

                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="ACTIVO" checked>
                            ACTIVOS
                        </label>

                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="DE BAJA">
                            DE BAJA
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-arrows-rotate"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-10 col-xxl-10">
        <div class="card p-2">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group buscador-personal">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" id="searchPersonal" class="form-control" placeholder="Buscar colaborador...">
                        <button class="btn btn-secondary" id="btnClearSearch" type="button">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <button type="button" id="btnExportExcel" class="btn btn-outline-primary d-none d-md-inline-flex align-items-center">
                        Exportar
                    </button>
                </div>
                <div class="table-responsive table-card">
                    <table id="tb_monotributistas" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>COLABORADOR</th>
                                <th>DNI</th>
                                <th>CUIL</th>
                                <th>RAZÓN SOCIAL</th>
                                <th>SERVICIO</th>
                                <th>TIPO CONTRATO</th>
                                <th>FECHA DESDE</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="contenedorConstancia" class="d-none"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/personal/monotributistas.js') }}"></script>
@endpush