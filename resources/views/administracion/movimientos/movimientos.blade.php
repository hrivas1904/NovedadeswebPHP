@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
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
        <option value="Ingresos">Ingresos</option>
        <option value="Transferencias">Transferencias</option>
        <option value="Cheques">Cheques</option>
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

<div class="row d-flex mt-2 mb-4">
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

<div class="d-flex mb-2 align-items-center gap-3">
    <button type="button" class="btn btn-primary" id="btnMarcarCumplido" title="Marcar cumplido">
        <i class="fs-5 fa-regular fa-square-check"></i>
    </button>

    <button type="button" class="btn btn-secondary" id="btnDuplicar" title="Duplicar">
        <i class="fs-5 fa-solid fa-copy"></i>
    </button>

    <button type="button" class="btn btn-danger" id="btnEliminar" title="Eliminar">
        <i class="fs-5 fa-regular fa-trash-can"></i>
    </button>

    <button type="button" class="btn btn-secondary " id="btnVolverPresupuesto" title="Volver a presupuesto">
        <i class="fs-5 fa-solid fa-rotate-left"></i>
    </button>

    <input class="form-control w-auto" type="date" id="inputCambioFechaMasiva">
    <button type="button" class="btn btn-primary" id="btnCambiarFechaMasiva">Cambiar fecha</button>

    <label class="fw-bold fs-6" id="labelSeleccionadosMovimientos">0 SELECCIONADOS</label>
</div>

<div class="card p-1">
    <table id="tablaMovimientos" class="table table-striped table-hover align-middle table-header-hp3c nowrap">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="checkSeleccionarTodo">
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
        estadoMasivo: @json(route('administracion.movimientos.estadoMasivo')),
        duplicarMasivo: @json(route('administracion.movimientos.duplicarMasivo')),
        eliminarMasivo: @json(route('administracion.movimientos.eliminarMasivo')),
        estado: @json(route('administracion.movimientos.estado', ':id')),
        fecha: @json(route('administracion.movimientos.fecha', ':id')),
        duplicar: @json(route('administracion.movimientos.duplicar', ':id')),
        eliminar: @json(route('administracion.movimientos.eliminar', ':id')),
        cuenta: @json(route('administracion.movimientos.cuenta', ':id')),
        concepto: @json(route('administracion.movimientos.concepto', ':id')),
        texto: @json(route('administracion.movimientos.texto', ':id')),
        importe: @json(route('administracion.movimientos.importe', ':id')),
        fechaMasiva: @json(route('administracion.movimientos.fechaMasiva')),
        operacion: "{{ route('administracion.movimientos.operacion', ':id') }}",
        extractoPreview: @json(route('administracion.conciliacion.extracto.preview')),
        extractoConfirmar: @json(route('administracion.conciliacion.extracto.confirmar')),
    };

    const CUENTAS_CATALOGO = @json($cuentas - > pluck('nombre'));
    const CONCEPTOS_CATALOGO = @json($conceptos);
    const SUBCONCEPTOS_POR_CONCEPTO = @json($subconceptosPorConcepto);
    const OPERACIONES_CATALOGO = ['INGRESOS', 'TRANSFERENCIAS', 'CHEQUES', 'EFECTIVO'];
</script>

<script src="{{ asset('js/administracion/movimientos/movimientos.js') }}"></script>
@endpush