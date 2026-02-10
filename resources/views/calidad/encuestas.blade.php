@extends('layouts.app')

@section('title', 'Encuestas de Calidad')

@section('content')

    <div class="container-fluid">
        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">ENCUESTAS DE CALIDAD</h3>
        </div>

        <div class="row d-flex align-item-center justify-content-start">
            <div class="col-auto">
                <input type="file" id="excelFile" accept=".xlsx,.xls,.csv" class="btn-primario">
                <button id="btnImportar" class="btn-primario">
                    Importar Excel
                </button>
            </div>
            <div class="col-auto">
                <select id="selectTipoEncuesta" class="form-control"></select>
            </div>
            <div class="col-2">
                <button id="btnAnalizarArchivo" class="btn btn-primario">
                    Analizar
                </button>
            </div>
        </div>

        <div class="container-fluid my-3 empleado-box p-3">
            <h5 class="fw-bold mb-3">Resultados del Análisis</h5>
            <form id="formGuardarResultados">
                <div class="row d-flex gap-3">
                    <div class="col-lg-2">
                        <label class="label-control">Tipo de encuesta</label>
                        <input id="inputTipoEncuesta" class="form-control" type="text"></input>
                    </div>
                    <div class="col-lg-2">
                        <label class="label-control">Desde</label>
                        <input id="inputFechaDesde" class="form-control" type="date"></input>
                    </div>
                    <div class="col-lg-2">
                        <label class="label-control">Hasta</label>
                        <input id="inputFechaHasta" class="form-control" type="date"></input>
                    </div>
                </div>

                <div class="row d-flex gap-3 mx-auto my-4">
                    <table id="tablaExcel" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead>
                            <th>Pregunta</th>
                            <th>Rtas Positivas</th>
                            <th>Rtas Negativas</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/calidad/analisisEncuestas.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
