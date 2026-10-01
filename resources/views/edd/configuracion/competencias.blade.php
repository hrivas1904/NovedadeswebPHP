@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-poblacion')
    @if($poblacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">Competencias específicas / funcionales por área</h3>
            <p class="edd-muted">Definí la base del área para este período. La usarán quienes tengan seleccionada la opción Base del área; las listas personalizadas no cambian.</p>
            <form method="get" class="row g-2 align-items-end mb-4">
                <input type="hidden" name="periodo" value="{{ $periodo['id'] }}">
                <div class="col-md-8">
                    <label class="form-label" for="edd-area-base">Área del sistema</label>
                    <select class="form-select" id="edd-area-base" name="area" required>
                        @foreach($areas as $area)<option value="{{ $area->ID_AREA }}" @selected($areaSeleccionada == $area->ID_AREA)>{{ $area->NOMBRE }} · #{{ $area->ID_AREA }}</option>@endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-secondary">Ver competencias del área</button></div>
            </form>
            @if($areaSeleccionada)
                <form method="post" action="{{ route('rrhh.edd.competencias.update', ['periodo' => $periodo['id'], 'area' => $areaSeleccionada]) }}" data-edd-form>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="revision" value="{{ $base['revision'] }}">
                    <fieldset @disabled($periodo['estado'] !== 'borrador')>
                        <legend class="visually-hidden">Editar competencias del área</legend>
                        @include('edd.configuracion.selector-biblioteca')
                        <label class="form-label" for="edd-competencias">Competencias del área *</label>
                        <textarea class="form-control" id="edd-competencias" name="competencias" rows="12" maxlength="30000" required aria-describedby="edd-ayuda-competencias">{{ $textoCompetencias }}</textarea>
                        <p class="small edd-muted mt-2" id="edd-ayuda-competencias">Una competencia por línea. Podés agregar una descripción después de dos puntos (:). Se conserva el texto completo, aunque visualmente ocupe varios renglones.</p>
                        <button class="btn btn-primary" type="submit" data-edd-guardar disabled>Guardar competencias del área</button>
                    </fieldset>
                    @include('edd.configuracion.estado-guardado')
                </form>
            @else
                <p class="edd-muted">No hay áreas disponibles en el sistema.</p>
            @endif
        </section>
    @endif
@endsection
