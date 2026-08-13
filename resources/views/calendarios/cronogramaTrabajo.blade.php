@extends('layouts.app')

@section('title', 'Calendario de Trabajo')

@section('content')
<div id="divAcciones" class="d-flex flex-column gap-3">
    <div class="d-flex gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group w-auto">
                <button class="btn btn-secondary" type="button" id="button-addon1">
                    <i class="fs-5 fa-regular fa-square-caret-left"></i>
                </button>
                <input type="text" class="form-control" placeholder="" readonly>
                <button class="btn btn-secondary" type="button" id="button-addon1">
                    <i class="fs-5 fa-regular fa-square-caret-right"></i>
                </button>
            </div>
            <select id="selectorArea" class="form-select w-auto">
                <option value="">Seleccione área</option>
            </select>
            <select id="selectorSubarea" class="form-select w-auto">
                <option value="">Seleccione sub-área</option>
            </select>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" id="btnPublicarCronograma" class="btn btn-secondary">Publicar cronograma</button>
            <button type="button" id="btnEnviarNovedades" class="btn btn-primary">Enviar novedades</button>
            <button type="button" id="btnVerAcuseRecibo" class="btn btn-secondary">Acuse de recibo</button>        
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-outline-secondary" id="btnVerConfiguracion" data-bs-toggle="modal" data-bs-target="#">Configuraciones</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Copiar mes anterior</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Pincel</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Deshacer</button>
            <div class="input-group w-auto">
                <input type="text" class="form-control" placeholder="Buscar colaborador..." id="inputBuscador">
                <button class="btn btn-secondary" type="button" id="button-addon1">
                    <i class="fs-5 fa-solid fa-square-xmark"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary" id="btnFiltrarDía">Día</button>
            <button type="button" class="btn btn-outline-secondary" id="btnFiltrarEquidad">Equidad</button>
            <button type="button" class="btn btn-outline-secondary" id="btnCierreMes">Cierre del mes</button>
            <button type="button" class="btn btn-outline-secondary" id="btnExportar" data-bs-toggle="modal" data-bs-target="#">Exportar</button>
        </div>
        <div class="d-flex gap-2 align-items-center">

        </div>
    </div>
</div>

<h3 class="tituloVista my-3">CRONOGRAMA DE TRABAJO</h3>

<div class="d-flex gap-3 mb-3">
    <button type="button" class="btn btn-outline-secondary" id="btnOcultarAcciones">
        <i class="fa-solid fa-angles-up"></i> Ocultar acciones
    </button>
    <button type="button" class="btn btn-outline-secondary" id="btnOcultarIndicadores">
        <i class="fa-solid fa-angles-up"></i> Ocultar indicadores
    </button>
</div>

<div class="row g-2 mb-3">
    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-solid fa-border-all"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">PUESTOS DEFINIDOS</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        5 ocupados · 7 vacantes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-calendar"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">TURNOS ASIGNADOS</h6>
                    <h2 class="fw-bold mb-1">-</h2>

                    <div class="small">
                        en Nursey · 217 sin asignar
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-moon"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">DESCANSOS SEMANALES</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        código DES en el mes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-star"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">FUNCIONES ASIGNADAS</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        días con tarea adicional
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">DÍAS BAJO DOTACIÓN MÍNIMA</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        ver el detalle
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-clock"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">PERSONAS SIN DESCANSO SEMANAL</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        ver el detalle y proponer días
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-circle-question"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">SIN DEFINIR</h6>
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        días sin concepto asignado
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="col">
        <div class="card p-3 h-100">
            <div class="d-flex align-items-start gap-3">

                <div class="icon-mini-kpi flex-shrink-0">
                    <i class="fs-4 fa-regular fa-copy"></i>
                </div>

                <div class="flex-grow-1">
                    <h6 class="mb-1">DOBLE ASIGNACIÓN</h6>

                    <h2 class="fw-bold mb-1">
                        0
                    </h2>

                    <div class="small">
                        ver el detalle
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('modal')

@endpush

@push('script')

@endpush