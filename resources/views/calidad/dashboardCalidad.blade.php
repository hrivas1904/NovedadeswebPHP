@extends('layouts.app')

@section('title', 'Dashboard de Calidad')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">DASHBOARD SGC</h3>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" id="fechaHasta" class="form-control">
            </div>
            <div class="col-md-2">
                <button id="btnFiltrar" class="btn btn-primario w-100">
                    <i class="fa-solid fa-filter"></i>
                     Filtrar
                </button>
            </div>
            <div class="col-md-2">
                <button id="btnLimpiar" class="btn btn-secundario w-100">
                    <i class="fa-solid fa-eraser"></i>
                     Limpiar filtros
                </button>
            </div>
            <div class="col-md-2">
                <button id="btnLimpiar" class="btn btn-peligro w-100" onclick="exportarDashboardPDF(dashboardData)">
                    <i class="fa-solid fa-file-pdf"></i>
                     Informe
                </button>
            </div>
        </div>        

        <div class="container-fluid justify-content-center">
            <!-- CARDS -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5>Nivel de Satisfacción</h5>
                            <h2 id="kpiSatisfaccion">0%</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5>Total Encuestas</h5>
                            <h2 id="kpiEncuestas">0</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-2 col-3">
                <div class="leyenda-resultados mb-2 g-3">
                    <h5 class="fw-bold">Referencias: </h5>
                    <span class="badge bg-success fs-6">≥ 80% Excelente</span>
                    <span class="badge bg-warning text-white fs-6">≥ 60% Bueno</span>
                    <span class="badge bg-danger fs-6">≤ 60% Malo</span>
                </div>
            </div>

            <!-- TABLAS -->
            <div class="card p-2 mt-4">
                <h5>Guardia (Adulto - Pediátrica)</h5>
                <table id="tablaGuardias" class="table table-bordered"></table>
            </div>

            <div class="card p-2 mt-4">
                <h5>Internación por Área</h5>
                <table id="tablaAreas" class="table table-bordered"></table>
            </div>

            <div class="card p-2 mt-4">
                <h5>UTI Detalle</h5>
                <table id="tablaUti" class="table table-bordered"></table>
            </div>

            <div class="card p-2 mt-4 d-none">
                <h5>Internación (Ambulatoria vs No)</h5>
                <table id="tablaInternacion" class="table table-bordered"></table>
            </div>
        </div>       

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/calidad/analisisEncuestas.js') }}"></script>
    <script src="{{ asset('js/calidad/dashboard.js') }}"></script>
    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush