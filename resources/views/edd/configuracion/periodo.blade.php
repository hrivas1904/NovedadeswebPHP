@extends('edd.configuracion.layout')

@section('edd-configuracion')
    <section class="edd-panel">
        <div class="edd-panel-header">
            <h3 class="h6">{{ $periodo ? 'Editar período' : 'Crear período' }}</h3>
            <span class="badge text-bg-secondary">{{ $periodo ? ucfirst($periodo['estado']) : 'Sin guardar' }}</span>
        </div>
        <p class="edd-muted">Podés guardar el borrador y completar las fechas y reglas después. Guardarlo todavía no habilita evaluaciones.</p>
        <form method="post" action="{{ $periodo ? route('rrhh.edd.periodos.update', $periodo['id']) : route('rrhh.edd.periodos.store') }}" data-edd-form>
            @csrf
            @if($periodo)
                @method('PATCH')
                <input type="hidden" name="revision" value="{{ $periodo['revision'] }}">
            @endif
            <fieldset @disabled(!$almacenamientoDisponible || ($periodo && $periodo['estado'] !== 'borrador'))>
                <legend class="visually-hidden">Datos y reglas del período</legend>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label" for="edd-codigo">Código *</label><input class="form-control" id="edd-codigo" name="codigo" maxlength="40" required placeholder="EDD-2026" value="{{ $periodo['codigo'] ?? '' }}"></div>
                    <div class="col-md-6"><label class="form-label" for="edd-nombre">Nombre *</label><input class="form-control" id="edd-nombre" name="nombre" maxlength="160" required placeholder="{{ config('edd.periodo_referencia') }}" value="{{ $periodo['nombre'] ?? '' }}"></div>
                    <div class="col-md-3"><label class="form-label" for="edd-anio">Año *</label><input class="form-control" id="edd-anio" name="anio" type="number" min="2000" max="2100" required value="{{ $periodo['anio'] ?? now()->year }}"></div>
                    @foreach(['fecha_corte' => 'Corte de población', 'inicio_evaluacion' => 'Inicio de evaluación', 'fin_evaluacion' => 'Fin de evaluación', 'limite_devolucion' => 'Límite de devolución'] as $campo => $etiqueta)
                        <div class="col-md-6 col-xl-3"><label class="form-label" for="edd-{{ $campo }}">{{ $etiqueta }}</label><input class="form-control" type="date" id="edd-{{ $campo }}" name="{{ $campo }}" value="{{ $periodo[$campo] ?? '' }}"></div>
                    @endforeach
                    <div class="col-md-6">
                        <label class="form-label" for="edd-autoevaluacion">Autoevaluación</label>
                        <select class="form-select" id="edd-autoevaluacion" name="autoevaluacion">
                            <option value="">A definir</option>
                            @foreach(['obligatoria' => 'Obligatoria', 'opcional' => 'Opcional', 'deshabilitada' => 'Deshabilitada'] as $valor => $etiqueta)<option value="{{ $valor }}" @selected(($periodo['reglas']['autoevaluacion'] ?? null) === $valor)>{{ $etiqueta }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="edd-visibilidad">El evaluador puede ver la autoevaluación</label>
                        <select class="form-select" id="edd-visibilidad" name="visibilidad_autoevaluacion">
                            <option value="">A definir</option>
                            <option value="al_enviar" @selected(($periodo['reglas']['visibilidad_autoevaluacion'] ?? null) === 'al_enviar')>Cuando el colaborador la envía</option>
                            <option value="al_completar_evaluador" @selected(($periodo['reglas']['visibilidad_autoevaluacion'] ?? null) === 'al_completar_evaluador')>Después de completar la evaluación del responsable</option>
                        </select>
                    </div>
                    @foreach(['inicio_autoevaluacion' => 'Inicio de autoevaluación', 'fin_autoevaluacion' => 'Fin de autoevaluación'] as $campo => $etiqueta)
                        <div class="col-md-6"><label class="form-label" for="edd-{{ $campo }}">{{ $etiqueta }}</label><input class="form-control" type="date" id="edd-{{ $campo }}" name="{{ $campo }}" value="{{ $periodo[$campo] ?? '' }}"></div>
                    @endforeach
                    <div class="col-md-6"><label class="form-label" for="edd-condiciones">Condiciones de devolución y cierre</label><textarea class="form-control" rows="4" id="edd-condiciones" name="condiciones_cierre" maxlength="4000" placeholder="Describí la evidencia de devolución, firmas o condiciones que RRHH deberá definir.">{{ $periodo['reglas']['condiciones_cierre'] ?? '' }}</textarea></div>
                    <div class="col-md-6"><label class="form-label" for="edd-excepciones">Criterios para excepciones y suplencias</label><textarea class="form-control" rows="4" id="edd-excepciones" name="excepciones" maxlength="4000">{{ $periodo['reglas']['excepciones'] ?? '' }}</textarea></div>
                </div>
                <p class="small edd-muted mt-3">Las condiciones escritas se conservarán como definición de RRHH para implementar la apertura y el cierre.</p>
                <button class="btn btn-primary" type="submit" data-edd-guardar disabled>{{ $periodo ? 'Guardar cambios' : 'Crear período en borrador' }}</button>
            </fieldset>
            @include('edd.configuracion.estado-guardado')
        </form>
        @if($periodo)
            <p class="small edd-muted mt-3 mb-0">Último guardado: {{ \Carbon\CarbonImmutable::parse($periodo['updated_at'], 'UTC')->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}.</p>
        @endif
    </section>
    @include('edd.partials.flujo')
@endsection
