@extends('edd.layout')

@section('edd-content')
    <div class="edd-panel-header mt-4">
        <div><h2 class="h5">Estructura del formulario</h2><p class="edd-muted mt-2 mb-0">Vista del recorrido previsto. Los campos se habilitarán al abrir una evaluación asignada.</p></div>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('rrhh.edd.equipo') }}">Volver a mi equipo</a>
    </div>
    <section class="edd-panel">
        <h3 class="h6">Colaborador, puesto y evaluador</h3>
        <p class="edd-muted">Estos datos se tomarán de la asignación del período.</p>
        @include('edd.partials.puntajes', ['tipoRespuesta' => 'evaluador'])
    </section>
    <p class="small edd-muted mt-3">El responsable recibirá la autoevaluación, calificará las mismas competencias del 1 al 4 y cerrará el proceso.</p>
    @include('edd.partials.flujo')
@endsection
