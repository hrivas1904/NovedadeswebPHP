@extends('edd.layout')

@section('edd-content')
    <div class="edd-panel-header mt-4">
        <h2 class="h5">Resumen RRHH</h2>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('rrhh.edd.configuracion') }}">Ver configuración del período</a>
    </div>
    <div class="row g-3">
        @foreach(['Evaluaciones previstas', 'Finalizadas', 'En proceso', 'Pendientes'] as $indicador)
            <div class="col-6 col-xl-3">
                <section class="edd-panel">
                    <h3 class="h6 edd-muted mb-0">{{ $indicador }}</h3>
                    <p class="edd-metric" aria-label="Sin datos">—</p>
                    <small class="edd-muted">Período sin habilitar</small>
                </section>
            </div>
        @endforeach
    </div>
    <div class="row g-3 mt-1">
        <div class="col-lg-4">
            <section class="edd-panel">
                <h2 class="h6">Promedio hospital</h2>
                <p class="edd-metric">— <small class="fs-6 edd-muted">/ {{ max(array_keys($escala)) }}</small></p>
                <p class="edd-muted small mb-0">Se calculará con las evaluaciones cerradas, indicando la cantidad incluida.</p>
            </section>
        </div>
        @foreach(['Competencias más fuertes', 'Principales oportunidades'] as $titulo)
            <div class="col-lg-4">
                <section class="edd-panel">
                    <h2 class="h6">{{ $titulo }}</h2>
                    <p class="edd-muted mt-4 mb-0">Todavía no hay resultados para analizar.</p>
                </section>
            </div>
        @endforeach
    </div>
    <section class="edd-panel mt-4">
        <h2 class="h6">Resultados por área</h2>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th scope="col">Área</th><th scope="col">Previstas</th><th scope="col">Cerradas</th><th scope="col">Avance</th><th scope="col">Promedio / {{ max(array_keys($escala)) }}</th></tr></thead>
                <tbody><tr><td colspan="5"><div class="edd-empty">Las áreas aparecerán al configurar la población del período.</div></td></tr></tbody>
            </table>
        </div>
    </section>
    @include('edd.partials.flujo')
@endsection
