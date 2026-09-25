@extends('edd.layout')

@section('edd-content')
    <section class="edd-panel mt-4" data-edd-equipo>
        <div class="edd-panel-header">
            <div><h2 class="h5">Mi equipo</h2><p class="edd-muted mb-0 mt-2">Evaluaciones asignadas a tu cuenta</p></div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('rrhh.edd.evaluacion.modelo') }}">Ver estructura del formulario</a>
        </div>
        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-5"><label for="edd-buscar" class="form-label">Buscar en mi equipo</label><input type="search" class="form-control" id="edd-buscar" data-edd-buscar placeholder="Nombre o legajo" @disabled($evaluaciones->isEmpty())></div>
            <div class="col-md-4">
                <label for="edd-estado" class="form-label">Estado</label>
                <select class="form-select" id="edd-estado" data-edd-estado @disabled($evaluaciones->isEmpty())>
                    <option value="">Todos los estados</option>
                    @foreach($estados as $estado)<option value="{{ $estado->value }}">{{ $estado->etiqueta() }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-outline-secondary w-100" type="button" data-edd-limpiar @disabled($evaluaciones->isEmpty())>Limpiar filtros</button></div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th scope="col">Colaborador</th><th scope="col">Puesto / servicio</th><th scope="col">Estado</th><th scope="col">Última actualización</th></tr></thead>
                <tbody>
                    @forelse($evaluaciones as $evaluacion)
                        <tr data-edd-fila data-estado="{{ $evaluacion['estado']->value }}" data-busqueda="{{ $evaluacion['colaborador'].' '.$evaluacion['legajo'] }}">
                            <td>{{ $evaluacion['colaborador'] }}<small class="d-block edd-muted">Legajo {{ $evaluacion['legajo'] }}</small></td>
                            <td>{{ $evaluacion['puesto'] }}<small class="d-block edd-muted">{{ $evaluacion['servicio'] }}</small></td>
                            <td><span class="badge {{ $evaluacion['estado']->clase() }}">{{ $evaluacion['estado']->etiqueta() }}</span></td>
                            <td>{{ $evaluacion['actualizada'] ?? 'Sin iniciar' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="edd-empty"><strong>Todavía no hay evaluaciones asignadas.</strong><p>Cuando RRHH habilite el período y asigne tu equipo, verás acá a cada colaborador y el estado de su evaluación.</p></div></td></tr>
                    @endforelse
                    <tr data-edd-sin-resultados hidden><td colspan="4" class="text-center edd-muted">No hay coincidencias con esos filtros.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="small edd-muted mt-3 mb-0" data-edd-conteo role="status" aria-live="polite"></p>
        <p class="small edd-muted mt-3 mb-0">Avance del equipo: sin evaluaciones asignadas. Se calculará con las cerradas sobre el total previsto.</p>
    </section>
    @include('edd.partials.flujo')
@endsection
