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

        <div class="card p-3">
            <div class="row mb-3 g-3">
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
                    <div class="card p-1 mt-4 gap-2">
                        <h5>RESULTADOS INTERNACIÓN CON ESTADÍA</h5>
                        <div class="card">
                            <div class="card-header" style="color: var(--color-default); cursor:pointer;" id="divStandardButton">
                                <label class="fw-bolder fs-6">STANDARD </label>
                                <i class="fa-solid fa-circle-chevron-down"></i>
                            </div>
                            <div class="card-body d-none" id="divStandardBody">
                                <table id="tablaInternacionPreguntasStandard" class="table table-bordered w-100"></table>
                            </div>                            
                        </div>

                        <div class="card">
                            <div class="card-header" style="color: var(--color-default); cursor:pointer;" id="divDesignButton">
                                <label class="fw-bolder fs-6">DESIGN </label>
                                <i class="fa-solid fa-circle-chevron-down"></i>
                            </div>
                            <div class="card-body d-none" id="divDesignBody">
                                <table id="tablaInternacionPreguntasDesign" class="table table-bordered w-100"></table>
                            </div>                            
                        </div>

                        <div class="card">
                            <div class="card-header" style="color: var(--color-default); cursor:pointer;" id="divUtisButton">
                                <label class="fw-bolder fs-6">UTI's </label>
                                <i class="fa-solid fa-circle-chevron-down"></i>
                            </div>
                            <div class="card-body d-none" id="divUtisBody">
                                <table id="tablaInternacionPreguntasUtis" class="table table-bordered w-100"></table>
                            </div>                            
                        </div>

                        <div class="card">
                            <div class="card-header" style="color: var(--color-default); cursor:pointer;" id="divOtrasButton">
                                <label class="fw-bolder fs-6">OTRAS </label>
                                <i class="fa-solid fa-circle-chevron-down"></i>
                            </div>
                            <div class="card-body d-none" id="divOtrasBody">
                                <table id="tablaInternacionPreguntasOtros" class="table table-bordered w-100"></table>
                            </div>                            
                        </div>                        
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

@endsection

@push('scripts')
    <script src="{{ asset('js/calidad/analisisEncuestas.js') }}"></script>
    <script src="{{ asset('js/calidad/dashboard.js') }}"></script>
    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
