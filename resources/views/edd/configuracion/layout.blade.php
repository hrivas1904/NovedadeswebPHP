@extends('edd.layout')

@section('edd-content')
    <h2 class="h5 mt-4">Configuración RRHH</h2>
    <p class="edd-muted">Definición del período, las personas que participan y el instrumento que se utilizará.</p>
    <nav class="edd-subnav" aria-label="Configuración de EDD">
        @foreach(['configuracion' => 'Período y reglas', 'configuracion.poblacion' => 'Población a evaluar', 'configuracion.evaluadores' => 'Evaluadores', 'configuracion.instrumento' => 'Instrumento'] as $ruta => $etiqueta)
            <a href="{{ route('rrhh.edd.'.$ruta) }}" @if(request()->routeIs('rrhh.edd.'.$ruta)) aria-current="page" @endif>{{ $etiqueta }}</a>
        @endforeach
    </nav>
    @yield('edd-configuracion')
@endsection
