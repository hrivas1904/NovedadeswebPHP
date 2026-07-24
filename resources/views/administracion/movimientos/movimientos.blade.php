@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">MOVIMIENTOS</h3>
    <div class="d-flex gap-3">
        <select class="form-select" id="selectCuentas">
            <option value="">Cuentas</option>
        </select>
        <select class="form-select" id="selectEstados">
            <option value="">Estados</option>
        </select>
        <select class="form-select" id="selectOperaciones">
            <option value="">Operaciones</option>
        </select>
        <button type="button" class="btn btn-sm btn-secondary">
            <i class="fa-regular fa-square-plus"></i>
            Manual
        </button>
    </div>

    <div class="row d-flex my-3">
        <div class="col-2 d-flex gap-3 align-items-end">
            <label class="form-label h6 fw-bold" style="color: var(--color-default);">Desde: </label>
            <input type="date" class="form-control" id="inputFechaDesde">
        </div>
        <div class="col-2 d-flex gap-3 align-items-end">
            <label class="form-label h6 fw-bold" style="color: var(--color-default);">Hasta: </label>
            <input type="date" class="form-control" id="inputFechaHasta">
        </div>
        <div class="col-8 d-flex">
            <input class="form-control w-100" id="inputBuscador" placeholder="Buscar...">
        </div>
    </div>

    <div class="card p-2">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox">
                    </th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Operación</th>
                    <th>Cuenta</th>
                    <th>Concepto</th>
                    <th>Sub-concepto</th>
                    <th>Detalle</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection