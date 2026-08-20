@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">ANÁLISIS</h3>
    <div class="d-flex gap-3">
        <button type="button" id="btnIrFlujoFondos" class="btn btn-sm btn-analisis active" style="color: var(--color-default);"
            data-url="{{ route('administracion.flujoFondosView') }}">
            <i class="fa-solid fa-money-bill-transfer"></i>
            Flujo de Fondos
        </button>

        <button type="button" id="btnIrComparativo6Meses" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.comparativaView') }}">
            <i class="fa-solid fa-chart-line"></i>
            Comparativo 6 meses
        </button>

        <button type="button" id="btnIrComparativoPresupuesto" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.comparativaPresupuestoView') }}">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            Comparativo Presupuesto
        </button>

        <button type="button" id="btnIrPresupuestoEjecutado" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.presupuestadoView') }}">
            <i class="fa-solid fa-scale-balanced"></i>
            Presup vs. Ejecutado
        </button>

        <button type="button" id="btnIrResumenAnual" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.resumenAnualView') }}">
            <i class="fa-regular fa-calendar-days"></i>
            Resumen anual
        </button>
    </div>
    <hr style="color: var(--color-default); border: 1px solid;" />
    <div class="renderBodyAnalisis card">

    </div>
</div>
@endsection

@push('scripts')
<script>
    const FLUJO_FONDOS_ROUTES = {view: @json(route('administracion.flujoFondosView'))};
</script>
<script src="{{ asset('js/administracion/analisis/flujoFondos.js') }}"></script>

<script>
    const PRESUP_EJECUTADO_ROUTES = {view: @json(route('administracion.presupuestadoView'))};
</script>
<script src="{{ asset('js/administracion/analisis/presupEjecutado.js') }}"></script>

<script>
    const RESUMEN_ANUAL_ROUTES = {view: @json(route('administracion.resumenAnualView'))};
</script>
<script src="{{ asset('js/administracion/analisis/resumenAnual.js') }}"></script>
    
<script src="/js/administracion/analisis/homeAnalisis.js"></script>
@endpush