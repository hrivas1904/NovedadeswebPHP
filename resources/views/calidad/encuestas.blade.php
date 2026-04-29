@extends('layouts.app')

@section('title', 'Encuestas de Calidad')

@section('content')

    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-box">
                    <img src="{{ asset('img/icons/importar-encuestas.png') }}" style="height: 32px;" alt="Logo subir encuestas">
                </div>
                <div>
                    <h3 class="tituloVista mb-0">IMPORTAR ENCUESTAS</h3>
                    <p class="mb-0 text-muted">Importación de encuestas de atención al paciente.</p>
                </div>
            </div>
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
    <script src="{{ asset('js/calidad/analisisEncuestas.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
