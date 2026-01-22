@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<h3 class="text-start tituloVista">DASHBOARD DE CONTROL GERENCIAL</h3>

<div class="container-fluid dashboard-container py-3">

    <div class="row mb-3">
        <div class="col-12">
            <div class="container-md g-3 card filter-card">
                <div class="card-body">
                    <div class="row g-3 d-flex align-items-end justify-content-around align-item-center">
                        <div class="col-md-2">
                            <label class="form-label">Desde</label>
                            <input type="date" id="filtroDesde" class="form-control" />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Hasta</label>
                            <input type="date" id="filtroHasta" class="form-control" />
                        </div>

                        <div class="col-md-auto d-flex">
                            <button id="btnAplicarFiltros" class="btn-primario flex-grow-1">
                                <i class="fa-solid fa-circle-check"></i> Aplicar filtros
                            </button>
                        </div>

                        <div class="col-md-auto d-flex">
                            <button id="btnLimpiarFiltros" class="btn-secundario flex-grow-1">
                                <i class="fa-solid fa-eraser"></i> Limpiar filtros
                            </button>
                        </div>
                        <div class="col-md-auto d-flex">
                            <button id="btnAplicarFiltros" class=btn-peligro flex-grow-1">
                                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div class="col-md-auto d-flex">
                            <button id="btnAplicarFiltros" class=btn-terciario flex-grow-1">
                                <i class="fa-solid fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Colaboradores activos</div>
                    <div id="kpiEmpleadosActivos" class="kpi-value">0</div>
                </div>
            </div>
        </div>

        <div class="col-xl-auto col-lg-auto col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Colaboradores de baja</div>
                    <div id="kpiEmpleadosBaja" class="kpi-value">0</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Total novedades</div>
                    <div id="kpiTotalNovedades" class="kpi-value">0</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Novedades mes actual</div>
                    <div id="kpiNovedadesMes" class="kpi-value">0</div>
                </div>
            </div>
        </div>

        <div class="col-xl-auto col-lg-auto col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Más frecuente</div>
                    <div id="kpiNovedadMasFrecuente" class="kpi-value-small">—</div>
                    <div id="kpiNovedadMasFrecuenteCantidad" class="kpi-subvalue">—</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Menos frecuente</div>
                    <div id="kpiNovedadMenosFrecuente" class="kpi-value-small">—</div>
                    <div id="kpiNovedadMenosFrecuenteCantidad" class="kpi-subvalue">—</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Área con más novedades</div>
                    <div id="kpiAreaMasNovedades" class="kpi-value-small">—</div>
                    <div id="kpiAreaMasNovedadesDetalle" class="kpi-subvalue">—</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-label">Área con menos novedades</div>
                    <div id="kpiAreaMenosNovedades" class="kpi-value-small">—</div>
                    <div id="kpiAreaMenosNovedadesDetalle" class="kpi-subvalue">—</div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-4">

            <div class="card chart-card mb-4">
                <div class="card-header">
                    <span class="chart-title">Novedades por tipo</span>
                </div>
                <div class="card-body">
                    <canvas id="chartNovedadesTipo"></canvas>
                </div>
            </div>

            <div class="card chart-card mb-4">
                <div class="card-header">
                    <span class="chart-title">Novedades por área</span>
                </div>
                <div class="card-body">
                    <canvas id="chartNovedadesAreas"></canvas>
                </div>
            </div>

        </div>

        <div class="col-lg-8">
            <div class="row g-4 mb-2">
                <div class="col-md-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <span class="chart-title">Novedades por mes</span>
                        </div>
                        <div class="card-body d-flex flex-column" style="height:300px">
                            <div class="flex-grow-1">
                                <canvas id="chartNovedadesMes"></canvas>
                            </div>
                            <div class="pt-2 small text-muted text-center">
                                Mes con más novedades: <span id="lblMesMaxNovedades">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <span class="chart-title">Empleados con más novedades</span>
                        </div>
                        <div class="card-body d-flex flex-column" style="height:300px">
                            <div class="flex-grow-1">
                                <canvas id="chartTopEmpleados"></canvas>
                            </div>

                            <div class="pt-2 small text-muted text-center">
                                Colaborador con más novedades:
                                <span id="lblEmpleadoTop">—</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header">
                            <span class="chart-title">Detalle por tipo</span>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="tblNovedadesTipo" class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header">
                            <span class="chart-title">Detalle por área</span>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="tblNovedadesArea" class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Área</th>
                                        <th>Cantidad</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>


</div>

@endsection

@push('scripts')
<script src="{{ asset('js/dashboard/dashboard.js') }}"></script>
@endpush