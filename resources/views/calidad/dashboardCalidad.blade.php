@extends('layouts.app')

@section('title', 'Dashboard de Calidad')

@section('content')

    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-box">
                    <img src="{{ asset('img/icons/dash-calidad-logo.png') }}" style="height: 32px;" alt="Logo subir encuestas">
                </div>
                <div>
                    <h3 class="tituloVista mb-0">DASHBOARD DE CALIDAD</h3>
                    <p class="mb-0 text-muted">Estadísticas de encuestas de atención al paciente.</p>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" id="fechaHasta" class="form-control">
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
            <div class="col-md-2">
                <button id="btnLimpiar" class="btn btn-danger w-100" onclick="exportarDashboardPDF(dashboardData)">
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
            <h3 class="pill-heading tituloVista mt-3">INFORMACIÓN RESUMIDA</h3>
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

            <div class="card p-2 mt-4 d-none">
                <h5>Internación (Ambulatoria vs No)</h5>
                <table id="tablaInternacion" class="table table-bordered"></table>
            </div>

            <h3 class="pill-heading tituloVista mt-3">RESPUESTAS POR TIPO DE ENCUESTAS Y ÁREAS</h3>

            <div class="card p-2 mt-4" id="cardGuardiaAdulto">
                <h5>RESULTADOS GUARDIA ADULTO</h5>

                <table id="tablaGuardia" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Pregunta</th>
                            <th>Positivos</th>
                            <th>Negativos</th>
                            <th>No aplica</th>
                            <th>Total</th>
                            <th>% Positivos</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="card p-2 mt-4">
                <h5>RESULTADOS INTERNACIÓN CON ESTADÍA</h5>
                <table id="tablaInternacionPreguntas" class="table table-bordered w-100"></table>
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
