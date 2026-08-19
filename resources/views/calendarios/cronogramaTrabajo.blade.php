@extends('layouts.app')

@section('title', 'Calendario de Trabajo')

@section('content')
<div id="divAcciones" class="d-flex flex-column gap-3">
    <div class="d-flex gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group w-auto">
                <button class="btn btn-secondary" type="button" id="btnMesAnterior"">
                    <i class=" fs-5 fa-regular fa-square-caret-left"></i>
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
            <button type="button" class="btn btn-outline-secondary" id="btnVerConfiguracion" data-bs-toggle="modal" data-bs-target="#modalConfiguraciones">Configuración de periodos</button>
            <button type="button" class="btn btn-outline-secondary" id="btnVerFeriados" data-bs-toggle="modal" data-bs-target="#modalFeriados">Feriados</button>
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
                <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">PERÍODOS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-3">
                    <div class="row d-flex">
                        <div class="col-12 col-md-3">
                            <div class="card text-center p-2">
                                <div class="d-flex flex-column">
                                    <label class="tex-muted fw-bold">PERÍODOS ABIERTOS</label>
                                    <h1 id="lblPeriodosAbiertos" class="fw-bold">-</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card text-center p-2">
                                <div class="d-flex flex-column">
                                    <label class="tex-muted fw-bold">VISIBLES EN EL SELECTOR</label>
                                    <h1 id="lblVisiblesSelector" class="fw-bold">-</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card text-center p-2">
                                <div class="d-flex flex-column">
                                    <label class="tex-muted fw-bold">CON CRONOGRAMA CARGADO</label>
                                    <h1 id="lblCronogramaCargado" class="fw-bold">-</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card text-center p-2">
                                <div class="d-flex flex-column">
                                    <label class="tex-muted fw-bold">CON ALGÚN SERVICIO CERRADO</label>
                                    <h1 id="lblServicioCerrado" class="fw-bold">-</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card p-1">
                        <table id="tbPeriodos" class="table table-striped table-hover align-middle table-header-hp3c">
                            <thead>
                                <tr>
                                    <th>PERÍODO</th>
                                    <th>DÍAS</th>
                                    <th>EMPIEZA</th>
                                    <th>ASIGNACIONES</th>
                                    <th>ESTADO</th>
                                    <th>VISIBLE</th>
                                    <th>OBSERVACIONES</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-nowrap justify-content-around mt-3">
                        <label class="form-label text-muted text-nowrap">Abrir un mes: </label>
                        <select class="form-select w-auto" id="selectorMesPeriodo">
                            <option value="">Seleccionar mes</option>
                        </select>
                        <select class="form-select w-auto" id="selectorAnnioPeriodo">
                            <option value="">Seleccionar año</option>
                        </select>
                        <div class="form-check flex-nowrap">
                            <input class="form-check-input" type="checkbox" value="" id="checkDefault">
                            <label class="form-check-label" for="checkDefault">Copiar personas y puestos del mes anterior</label>
                        </div>
                        <button type="button" id="btnAbrirPeriodoSelec" class="btn btn-primary text-nowrap">Abrir periodo</button>
                        <button type="button" id="btnAbrirTodosMeses" class="btn btn-outline-secondary text-nowrap">Abrir los 12 meses del año elegido</button>
                    </div>
                    <div class="border-top">
                        <p>
                            <span class="fw-bold">Abrir un período</span> crea el mes vacío en todas las áreas y servicios, con la cantidad de días y el día de la semana en que empieza. Si tildás la copia, arrastra las personas y los puestos del mes anterior —no los francos, que se rearman con el generador de ciclos o con el pincel—. Es el arranque más rápido y el que menos errores mete: el 90% de la dotación no cambia de un mes al otro.
                        </p>
                        <p>
                            <span class="fw-bold">Visible</span> controla qué ve el coordinador en el selector de la pantalla principal. Sirve para dos cosas concretas: que nadie cargue por error un mes que todavía no se habilitó, y sacar de la vista los meses ya cerrados y liquidados sin borrarlos. <span class="fw-bold">Ocultar no borra nada:</span> el período sigue existiendo, se sigue viendo en los reportes y en la trazabilidad, y se vuelve a mostrar tildando la casilla.
                        </p>
                        <p>
                            Un período con cronograma cargado o publicado no se puede eliminar. Es a propósito: un cronograma publicado es un hecho histórico, se usó para cubrir turnos reales y para liquidar, y borrarlo deja sin respaldo lo que se pagó. Si un mes se abrió por error y todavía está vacío, el botón de eliminar está disponible.
                        </p>
                        <p>
                            Criterio sugerido para el hospital: tener siempre visibles <span class="fw-bold">el mes en curso y los dos siguientes.</span> El mes en curso para resolver excepciones, el siguiente porque es el que se está armando, y el subsiguiente para cargar vacaciones con anticipación. Los cerrados se ocultan después de la liquidación.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFeriados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">
                    FERIADOS
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card p-1">
                    <table id="tbFeriados" class="table table-striped table-hover align-middle table-header-hp3c">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>FECHA</th>
                                <th>DÍA</th>
                                <th>DENOMINACIÓN</th>
                                <th>ALCANCE</th>
                                <th>CARÁCTER</th>
                                <th>VERIFICADO</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
<script src="{{ asset('js/calendario/configuracionPeriodos.js') }}"></script>
<script src="{{ asset('js/calendario/configuracionFeriados.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaTrabajo.js') }}"></script>

<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
</script>
@endpush