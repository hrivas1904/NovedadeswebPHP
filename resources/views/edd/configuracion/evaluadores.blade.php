@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-poblacion')
    @if($poblacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">Asignar evaluador</h3>
            <p><a href="{{ route('rrhh.edd.configuracion.evaluador-areas', ['periodo' => $periodo['id']]) }}">Configurar evaluadores por área</a></p>
            <p class="edd-muted">Marcá colaboradores incluidos en la población y elegí su responsable. La selección corresponde a esta página. Si ya tenían evaluador, se conserva el historial de la asignación anterior.</p>
            <p class="small edd-muted">Varios evaluadores pueden compartir un área. Repartí los colaboradores entre ellos: cada persona tendrá un responsable. Al asignar, ese evaluador también queda registrado en las áreas EDD de los seleccionados.</p>
            @include('edd.configuracion.filtro-area')
            <form method="post" action="{{ route('rrhh.edd.evaluadores.update', $periodo['id']) }}" data-edd-form>
                @csrf
                <fieldset @disabled($periodo['estado'] !== 'borrador')>
                    <legend class="visually-hidden">Asignación a colaboradores</legend>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label" for="edd-evaluador">Evaluador responsable *</label>
                            <select class="form-select" id="edd-evaluador" name="evaluador_user_id" required>
                                <option value="">Seleccioná una cuenta activa</option>
                                @foreach($evaluadores as $evaluador)<option value="{{ $evaluador->id }}">{{ $evaluador->name }} · {{ $evaluador->area_nombre ?? 'Sin área en la cuenta' }} · #{{ $evaluador->id }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label" for="edd-funcion">Función *</label><select class="form-select" id="edd-funcion" name="funcion">@foreach(['coordinador' => 'Coordinador/a', 'jefe' => 'Jefe/a', 'responsable' => 'Responsable', 'gerente' => 'Gerente'] as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select></div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm mb-2" type="button" data-edd-seleccionar>Seleccionar / desmarcar esta página</button>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Seleccionar</th><th>Legajo</th><th>Colaborador</th><th>Área</th><th>Evaluador actual</th><th></th></tr></thead>
                            <tbody>
                            @forelse($participantes as $persona)
                                <tr>
                                    <td><input class="form-check-input" type="checkbox" name="participantes[{{ $persona['id'] }}]" value="{{ $persona['revision'] }}" aria-label="Asignar a {{ $persona['contexto']['nombre'] }}"></td>
                                    <td>{{ $persona['legajo'] }}</td><td>{{ $persona['contexto']['nombre'] }}</td><td>{{ $persona['area_nombre'] }}</td>
                                    <td>{{ $persona['evaluador_nombre'] ?? 'Sin asignar' }}@if($persona['evaluador_user_id'] && $persona['evaluador_estado'] !== 'ACTIVO') <span class="text-danger">(cuenta inactiva)</span>@endif</td>
                                    <td><a href="{{ route('rrhh.edd.configuracion.participante', ['periodo' => $periodo['id'], 'participante' => $persona['id']]) }}">Configurar</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Primero agregá colaboradores en Población a evaluar.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" data-edd-guardar disabled type="submit">Asignar a seleccionados</button>
                </fieldset>
                @include('edd.configuracion.estado-guardado')
            </form>
            <div class="mt-3">{{ $participantes->links('pagination::bootstrap-5') }}</div>
        </section>
    @endif
@endsection
