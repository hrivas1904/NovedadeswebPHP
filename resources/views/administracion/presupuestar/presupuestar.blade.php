@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">PRESUPUESTAR</h3>

    <div class="card p-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">1. COBRANZAS DESDE GECLISA (IVA
            VENTAS)</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">Copiá el IVA Ventas de Geclisa y pegalo
            acá.</label>
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá el contenido del IVA Ventas de Geclisa..." rows="5"></textarea>
    </div>

    <div class="card p-3 my-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">2. PAGOS A PROVEEDORES DESDE
            FINNEGANS (CUENTAS A PAGAR)</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">Pegá el reporte de cuentas a pagar.</label>
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá el contenido de Cuentas a Pagar de Finnegans..." rows="5"></textarea>
    </div>

    <div class="card p-3">
        <label class="fw-bolder" style="font-size:1rem; color:var(--color-default);">3. LÍNEAS RECURRENTES</label>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <div class="row m-1 p-2 g-3 align-items-end"
            style="
                    --color-second-rgb: 0, 137, 199;
                    background-color: rgba(var(--color-second-rgb), 0.15);
                    border: 2px solid var(--color-default);
                    border-radius: 10px;
                    color: var(--color-default);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                ">
            <div class="col-xl-2 col-md-4 col-12">
                <select class="form-select">
                    <option value="" selected>Seleccione opción...</option>
                </select>
            </div>

            <div class="col-xl-1 col-md-4 col-12">
                <select class="form-select">
                    <option value="" selected>Seleccione opción...</option>
                </select>
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <select class="form-select">
                    <option value="" selected>Seleccione opción...</option>
                </select>
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" placeholder="Sub-concepto">
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" placeholder="Descripción">
            </div>

            <div class="col-xl-2 col-md-4 col-12">
                <input type="text" class="form-control" placeholder="Importe">
            </div>

            <div class="col-xl-1 col-md-4 col-12">
                <button type="button" class="btn btn-secondary btn-sm w-100">+ Agregar</button>
            </div>
        </div>
        <label class="text-muted mt-3">Sin líneas recurrentes cargadas.</label>
    </div>
</div>
@endsection