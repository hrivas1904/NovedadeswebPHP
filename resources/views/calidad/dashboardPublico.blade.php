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
    <title>DASHBOARD DE CALIDAD</title>
</head>

<body>
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-box">
                    <img src="{{ asset('img/icons/dash-calidad-logo.png') }}" style="height: 32px;"
                        alt="Logo subir encuestas">
                </div>
                <div>
                    <h3 class="tituloVista mb-0">DASHBOARD DE CALIDAD</h3>
                    <p class="mb-0 text-muted">Estadísticas de encuestas de atención al paciente.</p>
                </div>
            </div>
        </div>

        <div class="card p-3">
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
                        <div class="card card-hover-radial text-center">
                            <div class="card-body">
                                <h5>Promedio General Expectativas</h5>
                                <p class="text-small">Sin distinción de encuestas</p>
                                <h2 id="kpiSatisfaccion">0%</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="card card-hover-radial text-center" id="btnKpiPromedioGuardias">
                            <div class="card-body">
                                <h5>Promedio General Guardias</h5>
                                <p class="text-small">Expectativas de las Guardias (A-P)</p>
                                <h2 id="kpiGuardiaGeneral">0%</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="card card-hover-radial text-center" id="btnKpiPromedioInternacion">
                            <div class="card-body">
                                <h5>Promedio General Internación con Estadía</h5>
                                <p class="text-small">Expectativas Internación con Estadía</p>
                                <h2 id="kpiInternacionGeneral">0%</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 col-12">
                        <div class="card card-hover-radial text-center" id="btnKpiPromedioAmbulatoria">
                            <div class="card-body">
                                <h5>Promedio General Int Ambulatoria</h5>
                                <p class="text-small">Expectativas de Internación Ambulatoria</p>
                                <h2 id="kpiInternacionAmbulatoria">0%</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2 d-inline-block shadow-sm">
                    <div class="leyenda-resultados d-flex flex-wrap align-items-center gap-3">
                        <h5 class="fw-bold">Referencias: </h5>
                        <span class="badge bg-success fs-6">≥95%: Meta cumplida</span>
                        <span class="badge bg-warning text-white fs-6">≥90%- <95: Meta próxima a cumplir</span>
                                <span class="badge bg-danger fs-6">
                                    <90%: Meta incumplida</span>
                    </div>
                </div>

                <div class="section-divider text-center mt-4">
                    <span>
                        <h3 class="tituloVista">EXPECTATIVAS</h3>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4 col-lg-6 col-12">
                        <div class="card p-2">
                            <h5>Guardia (Adulto - Pediátrica)</h5>
                            <table id="tablaGuardias" class="table table-bordered">
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
                        <div class="card p-2">
                            <h5>Internación Estadía (Standard-Design-UTI's)</h5>
                            <table id="tablaAreas" class="table table-bordered">
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
                        <div class="card p-2">
                            <h5>Internación Ambulatoria</h5>
                            <table id="tablaExpAmb" class="table table-bordered">
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

                <div class="section-divider text-center mt-4">
                    <span>
                        <h3 class="tituloVista">RESULTADOS POR TIPO DE ENCUESTAS</h3>
                    </span>
                </div>

                <section id="sectionResultadosGuardias">
                    <div class="card p-2 mb-4" id="cardGuardiaAdulto">
                        <h5>RESULTADOS GUARDIA ADULTO</h5>
                        <table id="tablaGuardia" class="table table-striped table-bordered w-100"></table>
                    </div>
                    <div class="card p-2" id="cardGuardiaPediatrica">
                        <h5>RESULTADOS GUARDIA PEDIÁTRICA</h5>
                        <table id="tablaGuardiaPediatrica" class="table table-striped table-bordered w-100"></table>
                    </div>
                </section>

                <section id="sectionResultadosInternacionEstadia">
                    <div class="card p-2 mt-4">
                        <h5>RESULTADOS INTERNACIÓN CON ESTADÍA</h5>
                        <table id="tablaInternacionPreguntas" class="table table-bordered w-100"></table>
                    </div>

                    <div class="card p-2 mt-4">
                        <h5>RESULTADOS INTERNACIÓN CON ESTADÍA - GENERAL</h5>
                        <table id="tablaInternacionPreguntasGeneral" class="table table-bordered w-100"></table>
                    </div>
                </section>

                <section id="sectionResultadosInternacionAmbulatoria">
                    <div class="card p-2 mt-4">
                        <h5>RESULTADOS INTERNACIÓN AMBULATORIA</h5>
                        <table id="tablaInternacionAmbPregunta" class="table table-bordered w-100"></table>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary btn-floating shadow-lg" id="btn-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

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

</body>

</html>
