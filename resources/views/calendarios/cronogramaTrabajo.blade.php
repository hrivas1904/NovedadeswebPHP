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
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button type="button" class="btn btn-outline-secondary" id="btnVerConfiguracion" data-bs-toggle="modal" data-bs-target="#modalConfiguraciones">Periodos</button>
            <button type="button" class="btn btn-outline-secondary" id="btnVerFeriados" data-bs-toggle="modal" data-bs-target="#modalFeriados">Feriados</button>
            <button type="button" class="btn btn-outline-secondary" id="btnReplicarMesAnt">Copiar mes anterior</button>
            <button type="button" class="btn btn-outline-secondary" id="btnTogglePincel">
                <i class="fa-solid fa-paintbrush"></i> Pincel
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnDeshacer">Deshacer</button>
            <div class="input-group w-auto d-none">
                <input type="text" class="form-control" placeholder="Buscar colaborador..." id="inputBuscador">
                <button class="btn btn-secondary" type="button" id="button-addon1">
                    <i class="fs-5 fa-solid fa-square-xmark"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary" id="btnFiltrarDía" data-bs-toggle="modal" data-bs-target="#modalDia">Día</button>
            <button type="button" class="btn btn-outline-secondary" id="btnFiltrarEquidad" data-bs-toggle="modal" data-bs-target="#modalEquidad">Equidad</button>
            <button type="button" class="btn btn-outline-secondary" id="btnCierreMes">Cierre del mes</button>
            <button type="button" class="btn btn-outline-secondary" id="btnExportar" data-bs-toggle="modal" data-bs-target="#">Exportar</button>
            <button type="button" class="btn btn-outline-secondary" id="btnVerConflictos" data-bs-toggle="modal" data-bs-target="#modalConflictos">Conflictos</button>
        </div>
    </div>
    <div class="row">
        <div id="opcionesPincel" class="d-none d-flex align-items-center gap-2 mt-2 flex-wrap">
            <select id="selNovedadPincel" class="form-select form-select-sm" style="width:auto">
                <option value="">— Sin definir —</option>
            </select>
            <select id="selFuncionPincel" class="form-select form-select-sm" style="width:auto">
                <option value="">— Ninguna —</option>
            </select>
            <span class="badge bg-primary">Pincel activo — arrastrá sobre los días de una fila</span>
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

<div class="d-flex justify-content-end mb-2">
    <button id="btnAgregarPuesto" class="btn btn-sm btn-primary" type="button" disabled>
        <i class="fa-solid fa-plus"></i> Agregar puesto
    </button>
</div>

<div id="contenedorGrilla" class="border rounded p-3">
    <p class="text-muted text-center mb-0">Elegí período y área para ver la grilla.</p>
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
                                    <th>ACCIONES</th>
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
                        <tbody></tbody>
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
                <div class="d-flex flex-column gap-3">
                    <div class="input-group w-auto">
                        <button class="btn btn-secondary" type="button" id="button-addon1">
                            <i class="fs-5 fa-regular fa-square-caret-left"></i>
                        </button>
                        <input class="form-control" id="inputDia" type="number">
                        <button class="btn btn-secondary" type="button" id="button-addon1">
                            <i class="fs-5 fa-regular fa-square-caret-right"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <div class="card p-2 h-100">
                            <label class="form-label text-muted">TURNOS ACTIVOS</label>
                            <h1 id="lblTurnosActivos" class="fw-bold">-</h1>
                        </div>
                        <div class="card p-2 h-100">
                            <label class="form-label text-muted">CUMPLEN LA DOTACIÓN</label>
                            <h1 id="lblDotacion" class="fw-bold">-</h1>
                        </div>
                    </div>
                    <div id="divRenderDotacionDiaria" class="">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEquidad" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">
                    EQUIDAD EN EL REPARTO - <span id="detalleMesFecha">...</span>
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="tbRepartosTurnos" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th>COLABORADOR</th>
                            <th>ÁREAS</th>
                            <th>TURNOS</th>
                            <th>HORAS</th>
                            <th>HS NOCT</th>
                            <th>TURNOS NOCHE</th>
                            <th>FINES DE SEM</th>
                            <th>FERIADOS</th>
                            <th>DESCANSOS</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregarPuesto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar puesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Turno</label>
                    <select id="selTurnoNuevoPuesto" class="form-select form-select-sm"></select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Cantidad de puestos</label>
                    <input type="number" id="inputCantidadNuevoPuesto" class="form-control form-control-sm" min="1" max="50" value="1">
                </div>
                <div class="mb-2">
                    <label class="form-label">Dotación mínima</label>
                    <input type="number" id="inputDotacionNuevoPuesto" class="form-control form-control-sm" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarNuevoPuesto" class="btn btn-sm btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPickerPersona" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="divOcupanteActual" class="mb-3"></div>
                <div class="mb-2">
                    <label class="form-label">Rol</label>
                    <select id="selRolAsignacion" class="form-select form-select-sm">
                        <option value="titular">Titular</option>
                        <option value="apoyo">Apoyo</option>
                    </select>
                </div>
                <input type="text" id="inputBuscarPersona" class="form-control form-control-sm mb-2" placeholder="Buscar por nombre o legajo...">
                <div id="divResultadosBusqueda" style="max-height:300px; overflow-y:auto;"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCeldaNovedad" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="tituloCeldaNovedad">Novedad del día</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small mb-1">Novedad</label>
                    <select id="selNovedadCelda" class="form-select form-select-sm">
                        <option value="">— Sin definir —</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Función adicional</label>
                    <select id="selFuncionCelda" class="form-select form-select-sm">
                        <option value="">— Ninguna —</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarCeldaNovedad" class="btn btn-sm btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConflictos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Conflictos detectados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenedorConflictos" style="max-height:60vh; overflow-y:auto;">
        <p class="text-muted text-center mb-0">Cargando...</p>
      </div>
    </div>
  </div>
</div>

@endpush

@push('scripts')
<script>
    const RUTAS_CRONO_PERIODOS = {
        listar: "{{ route('rrhh.listarCronoPeriodo') }}",
        abrir: "{{ route('rrhh.abrirCronoPeriodo') }}",
        abrirAnio: "{{ route('rrhh.abrirAnnioCronoPeriodo') }}",
        toggleVisible: "{{ route('rrhh.toggleVisible', ':periodo') }}",
        eliminar: "{{ route('rrhh.eliminar', ':periodo') }}"
    };

    const RUTAS_CRONO_FERIADOS = {
        listar: "{{ url('/eventosProgramados/lista') }}",
        eliminar: "{{ url('/eventosProgramados/eliminar') }}",
        actualizarCaracter: "{{ route('rrhh.actualizarCaracterCronoFeriado') }}",
        actualizarVerificado: "{{ route('rrhh.actualizarVerificadoCronoFeriado') }}"
    };

    const RUTAS_CRONO_GRILLA = {
        periodosVisibles: "{{ route('rrhh.listarCronoPeriodosVisibles') }}",
        areas: "{{ route('rrhh.listarCronoAreasActivas') }}",
        serviciosPorArea: "{{ route('rrhh.listarCronoServiciosActivos', ':idArea') }}",
        turnosPorArea: "{{ route('rrhh.listarCronoTurnosActivos', ':idArea') }}",
        grilla: "{{ route('rrhh.listarCronoGrilla') }}",
        crearPuesto: "{{ route('rrhh.crearCronoPuesto') }}",
        ajustarCantidad: "{{ route('rrhh.ajustarCantidadCronoPuesto') }}",
        ajustarDotacion: "{{ route('rrhh.ajustarDotacionCronoPuesto') }}",
        eliminarPuesto: "{{ route('rrhh.eliminarCronoPuesto') }}",
        buscarEmpleados: "{{ route('rrhh.buscarCronoEmpleados') }}",
        asignarSlot: "{{ route('rrhh.asignarCronoSlot') }}",
        quitarSlot: "{{ route('rrhh.quitarCronoSlot') }}",
        novedadesActivas: "{{ route('rrhh.listarCronoNovedadesActivas') }}",
        asignacionesDia: "{{ route('rrhh.listarCronoAsignacionesDia') }}",
        actualizarCelda: "{{ route('rrhh.actualizarCronoCeldaNovedad') }}",
        funcionesActivas: "{{ route('rrhh.listarCronoFuncionesActivas', ':idArea') }}",
        slotFuncionesDia: "{{ route('rrhh.listarCronoSlotFuncionesDia') }}",
        actualizarFuncionDia: "{{ route('rrhh.actualizarCronoSlotFuncionDia') }}",
        pintarRango: "{{ route('rrhh.pintarCronoSlotRango') }}",
        copiarMesAnterior: "{{ route('rrhh.copiarCronoMesAnterior') }}",
        conflictos: "{{ route('rrhh.listarCronoConflictos') }}"
    };
</script>
<script src="{{ asset('js/calendario/cronogramaTrabajo.js') }}"></script>
<script src="{{ asset('js/calendario/configuracionPeriodos.js') }}"></script>
<script src="{{ asset('js/calendario/configuracionFeriados.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaDiario.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaEquidad.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaPicker.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaCelda.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaCopiarMes.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaConflictos.js') }}"></script>
<script src="{{ asset('js/calendario/cronogramaGrilla.js') }}"></script>
<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
</script>
@endpush