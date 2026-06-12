<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

    <link rel="stylesheet" href="{{ asset('css/paletaColores.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styleDt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modales.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cronograma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssFiltroEcommerce.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssNavbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssDivAvisos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssCards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ayuda.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssDashboardCalidad.css') }}">
    <title>DASHBOARD DE CALIDAD</title>
</head>

<body style="background:#f5f9ff;">
    <div class="container-fluid">

        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <div class="header-info text-end">
                    <h1 class="dashboard-title">
                        DASHBOARD SGC
                    </h1>
                    <p class="dashboard-subtitle">
                        Estadísticas de encuestas de atención al paciente
                    </p>
                </div>
                <div class="header-divider"></div>
                <div class="header-brand">
                    <img src="{{ asset('img/logo-hp3c-white.png') }}" class="logo-navbar" alt="HP3C">
                </div>
            </div>
        </div>

        <div class="p-3">
            <div class="row mb-3 g-3 d-none">
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
                        Limpiar
                    </button>
                </div>
                <div class="col-md-2">
                    <button id="btnLimpiar" class="btn btn-danger w-100" onclick="exportarDashboardPDF(dashboardData)">
                        <i class="fa-solid fa-file-pdf"></i>
                        Informe
                    </button>
                </div>
            </div>

            <div class="justify-content-center">
                <div class="row mb-4 g-3">
                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="glass-section card-hover-radial text-center">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="col-2">
                                        <div class="icon-kpi">
                                            <i class="fa-solid fa-chart-line" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                    <div class="col-10">
                                        <h5>Promedio General Expectativas</h5>
                                            <p class="text-small">Sin distinción de encuestas</p>
                                    </div>
                                </div>
                                <p style="font-family:'Franklin Gothic Medium'; font-size:3rem" id="kpiSatisfaccion">0%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="glass-section card-hover-radial text-center" id="btnKpiPromedioGuardias">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="col-2">
                                        <div class="icon-kpi">
                                            <i class="fa-solid fa-user-doctor" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                    <div class="col-10">
                                        <h5>Promedio General Guardias</h5>
                                        <p class="text-small">Expectativas de las Guardias (A-P)</p>
                                    </div>
                                </div>
                                <p style="font-family:'Franklin Gothic Medium'; font-size:3rem"
                                    id="kpiGuardiaGeneral">0%</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="glass-section card-hover-radial text-center" id="btnKpiPromedioInternacion">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="col-2">
                                        <div class="icon-kpi">
                                            <i class="fa-solid fa-bed-pulse" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                    <div class="col-10">
                                        <h5>Promedio Gral Internación Estadía</h5>
                                        <p class="text-small">Expectativas Internación con Estadía</p>
                                    </div>
                                </div>
                                <p style="font-family:'Franklin Gothic Medium'; font-size:3rem"
                                    id="kpiInternacionGeneral">0%</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="glass-section card-hover-radial text-center" id="btnKpiPromedioAmbulatoria">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="col-2">
                                        <div class="icon-kpi">
                                            <i class="fa-solid fa-clipboard-list" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                    <div class="col-10">
                                        <h5>Promedio General Int Ambulatoria</h5>
                                        <p class="text-small">Expectativas de Internación Ambulatoria</p>
                                    </div>
                                </div>
                                <p style="font-family:'Franklin Gothic Medium'; font-size:3rem"
                                    id="kpiInternacionAmbulatoria">0%</p>
                            </div>
                        </div>
                    </div>
                </div>               

                <div class="section-divider text-center mt-4">
                    <span>
                        <h3 class="tituloVista">EXPECTATIVAS</h3>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="glass-section p-2">
                            <h5>Guardia (Adulto - Pediátrica)</h5>
                            <table id="tablaGuardias" class="table table-bordered table-wrapper">
                                <thead></thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="glass-section p-2">
                            <h5>Internación Estadía (Standard-Design-UTI's)</h5>
                            <table id="tablaAreas" class="table table-bordered table-wrapper">
                                <thead></thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="glass-section p-2">
                            <h5>Internación Ambulatoria</h5>
                            <table id="tablaExpAmb" class="table table-bordered table-wrapper">
                                <thead></thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalGuardias" tabindex="-1">
                    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-center">
                                <div class="quality-legend">
                                    <div class="legend-title">
                                        <i class="fa-solid fa-chart-pie me-2"></i>
                                        Referencias
                                    </div>
                                    <div class="legend-items">
                                        <div class="legend-item success">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>≥95% Meta Cumplida</span>
                                        </div>
                                        <div class="legend-item warning">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span> ≥90% - <95% Próxima a Cumplir</span>
                                        </div>
                                        <div class="legend-item danger">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                            <span>&lt;90% Meta Incumplida</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardGuardiaAdultoHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS GUARDIA ADULTO</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardGuardiaAdultoBody">
                                        <table id="tablaGuardia" class="table table-striped table-bordered table-wrapper w-100"></table>
                                    </div>
                                </div>
                                <div style="color: var(--color-default);" class="card glass-section p-2 mb-4">
                                    <div class="card-header d-flex gap-3 align-items-center" id="cardGuardiaPedHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS GUARDIA PEDIÁTRICA</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardGuardiaPedBody">
                                        <table id="tablaGuardiaPediatrica" class="table table-striped table-bordered table-wrapper w-100"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>

                <div class="modal fade" id="modalInternacionEst" tabindex="-1">
                    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-center">
                                <div class="quality-legend">
                                    <div class="legend-title">
                                        <i class="fa-solid fa-chart-pie me-2"></i>
                                        Referencias
                                    </div>
                                    <div class="legend-items">
                                        <div class="legend-item success">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>≥95% Meta Cumplida</span>
                                        </div>
                                        <div class="legend-item warning">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span> ≥90% - <95% Próxima a Cumplir</span>
                                        </div>
                                        <div class="legend-item danger">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                            <span>&lt;90% Meta Incumplida</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaGeneralHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - GENERAL</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaGeneralBody">
                                        <table id="tablaInternacionPreguntasGeneral" class="table table-bordered table-wrapper w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaStdHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - STANDARD</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaStdBody">
                                        <table id="tablaInternacionPreguntasStandard" class="table table-bordered w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaDsgHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - DESIGN</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaDsgBody">
                                        <table id="tablaInternacionPreguntasDesign" class="table table-bordered w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaOtrasHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - OTRAS</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaOtrasBody">
                                        <table id="tablaInternacionPreguntasOtros" class="table table-bordered w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaUtiaHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - UTI ADULTO</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaUtiaBody">
                                        <table id="tablaInternacionPreguntasUtia" class="table table-bordered w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaUtipHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - UTI PEDIÁTRICA</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaUtipBody">
                                        <table id="tablaInternacionPreguntasUtip" class="table table-bordered w-100"></table>
                                    </div>
                                </div>

                                <div class="card glass-section p-2 mb-4">
                                    <div style="color: var(--color-default);" class="card-header d-flex gap-3 align-items-center" id="cardEstadiaUtinHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN CON ESTADÍA - UTI NEO</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardEstadiaUtinBody">
                                        <table id="tablaInternacionPreguntasUtin" class="table table-bordered w-100"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>

                <div class="modal fade" id="modalAmbulatoria" tabindex="-1">
                    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-center">
                                <div class="quality-legend">
                                    <div class="legend-title">
                                        <i class="fa-solid fa-chart-pie me-2"></i>
                                        Referencias
                                    </div>
                                    <div class="legend-items">
                                        <div class="legend-item success">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>≥95% Meta Cumplida</span>
                                        </div>
                                        <div class="legend-item warning">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span> ≥90% - <95% Próxima a Cumplir</span>
                                        </div>
                                        <div class="legend-item danger">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                            <span>&lt;90% Meta Incumplida</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div style="color: var(--color-default);" class="card glass-section p-2 mb-4">
                                    <div class="card-header d-flex gap-3 align-items-center" id="cardAmbHeader" style="cursor: pointer">
                                        <h5 class="mb-0">RESULTADOS INTERNACIÓN AMBULATORIA</h5>
                                        <i class="fa-solid fa-circle-chevron-down"></i>
                                    </div>
                                    <div class="card-body d-none" id="cardAmbBody">
                                        <table id="tablaInternacionAmbPregunta" class="table table-bordered table-wrapper w-100"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <!--<button type="button" class="btn btn-primary btn-floating shadow-lg" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>-->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        const DASHBOARD_TOKEN = "{{ request()->route('token') }}";
    </script>
    <script src="{{ asset('js/calidad/dashboardPublico.js') }}"></script>
    <script src="{{ asset('js/calidad/dashboardPublicoMotion.js') }}"></script>

</body>

</html>
