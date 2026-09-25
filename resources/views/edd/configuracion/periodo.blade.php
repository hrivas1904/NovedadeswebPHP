@extends('edd.configuracion.layout')

@section('edd-configuracion')
    <section class="edd-panel">
        <div class="edd-panel-header">
            <h3 class="h6">Configuración del período</h3>
            <span class="badge text-bg-secondary">Pendiente de configurar</span>
        </div>
        <fieldset disabled aria-describedby="edd-disponibilidad">
            <legend class="visually-hidden">Datos del período</legend>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="edd-nombre">Nombre</label><input class="form-control" id="edd-nombre" value="{{ $periodoReferencia }}"></div>
                <div class="col-md-6"><label class="form-label" for="edd-institucion">Institución</label><input class="form-control" id="edd-institucion" value="{{ $institucion }}"></div>
                @foreach(['inicio' => 'Inicio de evaluación', 'fin' => 'Fin de evaluación', 'corte' => 'Corte de población', 'limite-devolucion' => 'Límite de devolución'] as $campo => $etiqueta)
                    <div class="col-md-6 col-xl-3"><label class="form-label" for="edd-{{ $campo }}">{{ $etiqueta }}</label><input class="form-control" type="date" id="edd-{{ $campo }}"></div>
                @endforeach
            </div>
        </fieldset>
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <h3 class="h6">Reglas por definir</h3>
                <ul class="edd-muted mb-0">
                    <li>Fechas y obligatoriedad de la autoevaluación.</li>
                    <li>Ponderaciones y criterios de cierre.</li>
                    <li>Constancia de devolución y firmas.</li>
                    <li>Excepciones, suplencias y reaperturas.</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h3 class="h6">Apertura del período</h3>
                <p class="edd-muted mb-0">RRHH deberá completar el instrumento, las fechas y las asignaciones antes de habilitar las evaluaciones.</p>
            </div>
        </div>
    </section>
    @include('edd.partials.flujo')
@endsection
