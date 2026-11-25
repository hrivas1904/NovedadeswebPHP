@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">

        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>
                Inicio
            </a>
        </li>
        <li class="breadcrumb-item">
            Recursos Humanos
        </li>
        <li class="breadcrumb-item">
            Colaboradores
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Personal
        </li>

    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center">
    <h3 class="tituloVista mb-3">Gestión de colaboradores</h3>
    @if (Auth::user()->rol === 'Administrador/a')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAltaColaborador">
        Nuevo Colaborador
    </button>
    @endif
</div>

<div class="row d-flex justify-content-start align-items-start">
    <div class="col-2 d-none d-xl-block">
        <div class="card h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>

                @if (Auth::user()->rol === 'Administrador/a' || Auth::user()->rol === 'Colaborador/a L2')
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleAreas">
                        <span>Áreas</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaAreas">
                    </div>
                </div>
                @endif
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleCateg">
                        <span>Categorías</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaCateg">
                    </div>
                </div>
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleConvenios">
                        <span>Convenios</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaConvenios">
                    </div>
                </div>
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleEstados">
                        <span>Estados</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body" id="listaEstados">
                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="">
                            TODOS
                        </label>

                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="ACTIVO" checked>
                            ACTIVOS
                        </label>

                        <label class="filtro-item">
                            <input type="radio" name="estado" class="radio-estado" value="DE BAJA">
                            DE BAJA
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-arrows-rotate"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-10 col-xxl-10">
        <div class="card p-2">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group buscador-personal">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" id="searchPersonal" class="form-control" placeholder="Buscar colaborador...">
                        <button class="btn btn-secondary" id="btnClearSearch" type="button">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <button type="button" id="btnExportExcelDep" class="btn btn-outline-primary d-none d-md-inline-flex align-items-center">
                        Exportar
                    </button>
                </div>
                <div class="table-responsive table-card">
                    <table id="tb_personal" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>DNI</th>
                                <th>ÁREA</th>
                                <th>CATEGORÍA</th>
                                <th>CONVENIO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="contenedorConstancia" class="d-none"></div>
@endsection

@push('modals')
<div class="modal fade" id="modalAltaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <form id="formAltaColaborador" class="modal-content p-2">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Alta de personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-xl-6">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">DATOS PERSONALES</label>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" required>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Apellido</label>
                                    <input type="text" class="form-control" name="apellido" required>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">DNI</label>
                                    <input type="text" class="form-control inputDniPersonal" name="dni" required>
                                </div>
                                <div class="col-12 col-xl-9">
                                    <label for="preCuilPersonal" class="text-muted">CUIL</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control text-center preCuilPersonal" maxlength="2" inputmode="numeric" style="max-width: 65px;" required>
                                        <input type="text" class="form-control text-center midCuilPersonal" maxlength="8" readonly required>
                                        <input type="text" class="form-control text-center posCuilPersonal" maxlength="1" inputmode="numeric" style="max-width: 55px;" required>
                                        <input type="hidden" name="cuil" class="inputCuilPersonal">
                                    </div>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="text-muted">Fecha de nacimiento</label>
                                    <input class="form-control" type="date" id="inputFnacimientoPersonal" name="fechaNacimiento" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">DATOS DE CONTACTO</label>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Teléfono</label>
                                    <input type="text" class="form-control inputTelefonoPersonal" name="telefono" required>
                                </div>
                                <div class="col-12 col-xl-8">
                                    <label class="text-muted">Correo electrónico</label>
                                    <input type="email" class="form-control" name="correoElectronico">
                                </div>
                                <div class="col-12 col-xl-8">
                                    <label class="text-muted">Domicilio</label>
                                    <input type="text" class="form-control" name="domicilio" required>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label class="text-muted">Localidad</label>
                                    <select id="selectLocalidad" name="localidad" class="form-select" style="width:100%" required>
                                        <option value="">Seleccionar localidad</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <div class="d-flex justify-content-between aling-items-center">
                                    <label class="label-control text-muted fw-bold">CONTACTOS DE EMERGENCIA</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarContacto">Agregar contacto</button>
                                </div>
                            </div>
                            <div class="row g-3 my-1" id="divContactosEmergencia">

                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <div class="d-flex justify-content-between aling-items-center">
                                    <label class="label-control text-muted fw-bold">GRUPO FAMILIAR</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarHijo">Agregar familiar</button>
                                </div>
                            </div>
                            <div class="row g-3 my-1" id="divFamiliares">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <label class="label-control text-muted fw-bold">DATOS SOCIOECONÓMICOS</label>
                                <div class="col-12 col-xl-3">
                                    <label class="text-muted">Estado civil</label>
                                    <select class="form-select" name="estadoCivil">
                                        <option value="">Seleccione estado civil</option>
                                        <option value="SOLTERO/A">SOLTERO/A</option>
                                        <option value="CASADO/A">CASADO/A</option>
                                        <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                        <option value="VIUDO/A">VIUDO/A</option>
                                        <option value="N/D">N/D</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="text-muted">Género</label>
                                    <select class="form-select" name="genero">
                                        <option value="">Seleccione género</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMENINO">FEMENINO</option>
                                        <option value="NO BINARIO">NO BINARIO</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-1">
                                    <label class="text-muted">Título</label>
                                    <select class="form-select" name="titulo">
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="text-muted">Descipción del título</label>
                                    <input type="text" class="form-control input-titulo-personal" name="descripcionTitulo">
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="text-muted">MP</label>
                                    <input type="text" class="form-control input-mat-profesional" name="matricula">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row g-3">
                                <div class="d-flex justify-content-between gap-3 aling-items-center">
                                    <div class="col-12 col-xl-3">
                                        <label class="label-control text-muted fw-bold">DATOS LABORALES</label>
                                    </div>
                                    <div class="col-12 col-xl-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <select class="form-select" id="selectorTipoVinculo" name="tipoVinculo">
                                                <option value="">Seleccione tipo de vínculo</option>
                                                <option value="DEPENDENCIA">Relación de dependencia</option>
                                                <option value="EXTERNO">Externo</option>
                                            </select>
                                            <input class="form-control" type="date" name="fechaIngreso" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 my-1 d-none" id="divDependencia">
                                <div class="col-12 col-xl-3">
                                    <label class="label-control text-muted">Puesto de trabajo</label>
                                    <select class="form-select" id="selectorPuestoTrabajoPersonal" name="puestoTrabajo"></select>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="label-control text-muted">Categoría</label>
                                    <select class="form-select" id="selectorCategoriaPersonal" name="categoria"></select>
                                </div>
                                <div class="col-12 col-xl-1">
                                    <label class="label-control text-muted">Régimen</label>
                                    <select class="form-select" id="selectorRegimenPersonal" name="regimen"></select>
                                </div>
                                <div class="col-12 col-xl-1">
                                    <label class="label-control text-muted">Horas diarias</label>
                                    <input class="form-control" id="inputHorasDiariasPersonal" name="horasDiarias" readonly>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="label-control text-muted">Tipo de contrato</label>
                                    <select class="form-select" id="selectorTipoContratoDep" name="tipoContratoDep"></select>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="label-control text-muted">Fin período de prueba</label>
                                    <input class="form-control" type="date" name="fechaFinPrueba">
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="label-control text-muted">Obra social</label>
                                    <select class="form-select" id="selectorObrasSociales" name="obraSocial"></select>
                                </div>
                                <div class="col-12 col-xl-1">
                                    <label class="label-control text-muted">Código OS</label>
                                    <input class="form-control" id="inputCodigoObraSocial" readonly>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="label-control text-muted">Convenio</label>
                                    <select class="form-select" name="convenio">
                                        <option value="SANIDAD">SANIDAD</option>
                                        <option value="FUERA DE CONVENIO">FUERA DE CONVENIO</option>
                                    </select>
                                </div>
                                <div class="col-12 col-xl-2">
                                    <label class="label-control text-muted">Afiliado al sindicato</label>
                                    <select class="form-select" name="sindicato">
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 my-1 d-none" id="divExterno">
                                <div class="col-12 col-xl-3">
                                    <label class="label-control text-muted">Tipo de contrato</label>
                                    <select class="form-select" id="selectorTipoContratoExt" name="tipoContratoExt"></select>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <label class="label-control text-muted">Servicio</label>
                                    <select class="form-select" id="selectorServicioExterno" name="servicioExterno"></select>
                                </div>
                                <div class="col-12 col-xl-6">
                                    <label class="label-control text-muted">Razón social</label>
                                    <input class="form-control" name="razonSocial" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnCerraFormAltaPersonal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalVerLegajo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <form id="formLegajo" class="modal-content p-2" autocomplete="off">
            @csrf
            <input type="hidden" name="idPersonal" id="hiddenIdPersonalLegajo">
            <input type="hidden" name="idVinculo" id="hiddenIdVinculoLegajo">
            <input type="hidden" name="idTramo" id="hiddenIdTramoLegajo">
            <input type="hidden" name="tipoVinculo" id="hiddenTipoVinculoLegajo">
            <div id="divEliminadosLegajo"></div>

            {{-- CABECERA --}}
            <div class="modal-header align-items-start">
                <div class="d-flex align-items-center gap-3">
                    <div id="legajoFoto" class="legajo-foto"></div>
                    <div class="d-flex flex-column gap-1">
                        <h5 class="modal-title mb-0" id="legajoTitulo"></h5>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-light text-dark border" id="legajoNumero"></span>
                            <span class="badge" id="legajoEstado"></span>
                            <small class="text-muted" id="legajoResumen"></small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabLegajoPersonales" type="button">Datos personales</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLegajoLaborales" type="button">Datos laborales</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLegajoFamilia" type="button">Contactos y familia</button></li>
                    <li class="nav-item solo-con-legajo"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLegajoNovedades" type="button" id="tabBtnNovedadesLegajo">Novedades</button></li>
                    <li class="nav-item solo-con-legajo"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-prestamos" type="button" id="tab-prestamos-btn">Préstamos y embargos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLegajoHistorial" type="button">Historial</button></li>
                </ul>

                <div class="tab-content">

                    {{-- ============================================================
                         DATOS PERSONALES
                         ============================================================ --}}
                    <div class="tab-pane fade show active" id="tabLegajoPersonales">
                        <div class="row g-3">
                            <div class="col-12 col-xl-6">
                                <div class="card p-3 h-100">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">DATOS PERSONALES</label>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">Apellido</label>
                                            <input type="text" class="form-control" name="apellido" data-campo="apellido" required>
                                        </div>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">Nombre</label>
                                            <input type="text" class="form-control" name="nombre" data-campo="nombre" required>
                                        </div>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">DNI</label>
                                            <input type="text" class="form-control inputDniLegajo" name="dni" data-campo="dni" inputmode="numeric" maxlength="8" required>
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <label class="text-muted">CUIL</label>
                                            <div class="input-group" title="Click en el DNI para copiar el CUIL">
                                                <input type="text" class="form-control text-center preCuilLegajo" maxlength="2" inputmode="numeric" style="max-width: 65px;" required>
                                                <input type="text" class="form-control text-center midCuilLegajo copiar-cuil-legajo" maxlength="8" readonly required style="cursor: pointer;">
                                                <input type="text" class="form-control text-center posCuilLegajo" maxlength="1" inputmode="numeric" style="max-width: 55px;" required>
                                                <input type="hidden" name="cuil" class="inputCuilLegajo">
                                            </div>
                                        </div>
                                        <div class="col-8 col-xl-4">
                                            <label class="text-muted">Fecha de nacimiento</label>
                                            <input type="date" class="form-control" name="fechaNacimiento" data-campo="fecha_nacimiento" required>
                                        </div>
                                        <div class="col-4 col-xl-2">
                                            <label class="text-muted">Edad</label>
                                            <input type="text" class="form-control" id="inputEdadLegajo" readonly tabindex="-1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="card p-3 h-100">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">DATOS DE CONTACTO</label>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">Teléfono</label>
                                            <input type="text" class="form-control" name="telefono" data-campo="telefono" required>
                                        </div>
                                        <div class="col-12 col-xl-8">
                                            <label class="text-muted">Correo electrónico</label>
                                            <input type="email" class="form-control" name="correoElectronico" data-campo="correo">
                                        </div>
                                        <div class="col-12 col-xl-8">
                                            <label class="text-muted">Domicilio</label>
                                            <input type="text" class="form-control" name="domicilio" data-campo="domicilio" required>
                                        </div>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">Localidad</label>
                                            <select class="form-select" id="selectLocalidadLegajo" name="localidad" data-campo="localidad" style="width:100%" required>
                                                <option value="">Seleccionar localidad</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">DATOS SOCIOECONÓMICOS</label>
                                        <div class="col-12 col-xl-3">
                                            <label class="text-muted">Estado civil</label>
                                            <select class="form-select" name="estadoCivil" data-campo="estado_civil">
                                                <option value="">Seleccione estado civil</option>
                                                <option value="SOLTERO/A">SOLTERO/A</option>
                                                <option value="CASADO/A">CASADO/A</option>
                                                <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                                <option value="VIUDO/A">VIUDO/A</option>
                                                <option value="N/D">N/D</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="text-muted">Género</label>
                                            <select class="form-select" name="genero" data-campo="genero">
                                                <option value="">Seleccione género</option>
                                                <option value="MASCULINO">MASCULINO</option>
                                                <option value="FEMENINO">FEMENINO</option>
                                                <option value="NO BINARIO">NO BINARIO</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-xl-1">
                                            <label class="text-muted">Título</label>
                                            <select class="form-select" name="titulo" data-campo="titulo">
                                                <option value="">-</option>
                                                <option value="SI">SI</option>
                                                <option value="NO">NO</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="text-muted">Descripción del título</label>
                                            <input type="text" class="form-control" name="descripcionTitulo" data-campo="descripcion_titulo">
                                        </div>
                                        <div class="col-12 col-xl-2">
                                            <label class="text-muted">MP</label>
                                            <input type="text" class="form-control" name="matricula" data-campo="matricula">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 solo-con-legajo">
                                <div class="card p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="label-control text-muted fw-bold">CUENTAS BANCARIAS</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnMostraDivNuevaCuenta">Agregar cuenta</button>
                                    </div>

                                    <!-- legajo que usa administrarCuentas.js -->
                                    <input type="hidden" id="inputLegajo">

                                    <!-- alta de cuenta -->
                                    <div class="row g-3 align-items-end mb-3 d-none" id="divCrearCuentaBancariaColab">
                                        <div class="col-12 col-xl-3">
                                            <label class="text-muted">Banco</label>
                                            <select class="form-select" id="selectBanco">
                                                <option value="">Seleccione banco</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="text-muted">N° de cuenta</label>
                                            <input type="text" class="form-control" id="inputNumeroCuenta">
                                        </div>
                                        <div class="col-12 col-xl-4">
                                            <label class="text-muted">CBU</label>
                                            <input type="text" class="form-control" id="inputCbu" maxlength="22" inputmode="numeric">
                                        </div>
                                        <div class="col-12 col-xl-2 d-flex gap-2">
                                            <button type="button" class="btn btn-primary w-100" id="btnGuardarCuenta">Guardar</button>
                                            <button type="button" class="btn btn-outline-secondary" id="btnOcultarDivCuentaNueva" title="Cancelar">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- listado -->
                                    <div class="row g-3" id="contenedorCuentasBancarias"></div>
                                </div>
                            </div>

                            <div class="col-12 solo-con-legajo">
                                <div class="card p-3">
                                    <label class="label-control text-muted fw-bold mb-2">REQUERIMIENTOS ALIMENTARIOS</label>

                                    <!-- checkboxes que arma cargarRequerimientosAlimentarios() -->
                                    <div class="row g-3" id="contenedorRequerimientos"></div>

                                    <!-- se muestra al marcar "Otro" -->
                                    <div class="row g-3 mt-1" id="rowObservacionReq" style="display: none;">
                                        <div class="col-12">
                                            <label class="text-muted">Especificar</label>
                                            <input type="text" class="form-control" id="inputReqOtro" name="reqObservacion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                         DATOS LABORALES
                         ============================================================ --}}
                    <div class="tab-pane fade" id="tabLegajoLaborales">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">VÍNCULO</label>
                                        <div class="col-6 col-xl-2">
                                            <label class="text-muted">Tipo de vínculo</label>
                                            {{-- no editable: cambiar el tipo es cerrar un vínculo y abrir otro --}}
                                            <input type="text" class="form-control" data-campo="tipo_vinculo" readonly tabindex="-1">
                                        </div>
                                        <div class="col-6 col-xl-2">
                                            <label class="text-muted">Fecha de ingreso</label>
                                            <input type="date" class="form-control" name="fechaIngreso" data-campo="fecha_ingreso" required>
                                        </div>
                                        <div class="col-6 col-xl-2">
                                            <label class="text-muted">Antigüedad</label>
                                            <input type="text" class="form-control" id="inputAntiguedadLegajo" readonly tabindex="-1">
                                        </div>
                                        <div class="col-6 col-xl-2 solo-dependencia">
                                            <label class="text-muted">Fin período de prueba</label>
                                            <input type="date" class="form-control" name="fechaFinPrueba" data-campo="fecha_fin_periodo_prueba">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1 d-none" id="legajoBloqueBaja">
                                        <div class="col-6 col-xl-2">
                                            <label class="text-muted">Fecha de egreso</label>
                                            <input type="date" class="form-control" name="fechaEgreso" data-campo="fecha_egreso">
                                        </div>
                                        <div class="col-6 col-xl-3">
                                            <label class="text-muted">Motivo de baja</label>
                                            <input type="text" class="form-control" name="motivoBaja" data-campo="motivo_baja" maxlength="50">
                                        </div>
                                        <div class="col-12 col-xl-7">
                                            <label class="text-muted">Observaciones</label>
                                            <input type="text" class="form-control" name="observBaja" data-campo="observ_baja" maxlength="1000">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- dependencia: mismos selectores que el alta + área y servicio --}}
                            <div class="col-12 solo-dependencia">
                                <div class="card p-3">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">PUESTO Y CONDICIONES</label>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Puesto de trabajo</label>
                                            <select class="form-select" id="selectorPuestoTrabajoLegajo" name="puestoTrabajo" data-campo="id_puesto"></select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Área</label>
                                            <select class="form-select" id="selectorAreaLegajo" name="idArea" data-campo="id_area"></select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Servicio</label>
                                            <select class="form-select" id="selectorServicioLegajo" name="idServicio" data-campo="id_servicio"></select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Categoría</label>
                                            <select class="form-select" id="selectorCategoriaLegajo" name="categoria" data-campo="id_categoria"></select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">UTI</label>
                                            <select class="form-select" name="selector-uti" data-campo="uti">
                                                <option value="">Seleccione</option>
                                                <option value="1">SI</option>
                                                <option value="0">NO</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Noche</label>
                                            <select class="form-select" name="selector-noche" data-campo="noche">
                                                <option value="">Seleccione</option>
                                                <option value="1">SI</option>
                                                <option value="0">NO</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Régimen</label>
                                            <select class="form-select" id="selectorRegimenLegajo" name="regimen" data-campo="regimen"></select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Horas diarias</label>
                                            <input class="form-control" id="inputHorasDiariasLegajo" name="horasDiarias" data-campo="horas_diarias" readonly>
                                        </div>
                                        <div class="col-12 col-xl-2">
                                            <label class="label-control text-muted">Tipo de contrato</label>
                                            <select class="form-select" id="selectorTipoContratoDepLegajo" name="tipoContratoDep" data-campo="tipo_contrato_dep"></select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Obra social</label>
                                            <select class="form-select" id="selectorObrasSocialesLegajo" name="obraSocial" data-campo="id_obra_social"></select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Código OS</label>
                                            <input class="form-control" id="inputCodigoObraSocialLegajo" data-campo="codigo_obra_social" readonly>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Convenio</label>
                                            <select class="form-select" name="convenio" data-campo="convenio">
                                                <option value="SANIDAD">SANIDAD</option>
                                                <option value="FUERA DE CONVENIO">FUERA DE CONVENIO</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-xl-1">
                                            <label class="label-control text-muted">Afiliado</label>
                                            <select class="form-select" name="sindicato" data-campo="afiliado" data-formato="sino">
                                                <option value="SI">SI</option>
                                                <option value="NO">NO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- externo / monotributista / médico --}}
                            <div class="col-12 solo-externo d-none">
                                <div class="card p-3">
                                    <div class="row g-3">
                                        <label class="label-control text-muted fw-bold">CONTRATACIÓN</label>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Tipo de contrato</label>
                                            <select class="form-select" id="selectorTipoContratoExtLegajo" name="tipoContratoExt" data-campo="tipo_contrato_ext"></select>
                                        </div>
                                        <div class="col-12 col-xl-3">
                                            <label class="label-control text-muted">Servicio</label>
                                            <select class="form-select" id="selectorServicioExternoLegajo" name="servicioExterno" data-campo="id_servicio_ext"></select>
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <label class="label-control text-muted">Razón social</label>
                                            <input class="form-control" name="razonSocial" data-campo="razon_social" type="text">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                         CONTACTOS Y FAMILIA
                         ============================================================ --}}
                    <div class="tab-pane fade" id="tabLegajoFamilia">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="label-control text-muted fw-bold">CONTACTOS DE EMERGENCIA</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarContactoLegajo">Agregar contacto</button>
                                    </div>
                                    <div class="row g-3 my-1" id="divContactosLegajo"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="label-control text-muted fw-bold">GRUPO FAMILIAR</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarFamiliarLegajo">Agregar familiar</button>
                                    </div>
                                    <div class="row g-3 my-1" id="divFamiliaresLegajo"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                         NOVEDADES (se cargan al abrir la pestaña)
                         ============================================================ --}}
                    <div class="tab-pane fade" id="tabLegajoNovedades">
                        <div class="card p-3">
                            <div class="row g-3 align-items-end mb-2">
                                <div class="col-6 col-xl-2">
                                    <label class="text-muted">Registradas desde</label>
                                    <input type="date" class="form-control filtro-novedades-legajo" id="filtroNovDesdeLegajo">
                                </div>
                                <div class="col-6 col-xl-2">
                                    <label class="text-muted">Hasta</label>
                                    <input type="date" class="form-control filtro-novedades-legajo" id="filtroNovHastaLegajo">
                                </div>
                                <div class="col-6 col-xl-2">
                                    <label class="text-muted">Liquidación</label>
                                    <select class="form-select filtro-novedades-legajo" id="filtroNovLiquidadaLegajo">
                                        <option value="">Todas</option>
                                        <option value="0">Pendientes</option>
                                        <option value="1">Liquidadas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Registro</th>
                                            <th>Novedad</th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Aplicación</th>
                                            <th>Duración</th>
                                            <th>Descripción</th>
                                            <th>Registró</th>
                                            <th class="text-center">Liquidada</th>
                                            <th class="text-center">Comprobantes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="legajoTbNovedades"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-prestamos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div>
                                <label class="form-label mb-0 small">Estado</label>
                                <select id="filtroEstadoPrestamo" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1" selected>Vigentes</option>
                                    <option value="0">Anulados</option>
                                </select>
                            </div>
                            <div class="small">Saldo pendiente: <strong id="saldoTotalPrestamos">$ 0,00</strong></div>
                        </div>
                        <table id="tablaLegajoPrestamos" class="table table-sm table-hover w-100">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>N°</th>
                                    <th>Inicio</th>
                                    <th class="text-end">Monto</th>
                                    <th>Cuotas</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Próx. período</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- ============================================================
                         HISTORIAL
                         ============================================================ --}}
                    <div class="tab-pane fade" id="tabLegajoHistorial">
                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Vínculo</th>
                                            <th>Puesto</th>
                                            <th>Área</th>
                                            <th>Servicio</th>
                                            <th>Categoría</th>
                                            <th>Régimen</th>
                                        </tr>
                                    </thead>
                                    <tbody id="legajoTbHistorial"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                {{-- el guardado se implementa con la edición --}}
                <button type="submit" class="btn btn-primary" id="btnGuardarLegajo">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalRegNovedadColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Registrar novedad de sueldo
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalNovedad()"></button>
            </div>

            <div class="modal-body">
                <form id="formCargaNovedad" action="{{ route('rrhh.novedades.store') }}" method="POST">
                    @csrf
                    <div class="section-divider mb-3">
                        <span>Información general</span>
                    </div>

                    <div class="empleado-box p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label">Fecha de registro</label>
                                <input type="text" class="form-control" name="fechaRegistro"
                                    value="{{ now()->format('d-m-Y') }}" readonly>
                            </div>

                            <div class="col-lg-5 col-md-4">
                                <label class="form-label">Registrante</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->name }}"
                                    name="registrante" readonly>
                            </div>

                            <div class="col-lg-3 col-md-4">
                                <label class="form-label">Rol</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->rol }}" readonly>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">Fecha aplicación</label>
                                <input type="date" class="form-control" name="fechaAplicacion" onchange=""
                                    required readonly>
                            </div>

                        </div>
                    </div>

                    <div class="section-divider mb-3">
                        <span>Información del colaborador</span>
                    </div>

                    <div class="empleado-box p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-lg-2">
                                <label class="form-label">Legajo</label>
                                <input type="number" class="form-control" name="legajo" id="inputLegajo" required
                                    readonly>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">Colaborador/a</label>
                                <input type="text" class="form-control" name="colaborador" id="inputColaborador"
                                    required readonly>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">Servicio</label>
                                <input type="text" class="form-control" name="servicio" readonly>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">Antigüedad</label>
                                <input type="text" class="form-control" name="antiguedad" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider mb-3">
                        <span>Registrar novedad de sueldo</span>
                    </div>

                    <div class="empleado-box p-3">
                        <table id="tablaNovedades" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>NOVEDAD</th>
                                    <th>CÓDIGO</th>
                                    <th>DESDE</th>
                                    <th>HASTA</th>
                                    <th>VALOR</th>
                                    <th>OBSERVACIONES</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <button type="button" id="btnAgregarNovedad" class="btn btn-primary">
                            Agregar novedad
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalNovedad()">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formCargaNovedad" id="btnRegistrarNovedad">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Registrar novedad
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSubirArchivo" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-upload me-2"></i>
                    Subir archivo
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formSubirArchivo" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <input type="hidden" name="id_registro" id="inputRegistroArchivo">

                    <label class="form-label">Archivo</label>
                    <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.png" required>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-primario">
                        Subir
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modalVerComprobantes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-paperclip me-2"></i>
                    Comprobantes de la novedad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="contenedorComprobantes">
                <!-- visor dinámico -->
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaOs" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-plus me-2"></i> Registrar nueva OS
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalOs()"></button>
            </div>

            <div class="modal-body">
                <form id="formNuevaOs" action="{{ route('rrhh.obraSocial.registraNuevaOs') }}" method="POST">
                    @csrf <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label fw-bold">Código de la Obra Social</label>
                            <input type="text" class="form-control" name="codigoOs" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nombre de la Obra Social</label>
                            <input type="text" class="form-control" name="nombreOs" required>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalOs()">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formNuevaOs" id="btnRegistrarOs">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Registrar OS
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalDarBajaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloBajaColab">
                    <i class="fa-solid fa-plus me-2"></i> Dar de baja colaborador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formBajaColaborador">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-3 col-xl-3">
                            <label class="form-label fw-bold">FECHA DE BAJA</label>
                            <input type="text" class="form-control" name="fechaBajaColab" id="fechaBajaColab"
                                required>
                            <input type="hidden" id="inputLegajoBaja">
                        </div>
                        <div class="col-12 col-md-9 col-xl-9">
                            <label class="form-label fw-bold">MOTIVO DE BAJA</label>
                            <select id="selectMotivoBajaColab" name="selectMotivoBajaColab" class="form-select"
                                required>
                                <option value="" selected disabled>
                                    Seleccione motivo de baja
                                </option>
                                <option value="Mutuo acuerdo">
                                    Mutuo acuerdo
                                </option>
                                <option value="Renuncia del trabajador">
                                    Renuncia del trabajador
                                </option>
                                <option value="Despido con causa">
                                    Despido con causa
                                </option>
                                <option value="Despido sin causa">
                                    Despido sin causa
                                </option>
                                <option value="Finalización de contrato">
                                    Finalización de contrato
                                </option>
                                <option value="Vencimiento del periodo de prueba">
                                    Vencimiento del periodo de prueba
                                </option>
                                <option
                                    value="Incapacidad o inhabilidad del trabajador: Jubilación o incapacidad permanente">
                                    Incapacidad o inhabilidad del trabajador: Jubilación o incapacidad permanente
                                </option>
                                <option
                                    value="Fuerza mayor o falta de trabajo: Causas imprevisibles o económicas graves de la empresa">
                                    Fuerza mayor o falta de trabajo: Causas imprevisibles o económicas graves de la
                                    empresa
                                </option>
                                <option value="Muerte">
                                    Muerte
                                </option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">OBSERVACIONES</label>
                            <input type="text" class="form-control" name="observacionesBaja">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formBajaColaborador"
                    id="btnBajaColaborador">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Dar de baja
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalPrestamo" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> Registrar préstamo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-bs-target="#modalPrestamo"></button>
            </div>
            <form id="formPrestamo">
                <div class="modal-body">
                    <div class="d-flex flex-column gap-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="text-muted">Fecha</label>
                                <input type="date" class="form-control" name="fechaPrest" id="inputFechaPrest" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="text-muted">Legajo</label>
                                <input type="text" class="form-control" name="legajoPrest" id="inputLegajoPrest" readonly required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6">
                                <label class="text-muted">Colaborador</label>
                                <input type="text" class="form-control" name="colaboradorPrest" id="inputColaboradorPrest" readonly>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="text-muted">Monto préstamo</label>
                                <input type="text" class="form-control" name="montoPrest" id="inputMontoPrest" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="text-muted">Cuotas</label>
                                <input type="number" class="form-control" name="cuotasPrest" id="inputCuotasPrest" min="1" step="1" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="text-muted">Monto cuotas</label>
                                <input type="text" class="form-control" name="montoCuotasPrest" id="inputMontoCuotasPrest" required readonly>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="text-muted">Inicio descuento</label>
                                <input type="month" class="form-control" name="mesDescuentoPrest" id="inputMesDescuentoPrest" required>
                            </div>
                        </div>
                        <label class="text-muted fw-bold">NOVEDADES A GENERAR AUTOMÁTICAMENTE</label>
                        <table id="tbDetallePrestamo" class="table">
                            <thead>
                                <tr>
                                    <th>NOVEDAD</th>
                                    <th>CODIGO</th>
                                    <th>FECHA APLICACIÓN</th>
                                    <th>DETALLE</th>
                                    <th>IMPORTE</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" id="btnRegistrarPrestamo">Registrar préstamo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    window.RRHH_ROUTES = Object.assign(window.RRHH_ROUTES || {}, {
        subirComprobante: "{{ url('/rrhh/novedades/subir-comprobante') }}",
        comprobantesNovedad: "{{ url('/rrhh/novedades/:id/comprobantes') }}",
        verComprobante: "{{ url('/rrhh/comprobantes/:id/ver') }}",
    });
</script>

<script>
    const logoHp3c = "{{ asset('img/logo_2.png') }}";
    const firma = "{{ asset('img/ffernandez-firma.png') }}"
</script>

<script src="{{ asset('js/novedades/abmNovedades.js') }}"></script>
<script src="{{ asset('js/personal/selectores.js') }}"></script>
<script src="{{ asset('js/personal/administrarComprobantes.js') }}"></script>
<script src="{{ asset('js/personal/administrarContEmerg.js') }}"></script>
<script src="{{ asset('js/personal/administrarFamiliares.js') }}"></script>
<script src="{{ asset('js/personal/administrarCuentas.js') }}"></script>
<script src="{{ asset('js/personal/administrarLegajo.js') }}"></script>
<script src="{{ asset('js/personal/edicionLegajo.js') }}"></script>
<script src="{{ asset('js/personal/scriptComun.js') }}"></script>
<script src="{{ asset('js/personal/editColaborador.js') }}"></script>
<script src="{{ asset('js/personal/prestamos.js') }}"></script>
<script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
<script src="{{ asset('js/personal/nuevaAltaPersonal.js') }}"></script>
<script src="{{ asset('js/personal/generarConstanciaPdf.js') }}"></script>

<script>
    const USER_ROLE = "{{ Auth::user()->rol }}";
    const USER_AREA_ID = "{{ Auth::user()->area_id }}";

    if (USER_ROLE !== "Administrador/a") {
        $(".editAllowed").each(function() {
            if ($(this).is("input, textarea")) {
                $(this).prop("readonly", true);
            } else if ($(this).is("select")) {
                $(this)
                    .addClass("readonly-select") // para estilos (opacity, cursor, etc.)
                    .attr("tabindex", "-1")
                    .on("mousedown keydown", function(e) {
                        e.preventDefault();
                    });
            }
        });
    }
</script>
@endpush