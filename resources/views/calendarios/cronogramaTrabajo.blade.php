@extends('layouts.app')

@section('title', 'Calendario de Trabajo')

@section('content')
<div id="divAcciones" class="d-flex flex-column gap-3">
    <div class="d-flex gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group w-auto">
                <button class="btn btn-secondary" type="button" id="btnMesAnterior"">
                    <i class="fs-5 fa-regular fa-square-caret-left"></i>
                </button>
                <select class="form-select" id="selectorMesCrono"></select>
                <button class="btn btn-secondary" type="button" id="btnMesSiguiente">
                    <i class="fs-5 fa-regular fa-square-caret-right"></i>
                </button>
            </div>
            <select id="selectorArea" class="form-select w-auto"></select>
            <select id="selectorServicio" class="form-select w-auto" disabled>
                <option value="">Seleccione servicio</option>
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
            <button type="button" class="btn btn-outline-secondary" id="btnVerConfiguracion" data-bs-toggle="modal" data-bs-target="#modalConfiguraciones">Configuraciones</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Copiar mes anterior</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Pincel</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Deshacer</button>
            <div class="input-group w-auto">
                <input type="text" class="form-control" placeholder="Buscar colaborador..." id="inputBuscador">
                <button class="btn btn-secondary" type="button" id="button-addon1">
                    <i class="fs-5 fa-solid fa-square-xmark"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary" id="btnFiltrarDía" data-bs-toggle="modal" data-bs-target="#modalDia">Día</button>
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
        <i id="iconUpAcciones" class="fa-solid fa-angles-up"></i>
        <span id="textoAcciones">Ocultar acciones</span>
    </button>
    <button type="button" class="btn btn-outline-secondary" id="btnOcultarIndicadores">
        <i id="iconUpIndicadores" class="fa-solid fa-angles-up"></i>
        <span id="textoIndicadores">Ocultar indicadores</span>
    </button>
</div>

<div id="divIndicadores" class="row g-2 mb-3">
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
                    <h2 class="fw-bold mb-1">-</h2>
                    <div class="small">
                        ver el detalle
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalConfiguraciones" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex flex-column gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                        <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">CONFIGURACIONES</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap px-3 py-2 bg-white border-top" style="border-radius: 10px;">
                        <button id="btnVistaAreas" class="btn btn-outline-secondary active">Áreas y subáreas</button>
                        <button id="btnVistaCatalogo" class="btn btn-outline-secondary">Catálogo de novedades</button>
                        <button id="btnVistaFeriados" class="btn btn-outline-secondary">Feriados</button>
                        <button id="btnVistaPeriodos" class="btn btn-outline-secondary">Períodos</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">
                    TABLERO DEL <span id="detalleFechaLarga">...</span>
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group w-auto">
                    <button class="btn btn-secondary" type="button" id="button-addon1">
                        <i class="fs-5 fa-regular fa-square-caret-left"></i>
                    </button>
                    <input class="form-control" id="inputDia" type="number">
                    <button class="btn btn-secondary" type="button" id="button-addon1">
                        <i class="fs-5 fa-regular fa-square-caret-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/calendario/cronogramaTrabajo.js') }}"></script>

<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
</script>
@endpush