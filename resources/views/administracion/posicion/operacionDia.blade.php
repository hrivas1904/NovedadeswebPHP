@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">HOY</h3>

    <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">PRESUPUESTO HOY - TOTALIZADOR</label>

    <div class="row d-flex">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;" id="cardHeaderMacro">INGRESOS
                            PRESUPUESTADOS HOY</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyMacro">$0,00</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" id="cardHeaderNacion" style="font-size:0.8rem;">GASTOS
                            PRESUPUESTADOS HOY</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyNacion">$0,00</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">NETO PRESUPUESTADO HOY</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyFrances9">$0,00</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">SALDO BANCOS (4 CUENTAS)</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyFrances1">$0,00</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mx-1 my-4 p-2 justify-content-between align-items-center"
        style="
                --color-second-rgb: 0, 137, 199;
                background-color: rgba(var(--color-second-rgb), 0.15);
                border: 2px solid var(--color-default);
                border-radius: 10px;
                color: var(--color-default);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            ">
        <div class="col-auto p-2 d-flex flex-column justify-content-start">
            <label class="fw-bolder mb-1" id="lblNecesidadTitulo" style="font-size:0.8rem;">POSICIÓN EQUILIBRADA</label>
            <label class="fw-bolder text-muted" style="font-size:0.8rem;">Ingresos hoy - Gastos hoy + Saldos Bancos</label>
        </div>
        <div class="col-auto justify-content-end">
            <label class="fw-bolder h2 mb-2" id="lblNecesidadMonto">$0,00</label>
        </div>
    </div>

    <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">PRESUPUESTADO HOY</label>

    <div class="card">
        <div class="card-header" style="background-color: var(--color-second); color: white">
            <div class="row d-flex justify-content-between">
                <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;">Ingresos presupuestados hoy</label></div>
                <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;" id="totalIngPresHoy">$0,00</label></div>
            </div>
        </div>
        <div class="card-body" id="bodyIngPresHoy">
            <label class="text-muted">Sin registros.</label>
        </div>
    </div>

    <div class="card my-4">
        <div class="card-header" style="background-color: var(--color-default); color: white">
            <div class="row d-flex justify-content-between">
                <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;">Egresos presupuestados hoy</label></div>
                <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;" id="totalEgrPresHoy">$0,00</label></div>
            </div>
        </div>
        <div class="card-body" id="bodyEgrPresHoy">
            <label class="text-muted">Sin registros.</label>
        </div>
    </div>

    <div id="seccionEjecutadoHoy" style="display:none;">
        <label class="fw-bolder text-muted mt-4 mb-2" style="font-size:0.8rem;">EJECUTADO DEL DÍA</label>
        <div class="card">
            <div class="card-header" style="background-color: var(--color-second); color: white">
                <div class="row d-flex justify-content-between">
                    <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;">Ingresos ejecutados</label></div>
                    <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;" id="totalIngExecHoy">$0,00</label></div>
                </div>
            </div>
            <div class="card-body" id="bodyIngExecHoy">
                <label class="text-muted">Sin registros.</label>
            </div>
        </div>

        <div class="card my-4">
            <div class="card-header" style="background-color: var(--color-default); color: white">
                <div class="row d-flex justify-content-between">
                    <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;">Egresos ejecutados</label></div>
                    <div class="col-auto"><label class="fw-bolder" style="font-size:0.8rem;" id="totalEgrExecHoy">$0,00</label></div>
                </div>
            </div>
            <div class="card-body" id="bodyEgrExecHoy">
                <label class="text-muted">Sin registros.</label>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const HOY_ROUTES = { data: @json(route('administracion.hoy.dataHoy')) };
</script>
<script src="{{ asset('js/administracion/dashboard/hoy.js') }}"></script>
@endpush