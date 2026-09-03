@extends('layouts.app')

@section('title', 'Configuraciones Recursos Humanos')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">CONFIGURACIONES RECURSOS HUMANOS</h3>

    <div class="d-flex justify-content-start align-items-center gap-3">
        <button type="button" class="btn btn-sm btn-analisis btnParametro active" style="color: var(--color-default);" data-url="{{ route('rrhh.configAreasView') }}" data-vista="areasServicios">Áreas y Servicios</button>
        <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configCategoriasView') }}" data-vista="categorias">Categorías, Regimenes y Contratos</button>
    </div>
    <hr style="color: var(--color-default); border: 1px solid;" />
    <div class="renderDivParametros">
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/ajustes/parametrizacionAreas.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionServ.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionTurnos.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionFuncAdic.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionCateg.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionRegimenes.js') }}"></script>
<script src="{{ asset('js/ajustes/parametrizacionHome.js') }}"></script>
@endpush