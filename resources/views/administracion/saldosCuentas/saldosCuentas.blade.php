@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">SALDOS POR CUENTA</h3>

    <div class="d-flex align-items-center gap-2 mb-3">
        <label class="fw-bold" style="color:var(--color-default);">Mes:</label>
        <input type="month" id="mesSaldosCuenta" class="form-control" style="width:180px;" value="{{ $mes }}">
    </div>

    <div class="card p-2">
        <table class="table table-bordered table-hover align-middle nowrap" id="tablaSaldosCuentaPesos">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Saldo inicial</th>                    
                    <th>Movimientos ejecutados</th>                    
                    <th>Saldo actual</th>                    
                </tr>
            </thead>
            <tbody id="bodySaldosCuentaPesos"></tbody>
            <tfoot>
                <tr>
                    <th>Total pesos</th>
                    <th class="text-end" id="totalPesosInicial"></th>
                    <th class="text-end" id="totalPesosMov"></th>
                    <th class="text-end" id="totalPesosActual"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="card p-2 mt-2">
        <table class="table table-bordered table-hover align-middle nowrap" id="tablaSaldosCuentaUsd">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Saldo mensual</th>                    
                    <th>Movimientos ejecutados</th>                    
                    <th>Total USD</th>                    
                </tr>
            </thead>
            <tbody id="bodySaldosCuentaUsd"></tbody>
            <tfoot>
                <tr>
                    <th>Total USD</th>
                    <th class="text-end" id="totalUsdInicial"></th>
                    <th class="text-end" id="totalUsdMov"></th>
                    <th class="text-end" id="totalUsdActual"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const SALDOS_CUENTA_ROUTES = {
        data: @json(route('administracion.saldosCuenta.data')),
        guardar: @json(route('administracion.saldosCuenta.guardar')),
        eliminar: @json(route('administracion.saldosCuenta.eliminar')),
    };
</script>
<script src="{{ asset('js/administracion/saldosCuentas/saldosCuentas.js') }}"></script>
@endpush