@extends('edd.layout')

@section('edd-content')
    <section class="edd-panel mt-4">
        <h2 class="h5">Reportes e historial</h2>
        <p class="edd-muted">Seguimiento del período y consulta de resultados cerrados por área, competencia y colaborador.</p>
        <fieldset disabled aria-describedby="edd-disponibilidad">
            <legend class="visually-hidden">Filtros de reportes</legend>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="edd-reporte-periodo">Período</label><select class="form-select" id="edd-reporte-periodo"><option>Sin períodos habilitados</option></select></div>
                <div class="col-md-4"><label class="form-label" for="edd-reporte-area">Área</label><select class="form-select" id="edd-reporte-area"><option>Todas las áreas</option></select></div>
                <div class="col-md-4"><label class="form-label" for="edd-reporte-estado">Estado</label><select class="form-select" id="edd-reporte-estado"><option>Todos los estados</option>@foreach($estados as $estado)<option>{{ $estado->etiqueta() }}</option>@endforeach</select></div>
            </div>
        </fieldset>
        <div class="edd-empty"><strong>No hay resultados históricos disponibles.</strong><p>Los reportes conservarán el período, el instrumento y la escala de cada evaluación.</p></div>
    </section>
    <div class="row g-3 mt-1">
        @foreach(['Avance por área' => 'Previstas, pendientes, en proceso y cerradas.', 'Resultados por competencia' => 'Fortalezas, oportunidades y cantidad de respuestas.', 'Historial del colaborador' => 'Resultados, devoluciones y planes de acción por período.'] as $titulo => $descripcion)
            <div class="col-lg-4"><section class="edd-panel"><h3 class="h6">{{ $titulo }}</h3><p class="edd-muted small mb-0">{{ $descripcion }}</p></section></div>
        @endforeach
    </div>
@endsection
