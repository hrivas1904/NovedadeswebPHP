@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @include('edd.configuracion.disponibilidad-poblacion')
    @if($poblacionDisponible && $periodo)
        <section class="edd-panel">
            <h3 class="h6">Agregar colaboradores al período</h3>
            <p class="edd-muted">Buscá en la nómina activa actual, marcá las personas que querés evaluar y agregalas. La selección corresponde a esta página. La fecha de corte no reconstruye una nómina histórica.</p>
            @include('edd.configuracion.filtro-area', ['buscar' => true])
            <form method="post" action="{{ route('rrhh.edd.poblacion.store', $periodo['id']) }}" data-edd-form>
                @csrf
                <fieldset @disabled($periodo['estado'] !== 'borrador')>
                    <legend class="visually-hidden">Colaboradores disponibles</legend>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Agregar</th><th>Legajo</th><th>Colaborador</th><th>Área</th></tr></thead>
                            <tbody>
                            @forelse($candidatos as $persona)
                                <tr><td><input class="form-check-input" type="checkbox" name="legajos[]" value="{{ $persona->LEGAJO }}" aria-label="Agregar {{ $persona->COLABORADOR }}"></td><td>{{ $persona->LEGAJO }}</td><td>{{ $persona->COLABORADOR }}</td><td>{{ $persona->area_nombre ?? 'Sin área' }}</td></tr>
                            @empty
                                <tr><td colspan="4">No hay colaboradores disponibles con estos filtros.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" data-edd-guardar disabled type="submit">Agregar seleccionados</button>
                </fieldset>
                @include('edd.configuracion.estado-guardado')
            </form>
            <div class="mt-3">{{ $candidatos->links('pagination::bootstrap-5') }}</div>
        </section>
        <section class="edd-panel mt-4">
            <div class="edd-panel-header"><h3 class="h6">Población guardada · {{ $participantes->total() }} registros con este filtro de área</h3><a class="btn btn-outline-primary btn-sm" href="{{ route('rrhh.edd.configuracion.evaluadores', ['periodo' => $periodo['id']]) }}">Asignar evaluadores</a></div>
            <p class="edd-muted">En Configurar podés elegir el evaluador, personalizar las competencias o excluir a una persona conservando su registro.</p>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Legajo</th><th>Colaborador</th><th>Área EDD</th><th>Cuenta para autoevaluación</th><th>Participación</th><th>Competencias</th><th></th></tr></thead>
                    <tbody>
                    @forelse($participantes as $persona)
                        <tr>
                            <td>{{ $persona['legajo'] }}</td><td>{{ $persona['contexto']['nombre'] }}</td><td>{{ $persona['area_nombre'] }}</td>
                            <td>@include('edd.configuracion.cuenta-participante')</td>
                            <td>{{ $persona['incluido'] ? 'Incluida' : 'Excluida' }}</td>
                            <td>{{ $persona['competencias_personales'] === null ? 'Base del área' : 'Personalizadas' }}</td>
                            <td><a href="{{ route('rrhh.edd.configuracion.participante', ['periodo' => $periodo['id'], 'participante' => $persona['id']]) }}" aria-label="Configurar {{ $persona['contexto']['nombre'] }}">Configurar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Todavía no hay participantes con este filtro. Agregalos desde la nómina de arriba.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $participantes->links('pagination::bootstrap-5') }}
            <p class="small edd-muted">El vínculo de la cuenta se comprueba por legajo. Antes de abrir la autoevaluación habrá que resolver las cuentas faltantes o duplicadas.</p>
        </section>
    @endif
@endsection
