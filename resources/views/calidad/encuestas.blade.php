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
                <button id="btnProcesar" class="btn btn-primary">
                    Procesar estructura
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
            <h5 class="fw-bold mb-3">Previsualización del archivo</h5>
            <form id="formGuardarResultados">
                <div class="row d-flex gap-3 mx-auto my-4">
                    <table id="tablaExcel" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
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
