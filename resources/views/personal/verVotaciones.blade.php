@extends('layouts.app')

@section('title', 'Resultados votaciones')

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
            Votaciones
        </li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center">
    <h3 class="tituloVista mb-3">Resultados votaciones</h3>
    <div class="d-flex gap-3">
        <button class="btn btn-outline-secondary" id="btnVerResultados" onclick="verTop3xGrupo()">
            Ver resultados
        </button>
        <button class="btn btn-outline-primary" id="btnExportarResultados">
            <i class="fa-solid fa-download"></i> Exportar
        </button>
    </div>
</div>

<div class="card p-3">
    <div class="table-card">
        <table id="tbResultadosVotaciones" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>VOTO N°</th>
                    <th>FECHA</th>
                    <th>VOTANTE</th>
                    <th>G1 COLABORADOR</th>
                    <th>G1 SITUACIÓN</th>
                    <th>G2 COLABORADOR</th>
                    <th>G2 SITUACIÓN</th>
                    <th>G3 COLABORADOR</th>
                    <th>G3 SITUACIÓN</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalTop3xGrupo" tabindex="-1" aria-labelledby="modalTop3xGrupo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Top 3 por Grupo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-card">
                    <table id="tbTop3" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>GRUPO</th>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>CANTIDAD DE VOTOS</th>
                                <th>POSICIÓN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/personal/verVotaciones.js') }}"></script>
@endpush