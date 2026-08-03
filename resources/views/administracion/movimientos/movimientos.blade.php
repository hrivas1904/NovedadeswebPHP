@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista">MOVIMIENTOS</h3>
    <div class="d-flex gap-3">
        <select class="form-select" id="selectCuentas">
            <option value="">Cuentas</option>
            @foreach($cuentas as $c)
            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
            @endforeach
        </select>
        <select class="form-select" id="selectEstados">
            <option value="">Estados</option>
            <option value="Ejecutado">Ejecutado</option>
            <option value="Presupuesto">Presupuesto</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Cumplido">Cumplido</option>
        </select>
        <select class="form-select" id="selectOperaciones">
            <option value="">Operaciones</option>
            <option value="Ingreso">Ingreso</option>
            <option value="Transferencias">Transferencias</option>
            <option value="Cheque">Cheque</option>
            <option value="Efectivo">Efectivo</option>
        </select>
        <select class="form-select" id="selectConceptos">
            <option value="">Conceptos</option>
            @foreach($conceptos as $c)
            <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
        <input type="text" class="form-control" id="inputSubconcepto" placeholder="Sub-concepto...">
        <button type="button" class="btn btn-sm btn-primary" id="btnAbrirManual" data-bs-toggle="modal" data-bs-target="#modalMovimientoManual">
            Manual
        </button>
    </div>

    <div class="row d-flex my-2">
        <div class="col-2 d-flex gap-3 align-items-end">
            <label class="form-label h6 fw-bold" style="color: var(--color-default);">Desde: </label>
            <input type="date" class="form-control" id="inputFechaDesde">
        </div>
        <div class="col-2 d-flex gap-3 align-items-end">
            <label class="form-label h6 fw-bold" style="color: var(--color-default);">Hasta: </label>
            <input type="date" class="form-control" id="inputFechaHasta">
        </div>
        <div class="col-7 d-flex">
            <input class="form-control w-100" id="inputBuscador" placeholder="Buscar...">
        </div>
        <div class="col-1 d-flex">
            <button type="button" class="btn btn-sm btn-secondary w-100" id="btnLimpiarFiltros">
                Limpiar
            </button>
        </div>
    </div>

    <div class="card p-1">
        <table id="tablaMovimientos" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox">
                    </th>
                    <th>FECHA</th>
                    <th>ESTADO</th>
                    <th>OPERACIÓN</th>
                    <th>CUENTA</th>
                    <th>CONCEPTO</th>
                    <th>SUB-CONCEPTO</th>
                    <th>DETALLE</th>
                    <th>IMPORTE</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalMovimientoManual" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo movimiento manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">Fecha</label>
                        <input type="date" class="form-control" id="manualFecha">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Estado</label>
                        <select class="form-select" id="manualEstado">
                            <option value="EJECUTADO">Ejecutado</option>
                            <option value="PRESUPUESTO" selected>Presupuesto</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="CUMPLIDO">Cumplido</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Cuenta</label>
                        <select class="form-select" id="manualCuenta">
                            @foreach($cuentas as $c)
                            <option value="{{ $c->nombre }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Sección</label>
                        <select class="form-select" id="manualSeccion">
                            <option value="2 INGRESOS">2 INGRESOS</option>
                            <option value="3 EGRESOS" selected>3 EGRESOS</option>
                            <option value="4 TARJETAS D/C">4 TARJETAS D/C</option>
                            <option value="5 TRANSFERENCIAS">5 TRANSFERENCIAS</option>
                            <option value="6 SEÑAS">6 SEÑAS</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Concepto</label>
                        <select class="form-select" id="manualConcepto"></select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Sub-concepto</label>
                        <input type="text" class="form-control" id="manualSubconcepto">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Importe</label>
                        <input type="text" class="form-control" id="manualImporte">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Detalle</label>
                        <input type="text" class="form-control" id="manualDetalle">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarManual" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    const MOVIMIENTOS_ROUTES = {
        data: @json(route('administracion.movimientosData')),
        manual: @json(route('administracion.movimientosGuardarManual')),
        estado: @json(route('administracion.movimientos.estado', ':id')),
        fecha: @json(route('administracion.movimientos.fecha', ':id')),
        duplicar: @json(route('administracion.movimientos.duplicar', ':id')),
        eliminar: @json(route('administracion.movimientos.eliminar', ':id')),
    };
    const CONCEPTOS_CATALOGO = @json($conceptos);
</script>

<script src="{{ asset('js/administracion/movimientos/movimientos.js') }}"></script>
@endpush