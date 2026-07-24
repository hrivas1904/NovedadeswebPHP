@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">POSICIÓN</h3>
    <div class="d-flex my-2 gap-3 align-items-end">
        <label class="fw-bolder h5" style="color: var(--color-default); font-size: 1.1rem;">Posición Disponible</label>
        <input class="form-control flex-grow-0" type="date" style="width: 180px;">
        <button type="button" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-cloud-download"></i> Hoy
        </button>
        <label class="h6 text-muted" id="lblDiaDetallado">Lunes 06 De Julio de 2026</label>
    </div>

    <label class="fw-bolder text-muted mb-2" style="font-size:0.8rem;">CUENTAS BANCARIAS</label>

    <div class="row d-flex">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;" id="cardHeaderMacro">MACRO</label>
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
                        <label class="h6 text-muted" id="cardHeaderNacion" style="font-size:0.8rem;">NACIÓN</label>
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
                        <label class="h6 text-muted" style="font-size:0.8rem;">FRANCÉS (986)</label>
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
                        <label class="h6 text-muted" style="font-size:0.8rem;">FRANCÉS (1001)</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyFrances1">$0,00</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <label class="fw-bolder text-muted mt-4 mb-2" style="font-size:0.8rem;">FONDOS COMUNES DE INVERSIÓN - PESOS</label>

    <div class="row d-flex">
        <div class="col-12 col-md-6">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;" id="">FCI PESOS</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyFciPesos">$0,00</label>
                    </div>
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">Macro / transferencias</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">FCI FARMACIA TRES CERRITOS</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyFciF3c">$0,00</label>
                    </div>
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">Portofolio - gasto deducible</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <label class="fw-bolder text-muted mt-4 mb-2" style="font-size:0.8rem;">POSICIÓN EN DOLARES Y EFECTIVO</label>

    <div class="row d-flex">
        <div class="col-12 col-md-6">
            <div class="card" style="color:var(--color-default);">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">TOTAL USD (FCI + EFECTIVO)</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyTotalUsdFci">$0,00</label>
                    </div>
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">Carga mensual</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card icon-kpi" style="color: var(--color-default)">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;">EFECTIVO EN PESOS (CAJA)</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyEfectivoCaja">$0,00</label>
                    </div>
                    <div>
                        <label class="h6 text-muted" style="font-size:0.8rem;"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-flex mt-5">
        <div class="col-12 col-md-6">
            <div class="card icon-kpi" style="background-color:var(--color-default); color:white;">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6" style="font-size:0.8rem;">TOTAL DISPONIBLE EN PESOS</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyTotalPesos">$0,00</label>
                    </div>
                    <div>
                        <label class="h6" style="font-size:0.8rem;">Bancos + FCI + Caja</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card icon-kpi" style="background-color:var(--color-default); color:white;">
                <div class="card-body flex-column">
                    <div>
                        <label class="h6" style="font-size:0.8rem;">POSICIÓN EN DOLARES</label>
                    </div>
                    <div>
                        <label class="h3 fw-bolder" id="cardBodyTotalUsd">USD 0</label>
                    </div>
                    <div>
                        <label class="h6" style="font-size:0.8rem;">FCI USD + Efectivo USD</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const POSICION_ROUTES = {data: @json(route('administracion.posicion.data'))};
</script>
<script src="{{ asset('/js/administracion/dashboard/posicion.js') }}"></script>
@endpush