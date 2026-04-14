@extends('layouts.app')

@section('title', 'Respuestas encuestas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
        <h3 class="pill-heading tituloVista">RESPUESTAS DE ENCUESTAS</h3>
    </div>

    <div class="row align-items-end g-2">

        <div class="col-md-3">
            <label class="form-label mb-0 text-muted">Desde</label>
            <input type="date" id="fechaDesde" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-0 text-muted">Hasta</label>
            <input type="date" id="fechaHasta" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-0 text-muted">Tipo encuesta</label>
            <select id="selectTipoEncuesta" class="form-select"></select>
        </div>
        <div class="col-md-2">
            <button id="btnFiltrar" class="btn btn-primary w-100">
                <i class="fa-solid fa-filter"></i>
                Filtrar
            </button>
        </div>
        <div class="col-md-2">
            <button id="btnLimpiar" class="btn btn-secondary w-100">
                <i class="fa-solid fa-eraser"></i>
                Limpiar filtros
            </button>
        </div>

    </div>

    <div class="container-fluid my-3 empleado-box p-3">
        <h5 class="fw-bold mb-3">Previsualización del archivo</h5>
        <div class="row d-flex mx-auto my-4">
            <table id="tablaExcel" class="table table-striped table-bordered table-hover align-middle nowrap">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/calidad/respuestasEncuestas.js') }}"></script>
@endpush