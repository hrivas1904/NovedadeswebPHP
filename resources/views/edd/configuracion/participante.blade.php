@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-poblacion')
    <section class="edd-panel">
        <div class="edd-panel-header">
            <div><h3 class="h6">{{ $participante['contexto']['nombre'] }}</h3><p class="edd-muted mb-0">Legajo {{ $participante['legajo'] }}</p></div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('rrhh.edd.configuracion.poblacion', ['periodo' => $periodo['id']]) }}">Volver a población</a>
        </div>
        @include('edd.configuracion.cuenta-participante', ['persona' => $participante])
        <p class="small edd-muted mt-2">Esta configuración pertenece únicamente a {{ $periodo['nombre'] }}. No modifica el legajo ni requiere un descriptivo de puesto.</p>
        <form method="post" action="{{ route('rrhh.edd.participantes.update', ['periodo' => $periodo['id'], 'participante' => $participante['id']]) }}" data-edd-form>
            @csrf
            @method('PATCH')
            <input type="hidden" name="revision" value="{{ $participante['revision'] }}">
            <fieldset @disabled($periodo['estado'] !== 'borrador')>
                <legend class="visually-hidden">Configuración individual</legend>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="edd-area-persona">Área en este período *</label>
                        <select class="form-select" id="edd-area-persona" name="area_id" required>
                            @foreach($areas as $area)<option value="{{ $area->ID_AREA }}" @selected($participante['area_id'] == $area->ID_AREA)>{{ $area->NOMBRE }} · #{{ $area->ID_AREA }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="edd-incluido">Participación *</label>
                        <select class="form-select" id="edd-incluido" name="incluido"><option value="1" @selected($participante['incluido'])>Incluido/a</option><option value="0" @selected(!$participante['incluido'])>Excluido/a</option></select>
                    </div>
                    <div class="col-12"><label class="form-label" for="edd-motivo-exclusion">Motivo de exclusión (obligatorio si se excluye)</label><input class="form-control" id="edd-motivo-exclusion" name="motivo_exclusion" maxlength="2000" value="{{ $participante['motivo_exclusion'] }}"></div>
                    <div class="col-md-8">
                        <label class="form-label" for="edd-evaluador">Evaluador responsable</label>
                        <select class="form-select" id="edd-evaluador" name="evaluador_user_id">
                            <option value="">Sin asignar</option>
                            @if($participante['evaluador_user_id'] && $participante['evaluador_estado'] !== 'ACTIVO')<option value="{{ $participante['evaluador_user_id'] }}" selected>{{ $participante['evaluador_nombre'] }} (cuenta inactiva: elegí otra)</option>@endif
                            @foreach($evaluadores as $evaluador)<option value="{{ $evaluador->id }}" @selected($participante['evaluador_user_id'] == $evaluador->id)>{{ $evaluador->name }} · {{ $evaluador->rol }} · #{{ $evaluador->id }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="edd-funcion">Función</label>
                        <select class="form-select" id="edd-funcion" name="funcion">@foreach(['coordinador' => 'Coordinador/a', 'jefe' => 'Jefe/a', 'responsable' => 'Responsable', 'gerente' => 'Gerente'] as $valor => $etiqueta)<option value="{{ $valor }}" @selected(($participante['funcion'] ?? 'coordinador') === $valor)>{{ $etiqueta }}</option>@endforeach</select>
                    </div>
                    <div class="col-12"><p class="small edd-muted mb-0">Excluir finaliza la asignación vigente y conserva su historial. Al reincluir, elegí nuevamente el responsable.</p></div>
                    <div class="col-12">
                        <label class="form-label" for="edd-modo-competencias">Competencias específicas / funcionales</label>
                        <select class="form-select" id="edd-modo-competencias" name="modo_competencias">
                            <option value="area" @selected($participante['competencias_personales'] === null)>Usar la base del área</option>
                            <option value="personal" @selected($participante['competencias_personales'] !== null)>Personalizar para este colaborador</option>
                        </select>
                        <p class="small edd-muted mt-2">La base sigue los cambios del área mientras el período está en borrador. Personalizar permite quitar, cambiar o agregar competencias únicamente para esta persona.</p>
                        <p class="alert alert-info" data-edd-area-cambiada hidden>Guardá el cambio de área para consultar su base actualizada. Si elegís personalizar, se guardará el texto que ves debajo.</p>
                        <label class="form-label" for="edd-competencias">Lista de competencias</label>
                        <textarea class="form-control" id="edd-competencias" name="competencias" rows="12" maxlength="30000" @disabled($participante['competencias_personales'] === null)>{{ $textoCompetencias }}</textarea>
                        @if(!$textoCompetencias)<p class="text-warning-emphasis mt-2">Todavía no hay competencias definidas. Podés configurar la base del área o personalizar esta lista.</p>@endif
                        <p class="small edd-muted mt-2">Una competencia por línea; descripción opcional después de dos puntos (:). Volver a Base del área reemplaza la personalización al guardar; el cambio queda en auditoría.</p>
                    </div>
                </div>
                <button class="btn btn-primary mt-3" data-edd-guardar disabled type="submit">Guardar configuración del colaborador</button>
            </fieldset>
            @include('edd.configuracion.estado-guardado')
        </form>
    </section>
@endsection
