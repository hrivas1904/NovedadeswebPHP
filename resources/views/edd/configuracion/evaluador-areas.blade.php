@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-planificacion')
    @if($planificacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">Evaluadores por área</h3>
            <p class="edd-muted">Podés registrar varios evaluadores en la misma área y repartirles distintos colaboradores. Estar registrado en un área no da acceso a todas sus evaluaciones: cada responsable ve solamente sus asignaciones.</p>
            <p><a href="{{ route('rrhh.edd.configuracion.evaluadores', ['periodo' => $periodo['id']]) }}">Asignar colaboradores a cada evaluador</a></p>
            <form method="post" action="{{ route('rrhh.edd.evaluador-areas.store', $periodo['id']) }}" data-edd-form data-edd-inscripcion>
                @csrf
                <fieldset @disabled($periodo['estado'] !== 'borrador')>
                    <legend class="visually-hidden">Registrar evaluador en un área</legend>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="edd-evaluador-area" class="form-label">Evaluador *</label>
                            <select class="form-select" id="edd-evaluador-area" name="evaluador_user_id" required>
                                <option value="">Seleccioná una cuenta activa</option>
                                @foreach($evaluadores as $evaluador)<option value="{{ $evaluador->id }}">{{ $evaluador->name }} · {{ $evaluador->area_nombre ?? 'Sin área en la cuenta' }} · #{{ $evaluador->id }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label for="edd-area-evaluador" class="form-label">Área EDD *</label>
                            <select class="form-select" id="edd-area-evaluador" name="area_id" required>
                                <option value="">Seleccioná un área</option>
                                @foreach($areas as $area)<option value="{{ $area->ID_AREA }}">{{ $area->NOMBRE }} · #{{ $area->ID_AREA }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" data-edd-guardar disabled>Registrar en el área</button>
                </fieldset>
                @include('edd.configuracion.estado-guardado')
            </form>
            <div class="table-responsive mt-4">
                <table class="table align-middle">
                    <thead><tr><th>Área EDD</th><th>Evaluador</th><th>Acción</th></tr></thead>
                    <tbody>
                    @forelse($registros as $registro)
                        <tr><td>{{ $registro->area_nombre }} · #{{ $registro->area_id }}</td>
                            <td>{{ $registro->name }} @if($registro->estado !== 'ACTIVO')<span class="text-danger">(cuenta inactiva)</span>@endif</td>
                            <td>
                                <form method="post" action="{{ route('rrhh.edd.evaluador-areas.destroy', [$periodo['id'], $registro->id]) }}" data-edd-form data-edd-quitar-inscripcion>
                                    @csrf @method('DELETE')
                                    <fieldset @disabled($periodo['estado'] !== 'borrador')>
                                        <button class="btn btn-outline-secondary btn-sm" data-edd-guardar disabled>Quitar del área</button>
                                    </fieldset>
                                    @include('edd.configuracion.estado-guardado')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Todavía no hay evaluadores registrados. También se registran al asignarles colaboradores del área.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
