@extends('layouts.app')

@section('title', 'Log de Transacciones')

@section('content')
<h3 class="tituloVista mb-2">LOG DE TRANSACCIONES</h3>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-3">
            <label for="inputFechaDesde" class="form-label">Desde</label>
            <input class="form-control w-auto" id="inputFechaDesde" type="date">
            <label for="inputFechaHasta" class="form-label">Hasta</label>
            <input class="form-control w-auto" id="inputFechaHasta" type="date">
            <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">Limpiar filtros</button>
            <button type="button" class="btn btn-primary" id="btnExportarLog">Exportar Log</button>
        </div>
    </div>
    <div class="card-body">
        <table id="tbTransacciones" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">FECHA</th>
                    <th class="text-start">ACCIÓN</th>
                    <th class="text-start">USUARIO</th>                    
                    <th class="text-start">MÓDULO</th>
                    <th class="text-start">TABLA AFECTADA</th>
                    <th class="text-start">REGISTRO AFECTADO</th>                    
                    <th class="text-start">DESCRIPCIÓN</th>                    
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/log/logTransac.js') }}"></script>
@endpush