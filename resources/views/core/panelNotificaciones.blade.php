@extends('layouts.app')

@section('title', 'Panel de Notificaciones')

@section('content')
<h3 class="tituloVista mb-2">NOTIFICACIONES</h3>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-3">
            <label for="inputFechaDesde" class="form-label">Desde</label>
            <input class="form-control w-auto" id="inputFechaDesde" type="date">
            <label for="inputFechaHasta" class="form-label">Hasta</label>
            <input class="form-control w-auto" id="inputFechaHasta" type="date">
            <select class="form-select w-auto" id="selectorLeidas">
                <option value="">TODAS</option>
                <option value="1">LEÍDAS</option>
                <option value="0">NO LEÍDAS</option>
            </select>
            <button type="button" class="btn btn-primary" id="btnMarcarLeidas">Marcar como leída</button>
        </div>
    </div>
    <div class="card-body">
        <table id="tbTodasAlertas" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>FECHA</th>
                    <th>MÓDULO</th>                    
                    <th>MENSAJE</th>
                    <th>REFERENCIA</th>
                    <th>URL</th>
                    <th>
                        <input type="checkbox" id="seleccionarTodasAlertas">
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/home/alertasView.js') }}"></script>
@endpush