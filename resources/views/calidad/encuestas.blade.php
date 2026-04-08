@extends('layouts.app')

@section('title', 'Encuestas de Calidad')

@section('content')

    <div class="container-fluid">
        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">ENCUESTAS DE CALIDAD</h3>
        </div>

        <div class="row align-items-end g-2">

            <div class="col-12 col-md-auto">
                <label class="form-label mb-0">Tipo encuesta</label>
                <select id="selectTipoEncuesta" class="form-select"></select>
            </div>

            <div class="col-12 col-md-auto">
                <label class="form-label mb-0">Archivo</label>
                <input type="file" id="excelFile" accept=".xlsx,.xls,.csv" class="form-control">
            </div>

            <div class="col-12 col-md-auto">
                <button id="btnImportar" type="button" class="btn btn-primary w-100">
                    Importar Excel
                </button>
            </div>

            <div class="col-12 col-md-auto">
                <button type="button" id="btnAnalizar" class="btn btn-secondary w-100">
                    Analizar
                </button>
            </div>

        </div>

        <div class="container-fluid my-3 empleado-box p-3">
            <h5 class="fw-bold mb-3">Previsualización del archivo</h5>
            <div class="row d-flex gap-3 mx-auto my-4">
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
    <script src="{{ asset('js/calidad/analisisEncuestas.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
