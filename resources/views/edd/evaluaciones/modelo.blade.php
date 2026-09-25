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
    <section class="edd-panel mt-4">
        <fieldset disabled aria-describedby="edd-disponibilidad">
            <legend>Fortalezas, desarrollo y plan de acción</legend>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="edd-fortalezas">Fortalezas</label><textarea class="form-control" id="edd-fortalezas" rows="3"></textarea></div>
                <div class="col-md-6"><label class="form-label" for="edd-desarrollo">Aspectos a desarrollar</label><textarea class="form-control" id="edd-desarrollo" rows="3"></textarea></div>
                <div class="col-12"><label class="form-label" for="edd-accion">Acción / necesidad de capacitación</label><textarea class="form-control" id="edd-accion" rows="2"></textarea></div>
                <div class="col-md-6"><label class="form-label" for="edd-responsable-accion">Responsable del plan</label><input class="form-control" id="edd-responsable-accion"></div>
                <div class="col-md-6"><label class="form-label" for="edd-plazo">Fecha objetivo</label><input class="form-control" type="date" id="edd-plazo"></div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4"><button type="button" class="btn btn-outline-primary">Guardar borrador</button><button type="button" class="btn btn-primary">Completar evaluación</button></div>
        </fieldset>
    </section>
    @include('edd.devolucion.formulario')
    @include('edd.cierre.resumen')
    @include('edd.partials.flujo')
@endsection
