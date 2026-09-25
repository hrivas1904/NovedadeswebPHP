@extends('edd.layout')

@section('edd-content')
    <section class="edd-panel mt-4">
        <h2 class="h5">Mi autoevaluación</h2>
        <p class="edd-muted">Tu reflexión sobre el desempeño, las competencias y los objetivos del período.</p>
        @if(!$tieneLegajo)
            <p class="edd-notice">Tu cuenta todavía no tiene un legajo vinculado. RRHH deberá completar ese dato para habilitar tu autoevaluación.</p>
        @else
            <div class="edd-empty"><strong>No tenés una autoevaluación habilitada.</strong><p>Se habilitará cuando RRHH configure el período y tu participación.</p></div>
        @endif
        <details class="mt-3">
            <summary>Ver estructura de la autoevaluación</summary>
            <div class="mt-4">
                @include('edd.partials.puntajes', ['tipoRespuesta' => 'autoevaluacion'])
                <p class="small edd-muted mt-3">Tus puntajes y comentarios se registrarán separados de los del evaluador.</p>
                <button type="button" class="btn btn-outline-primary" disabled aria-describedby="edd-disponibilidad">Guardar borrador</button>
                <button type="button" class="btn btn-primary" disabled aria-describedby="edd-disponibilidad">Enviar autoevaluación</button>
            </div>
        </details>
    </section>
@endsection
