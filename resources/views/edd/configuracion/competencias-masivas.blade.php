@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-poblacion')
    @if($poblacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">Competencias específicas para un grupo de colaboradores</h3>
            <p class="edd-muted">Filtrá por área y marcá una o varias personas de esta página. Podés copiar una lista y ajustarla antes de aplicarla. Las competencias generales se mantienen iguales para todos.</p>
            @include('edd.configuracion.filtro-area')
            <form method="post" action="{{ route('rrhh.edd.competencias.aplicar', $periodo['id']) }}" data-edd-form>
                @csrf
                <fieldset @disabled($periodo['estado'] !== 'borrador')>
                    <legend class="visually-hidden">Aplicar competencias a seleccionados</legend>
                    <label class="form-label" for="edd-modo-competencias">Lista que usarán los seleccionados</label>
                    <select class="form-select mb-3" id="edd-modo-competencias" name="modo_competencias">
                        <option value="personal">Copiar esta lista a cada persona</option>
                        <option value="area">Volver a la base del área de cada persona</option>
                    </select>
                    @include('edd.configuracion.selector-biblioteca')
                    <label class="form-label" for="edd-competencias">Competencias específicas *</label>
                    <textarea class="form-control" id="edd-competencias" name="competencias" rows="8" maxlength="30000" required></textarea>
                    <p class="small edd-muted mt-2">Una por línea; descripción opcional después de dos puntos (:). Al aplicar, se reemplaza la lista específica de cada persona seleccionada. Luego podés editar cada una por separado.</p>
                    <button class="btn btn-outline-secondary btn-sm mb-2" type="button" data-edd-seleccionar>Seleccionar / desmarcar esta página</button>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Aplicar</th><th>Colaborador</th><th>Área EDD</th><th>Rol / servicio actual</th><th>Lista actual</th></tr></thead>
                            <tbody>
                            @forelse($participantes as $persona)
                                <tr>
                                    <td><input class="form-check-input" type="checkbox" name="participantes[{{ $persona['id'] }}]" value="{{ $persona['revision'] }}" aria-label="Aplicar a {{ $persona['contexto']['nombre'] }}"></td>
                                    <td>{{ $persona['contexto']['nombre'] }} · {{ $persona['legajo'] }}</td><td>{{ $persona['area_nombre'] }}</td>
                                    <td>@include('edd.configuracion.rol-servicio')</td>
                                    <td>{{ $persona['competencias_personales'] === null ? 'Base del área' : 'Personalizada' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">Primero agregá colaboradores en Población a evaluar.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" data-edd-guardar disabled>Aplicar a seleccionados</button>
                </fieldset>
                @include('edd.configuracion.estado-guardado')
            </form>
            <div class="mt-3">{{ $participantes->links('pagination::bootstrap-5') }}</div>
        </section>
    @endif
@endsection
