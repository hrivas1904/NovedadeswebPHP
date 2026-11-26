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
        <button class="btn btn-outline-secondary" id="btnVerAsistencia" onclick="verAsistencia()">
            Ver asistencia
        </button>
        <button class="btn btn-secondary" id="btnVerResultados" onclick="verTop3xGrupo()">
            Mejores 3
        </button>
        <button class="btn btn-outline-secondary" id="btnVerResultados" onclick="verResultadosxGrupo()">
            Resultados
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

<div class="modal fade" id="modalResultadosxGrupo" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-ranking-star me-2"></i>
                    Resultados completos por grupo
                </h5>

                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="d-flex justify-content-end mb-3">
                    <button type="button"
                        class="btn btn-outline-success"
                        id="btnExportarResultadosxGrupo">

                        <i class="fa-solid fa-file-excel me-2"></i>
                        Exportar Excel
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="tbResultadosxGrupo"
                        class="table table-striped table-hover table-sm w-100">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1" aria-labelledby="modalTop3xGrupo" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Asistencia confirmadas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-card p-2">
                    <table id="tbAsistencias" class="table table-striped table-hover">
                        <thead></thead>
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
<script src="{{ asset('js/personal/verAsistencias.js') }}"></script>
@endpush