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
            <select id="selectorModulo" class="form-select w-auto">
                <option value="">Seleccionar módulo</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <table id="tbTransact" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>FECHA</th>
                    <th>USUARIO</th>
                    <th>MÓDULO</th>
                    <th>ACCIÓN</th>
                    <th>DETALLE</th>
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