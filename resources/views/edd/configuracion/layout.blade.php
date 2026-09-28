@extends('edd.layout')

@section('edd-content')
    <div class="edd-panel-header mt-4">
        <h2 class="h5">Configuración RRHH</h2>
        @if($almacenamientoDisponible)
            <a class="btn btn-outline-primary btn-sm" href="{{ route('rrhh.edd.configuracion', ['nuevo' => 1]) }}">Nuevo período</a>
        @endif
    </div>
    <p class="edd-muted">Definición del período, las personas que participan y el instrumento que se utilizará.</p>
    @if(session('edd_guardado'))<p class="alert alert-success" role="status">{{ session('edd_guardado') }}</p>@endif
    @if(!$almacenamientoDisponible)
        <p class="alert alert-warning">El almacenamiento de EDD todavía no está instalado. La administración del sistema debe completar su instalación para habilitar el guardado.</p>
    @elseif($periodos->isNotEmpty())
        <form method="get" action="{{ route(request()->routeIs('rrhh.edd.configuracion.participante') ? 'rrhh.edd.configuracion.poblacion' : request()->route()->getName()) }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-8 col-xl-6">
                <label class="form-label" for="edd-periodo-actual">Período de trabajo</label>
                <select id="edd-periodo-actual" class="form-select" name="periodo">
                    @if(!$periodo)<option value="" selected disabled>Seleccioná un período guardado</option>@endif
                    @foreach($periodos as $opcion)<option value="{{ $opcion['id'] }}" @selected(($periodo['id'] ?? null) === $opcion['id'])>{{ $opcion['nombre'] }} · {{ $opcion['codigo'] }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary" type="submit">Ver período</button></div>
        </form>
    @endif
    <nav class="edd-subnav" aria-label="Configuración de EDD">
        @foreach(['configuracion' => 'Período y reglas', 'configuracion.poblacion' => 'Población a evaluar', 'configuracion.evaluadores' => 'Evaluadores', 'configuracion.competencias' => 'Competencias por área', 'configuracion.instrumento' => 'Instrumento'] as $ruta => $etiqueta)
            <a href="{{ route('rrhh.edd.'.$ruta, ['periodo' => $periodo['id'] ?? null]) }}" @if(request()->routeIs('rrhh.edd.'.$ruta) || ($ruta === 'configuracion.poblacion' && request()->routeIs('rrhh.edd.configuracion.participante'))) aria-current="page" @endif>{{ $etiqueta }}</a>
        @endforeach
    </nav>
    @yield('edd-configuracion')
@endsection

@push('scripts')
    <script src="{{ asset('js/edd/configuracion.js') }}" defer></script>
    <script src="{{ asset('js/edd/poblacion.js') }}" defer></script>
@endpush
