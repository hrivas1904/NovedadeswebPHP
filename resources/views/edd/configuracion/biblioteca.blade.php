@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-planificacion')
    @if($planificacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">{{ $esGenerales ? 'Competencias generales · comunes a todo el personal' : 'Biblioteca de competencias específicas' }}</h3>
            <p class="edd-muted">{{ $esGenerales ? 'Esta es la única lista de generales para el período. Se suma a las específicas de cada colaborador. Cada competencia se evalúa del 1 al 4.' : 'Guardá listas por área, función o persona y reutilizalas al configurar las competencias. No es necesario vincular un descriptivo de puesto.' }}</p>
            @if(!$esGenerales)
                <form method="get" class="row g-2 align-items-end mb-4">
                    <input type="hidden" name="periodo" value="{{ $periodo['id'] }}">
                    <div class="col-md-8"><label for="edd-lista-guardada" class="form-label">Lista del período</label>
                        <select class="form-select" id="edd-lista-guardada" name="lista">
                            <option value="">Crear nueva lista</option>
                            @foreach($bibliotecas as $opcion)<option value="{{ $opcion['id'] }}" @selected($lista['id'] == $opcion['id'])>{{ $opcion['nombre'] }} · #{{ $opcion['id'] }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-auto"><button class="btn btn-outline-secondary">Ver lista</button></div>
                </form>
                <p><a href="{{ route('rrhh.edd.configuracion.competencias-masivas', ['periodo' => $periodo['id']]) }}">Aplicar una lista a colaboradores</a> · <a href="{{ route('rrhh.edd.configuracion.competencias', ['periodo' => $periodo['id']]) }}">Configurar la base de un área</a></p>
            @endif
            @if($esGenerales && !$lista['id'])<p class="alert alert-info">Todavía no hay generales guardadas. Podés usar la lista Generales HP3C como punto de partida, revisarla y guardarla.</p>@endif
            <form method="post" action="{{ route($esGenerales ? 'rrhh.edd.generales.update' : 'rrhh.edd.biblioteca.store', $periodo['id']) }}" data-edd-form>
                @csrf
                <input type="hidden" name="revision" value="{{ $lista['revision'] }}">
                @if(!$esGenerales && $lista['id'])<input type="hidden" name="lista_id" value="{{ $lista['id'] }}">@endif
                <fieldset @disabled($periodo['estado'] !== 'borrador')>
                    <legend class="visually-hidden">Editar lista de competencias</legend>
                    @if(!$esGenerales)
                        <label class="form-label" for="edd-nombre-lista">Nombre de la lista *</label>
                        <input class="form-control mb-3" id="edd-nombre-lista" name="nombre" value="{{ $lista['nombre'] }}" maxlength="160" placeholder="Por ejemplo: Administración · pagos" required>
                    @endif
                    @include('edd.configuracion.selector-biblioteca')
                    <label class="form-label" for="edd-competencias">Competencias *</label>
                    <textarea class="form-control" id="edd-competencias" name="competencias" rows="12" maxlength="30000" required>{{ $textoCompetencias }}</textarea>
                    <p class="small edd-muted mt-2">Una competencia por línea. Descripción opcional después de dos puntos (:). La calificación es siempre de 1 a 4.</p>
                    <button class="btn btn-primary" data-edd-guardar disabled>{{ $esGenerales ? 'Guardar generales para todo el período' : 'Guardar lista en la biblioteca' }}</button>
                </fieldset>
                @include('edd.configuracion.estado-guardado')
            </form>
        </section>
    @endif
@endsection
