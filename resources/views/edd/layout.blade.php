@extends('layouts.app')

@section('title', 'Evaluación de Desempeño')

@section('content')
    @include('edd.partials.estilos')
    <div class="edd-shell" data-edd>
        <header class="edd-header">
            <div>
                <p class="edd-eyebrow mb-1">{{ $institucion }}</p>
                <h1 class="h3 mb-2">Evaluación de desempeño</h1>
                <p class="mb-0 edd-muted">{{ $periodoReferencia }} <span class="mx-2" aria-hidden="true">·</span> Escala de 1 a {{ max(array_keys($escala)) }}</p>
            </div>
            <span class="badge edd-design-badge">Diseño inicial</span>
        </header>

        <nav class="edd-navigation" aria-label="Evaluación de desempeño">
            @can('edd.administrar')
                <a href="{{ route('rrhh.edd.resumen') }}" @if(request()->routeIs('rrhh.edd.resumen')) aria-current="page" @endif>Resumen RRHH</a>
                <a href="{{ route('rrhh.edd.configuracion') }}" @if(request()->routeIs('rrhh.edd.configuracion*')) aria-current="page" @endif>Configuración</a>
            @endcan
            @can('edd.evaluar')
                <a href="{{ route('rrhh.edd.equipo') }}" @if(request()->routeIs('rrhh.edd.equipo', 'rrhh.edd.evaluacion.*')) aria-current="page" @endif>Mi equipo</a>
            @endcan
            <a href="{{ route('rrhh.edd.autoevaluacion') }}" @if(request()->routeIs('rrhh.edd.autoevaluacion')) aria-current="page" @endif>Mi autoevaluación</a>
            @can('edd.administrar')
                <a href="{{ route('rrhh.edd.reportes') }}" @if(request()->routeIs('rrhh.edd.reportes')) aria-current="page" @endif>Reportes e historial</a>
            @endcan
        </nav>

        <p class="edd-notice" id="edd-disponibilidad">
            <i class="fa-solid fa-circle-info me-2" aria-hidden="true"></i>
            Estructura inicial del módulo. Todavía no hay un período habilitado para cargar evaluaciones.
        </p>

        @yield('edd-content')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/edd/edd.js') }}" defer></script>
@endpush
