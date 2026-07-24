@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">IMPORTACIÓN</h3>
    <div class="d-flex gap-3">
        <button type="button" id="btnIrFlujoFondos" class="btn btn-sm btn-analisis active" style="color: var(--color-default);"
            data-url="{{ route('administracion.importMovBancariosView') }}">
            <i class="fa-solid fa-building-columns"></i>
            Bancos
        </button>

        <button type="button" id="btnIrComparativo6Meses" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.importMovCajaView') }}">
            <i class="fa-solid fa-cash-register"></i>
            Caja
        </button>

        <button type="button" id="btnIrPresupuestoEjecutado" class="btn btn-sm btn-analisis" style="color: var(--color-default);"
            data-url="{{ route('administracion.importTsvView') }}">
            <i class="fa-solid fa-file-circle-plus"></i>
            TSV
        </button>
    </div>
    <hr style="color: var(--color-default); border: 1px solid;" />
    <div class="renderBodyImportacion card">

    </div>
</div>
@endsection

@push('scripts')
<script src="/js/administracion/importacion/importacion.js"></script>
<script src="/js/administracion/importacion/importacionBancos.js"></script>
@endpush