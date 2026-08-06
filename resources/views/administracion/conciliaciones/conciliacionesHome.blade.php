@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">CONCILIACIONES</h3>
    <div class="d-flex gap-3">
        <button type="button" id="btnIrFlujoFondos" class="btn btn-sm btn-analisis active" style="color: var(--color-default);"
            data-url="{{ route('administracion.conciliacionesMacroView') }}">
            <i class="fa-solid fa-building-columns"></i>
            MACRO
        </button>

        <button type="button" id="btnIrComparativo6Meses" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.conciliacionesNacionView') }}">
            <i class="fa-solid fa-building-columns"></i>
            NACIÓN
        </button>

        <button type="button" id="btnIrPresupuestoEjecutado" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.conciliacionesFrances986View') }}">
            <i class="fa-solid fa-building-columns"></i>
            FRANCÉS (986)
        </button>

        <button type="button" id="btnIrResumenAnual" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.conciliacionesFrances1001View') }}">
            <i class="fa-solid fa-building-columns"></i>
            FRANCÉS (1001)
        </button>
    </div>
    <hr style="color: var(--color-default); border: 1px solid;" />
    <div class="renderBodyAnalisis">

    </div>
</div>
@endsection

@push('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const CONCILIACION_ROUTES = {
        data: @json(route('administracion.conciliacion.data')),
        estado: @json(route('administracion.conciliacion.estado', ':id')),
        estadoMasivo: @json(route('administracion.conciliacion.estadoMasivo')),
        comprobante: @json(route('administracion.conciliacion.comprobante', ':id')),
        extractoPreview: @json(route('administracion.importacion.bancos.preview')),
        extractoConfirmar: @json(route('administracion.importacion.bancos.confirmar')),
        pagosPreview: @json(route('administracion.conciliacion.pagos.preview')),
        pagosConfirmar: @json(route('administracion.conciliacion.pagos.confirmar')),
    };
    const CONCEPTOS_CATALOGO = @json($conceptos);
    const SUBCONCEPTOS_POR_CONCEPTO = @json($subconceptosPorConcepto);
</script>

<script src="/js/administracion/conciliaciones/conciliacionesHome.js"></script>
<script src="{{ asset('js/administracion/conciliaciones/conciliacionMacro.js') }}"></script>
<script src="{{ asset('js/administracion/conciliaciones/conciliacionNacion.js') }}"></script>
<script src="{{ asset('js/administracion/conciliaciones/conciliacionFrances986.js') }}"></script>
<script src="{{ asset('js/administracion/conciliaciones/conciliacionFrances1001.js') }}"></script>
@endpush