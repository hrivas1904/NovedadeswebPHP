@extends('layouts.app')

@section('title', 'Configuraciones Recursos Humanos')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>Inicio
            </a>
        </li>
        <li class="breadcrumb-item">Recursos Humanos</li>
        <li class="breadcrumb-item">Configuraciones</li>
        <li class="breadcrumb-item active" aria-current="page">Parámetros</li>
    </ol>
</nav>

<h3 class="tituloVista mb-3">Configuraciones Recursos Humanos</h3>

<div class="d-flex justify-content-start align-items-center gap-3">
    <button type="button" class="btn btn-sm btn-analisis btnParametro active" style="color: var(--color-default);" data-url="{{ route('rrhh.configAreasView') }}" data-vista="areasServicios">Áreas y Servicios</button>
    <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configCategoriasView') }}" data-vista="categorias">Categorías, Regimenes y Contratos</button>
    <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configNovedades') }}" data-vista="novedades">Catálogo novedades</button>
    <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.obraSocial.administrarObraSociales') }}" data-vista="obrasSociales">Obras Sociales</button>
    <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configPuestosView') }}" data-vista="puestosTrabajo">Puestos de Trabajo</button>
</div>
<hr style="color: var(--color-default); border: 1px solid;" />
<div class="renderDivParametros">
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/personal/selectores.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionAreas.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionServ.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionTurnos.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionFuncAdic.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionCateg.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionRegimenes.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionNovedades.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionContratos.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionPuestos.js') }}"></script>
<script src="{{ asset('js/ajustes/obraSociales.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionHome.js') }}"></script>
@endpush