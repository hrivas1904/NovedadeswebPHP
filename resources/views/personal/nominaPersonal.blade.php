@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">GESTIÓN DE COLABORADORES</h3>

    <div class="row d-flex justify-content-start align-items-start">
        <div class="col-2 d-none d-xl-block">
            <div class="card" style="border-radius:15px;">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                            Filtros
                        </h5>
                        <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3">
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
                                <input
                                    type="radio"
                                    name="estado"
                                    class="radio-estado"
                                    value="">
                                TODOS
                            </label>

                            <label class="filtro-item">
                                <input
                                    type="radio"
                                    name="estado"
                                    class="radio-estado"
                                    value="ACTIVO"
                                    checked>
                                ACTIVOS
                            </label>

                            <label class="filtro-item">
                                <input
                                    type="radio"
                                    name="estado"
                                    class="radio-estado"
                                    value="DE BAJA">
                                DE BAJA
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary w-100">
                            <i class="fa-solid fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-10 col-xxl-10">
            <div class="card" style="border-radius:15px;">
                <div class="card-header">
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">

                        @if (Auth::user()->rol === 'Administrador/a')
                        <button type="button" class="btn btn-primary" onclick="abrirModal()">
                            <i class="fa-solid fa-user me-2"></i>
                            Nuevo Colaborador
                        </button>
                        @endif

                        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">

                            <!-- SOLO DESKTOP -->
                            <button type="button" id="btnExportExcel"
                                class="btn btn-primary d-none d-md-inline-flex align-items-center">
                                <i class="fa-regular fa-file-excel me-2"></i>
                                Excel
                            </button>

                            <!-- BUSCADOR -->
                            <div class="input-group buscador-personal">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                </span>

                                <input type="text" id="searchPersonal" class="form-control"
                                    placeholder="Buscar colaborador...">

                                <button class="btn btn-secondary" id="btnClearSearch" type="button">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive px-2">
                        <table id="tb_personal" class="table table-bordered table-hover align-middle">
                            <thead class="thead-dark">
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
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalAltaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user me-2"></i> Dar de alta nuevo colaborador
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModal()"></button>
            </div>

            <div class="modal-body">
                <form id="formAltaColaborador">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <div class="section-divider mb-3">
                                <span>Datos Personales</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-7">
                                        <label class="form-label">Apellido y Nombre</label>
                                        <input type="text" name="nombre_completo" id="nombre_completo"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Estado</label>
                                        <select name="estado" id="estado" class="form-select">
                                            <option value="ACTIVO">ACTIVO</option>
                                            <option value="DE BAJA">DE BAJA</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">DNI</label>
                                        <input type="number" name="dni" id="dni" class="form-control"
                                            inputmode="numeric" maxlength="8" required>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label fw-bold">CUIL</label>
                                        <div class="input-group">
                                            <input type="number" id="firstPartCuil" class="form-control text-center"
                                                min="00" style="max-width: 70px;" maxlength="2" required>
                                            <span class="input-group-text">-</span>
                                            <input type="text" id="middlePartCuil"
                                                class="form-control text-center bg-light" readonly>
                                            <span class="input-group-text">-</span>
                                            <input type="number" id="lastPartCuil" class="form-control text-center"
                                                min="0" max="9" style="max-width: 60px;"
                                                maxlength="1" required>
                                        </div>
                                        <input name="cuil" id="cuil" hidden>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Fecha nacimiento</label>
                                        <input id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                                            type="text" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="section-divider mb-3">
                                <span>Datos de Contacto</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-7">
                                        <label class="form-label">Correo electrónico</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input type="number" name="telefono" id="telefono" class="form-control">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input type="text" name="domicilio" id="domicilio" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <select id="selectLocalidad" name="localidad" class="form-select"
                                            style="width:100%" required>
                                            <option value="">Seleccionar localidad</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Contactos de emergencia</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">

                                    <!-- CONTACTO 1 -->
                                    <div class="col-lg-6">
                                        <div class="card-glass p-3 h-100">
                                            <h6 class="mb-3 text-muted">Contacto de Emergencia 1</h6>
                                            <div class="mb-2">
                                                <label class="form-label">Nombre</label>
                                                <input id="personaEmergencia1Edit" name="personaEmerg1"
                                                    class="form-control editAllowed">
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Teléfono</label>
                                                    <input id="contactoEmergencia1Edit" name="telefEmerg1"
                                                        class="form-control editAllowed">
                                                </div>

                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Parentesco</label>
                                                    <input id="parentescoEmergencia1Edit" name="parentesco1"
                                                        class="form-control editAllowed">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CONTACTO 2 -->
                                    <div class="col-lg-6">
                                        <div class="card-glass p-3 h-100">
                                            <h6 class="mb-3 text-muted">Contacto de Emergencia 2</h6>

                                            <div class="mb-2">
                                                <label class="form-label">Nombre</label>
                                                <input id="personaEmergencia2Edit" name="personaEmerg2"
                                                    class="form-control editAllowed">
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Teléfono</label>
                                                <input id="contactoEmergencia2Edit" name="telefEmerg2"
                                                    class="form-control editAllowed">
                                            </div>

                                            <div>
                                                <label class="form-label">Parentesco</label>
                                                <input id="parentescoEmergencia2Edit" name="parentesco2"
                                                    class="form-control editAllowed">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Grupo familiar</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2">
                                        <label class="form-label">Padre</label>
                                        <input id="padreColaborador" name="padreColaborador"
                                            class="form-control"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Madre</label>
                                        <input id="madreColaborador" name="madreColaborador" class="form-control"
                                            type="text"></input>
                                    </div>
                                    <div class="col-lg-2 d-flex flex-column">
                                        <label class="form-label">Agregar hijo/a</label>
                                        <button type="button" id="btnAgregarHijo" class="btn btn-primary">
                                            <i class="fa-solid fa-plus"></i> Hijo/a
                                        </button>
                                    </div>

                                    <div id="divHijos" class="col-lg-12 p-3" hidden>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Datos Socioeconómicos</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">Estado civil</label>
                                        <select name="estado_civil" id="estado_civil" class="form-select" required>
                                            <option selected disabled="">Seleccione estado civil</option>
                                            <option value="SOLTERO/A">SOLTERO/A</option>
                                            <option value="CASADO/A">CASADO/A</option>
                                            <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                            <option value="VIUDO/A">VIUDO/A</option>
                                            <option value="N/D">N/D</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Género</label>
                                        <select name="genero" id="genero" class="form-select" required>
                                            <option selected disabled value="">Seleccione género</option>
                                            <option value="MASCULINO">MASCULINO</option>
                                            <option value="FEMENINO">FEMENINO</option>
                                            <option value="NO BINARIO">NO BINARIO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label fw-bold">Obra social</label>
                                        <div class="input-group flex-nowrap">
                                            <select name="obra_social_id" id="obraSocial" class="form-select">
                                                <option value="">Seleccione obra social</option>
                                            </select>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                id="btnAbrirModalOs">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Código OS</label>
                                        <input type="text" name="codigo_os" id="codigoOS" class="form-control"
                                            readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Título</label>
                                        <select name="posee_titulo" id="select-titulo" class="form-select">
                                            <option selected disabled value="" disabled>Seleccione opción
                                            </option>
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 d-none" id="divTitulo">
                                        <label class="form-label">Descripción de título</label>
                                        <input name="titulo" class="form-control" type="text" />
                                    </div>

                                    <div class="col-lg-1 d-none" id="divMatricula">
                                        <label class="form-label">M.P.</label>
                                        <input name="matricula_profesional" class="form-control" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Datos Laborales</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2">
                                        <label class="form-label">Tipo de contrato</label>
                                        <select name="tipo_contrato" id="tipoContrato" class="form-select" required>
                                            <option selected="">Seleccione opción</option>
                                            <option value="Tiempo fijo">PLAZO FIJO</option>
                                            <option value="Tiempo indeterminado">PLAZO INDETERMINADO</option>
                                            <option value="Monotributista">MONOTRIBUTISTA</option>
                                            <option value="Pasantía">PASANTÍA</option>
                                            <option value="Práctica profesional">PRÁCTICA PROFESIONAL</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha ingreso</label>
                                        <input type="date" name="fecha_ingreso" id="fechaInicio"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="date" name="fecha_fin_prueba" id="fechaFin"
                                            class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Área</label>
                                        <select name="area_id" id="area" class="form-select js-select-area">
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Servicio</label>
                                        <select name="servicio_id" id="servicio"
                                            class="form-select js-select-servicio" disabled>
                                            <option selected value="">Seleccione servicio</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Convenio</label>
                                        <select name="posee_convenio" id="convenio" class="form-select">
                                            <option value="SANIDAD">SANIDAD</option>
                                            <option value="FUERA DE CONVENIO">FUERA DE CONVENIO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria_id" id="categoria"
                                            class="form-select js-select-categoria">
                                            <option selected value="">Seleccione categoría</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Rol</label>
                                        <select name="rol_interno_id" id="rol_interno"
                                            class="form-select js-select-rol">
                                            <option selected value="">Seleccione rol</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Régimen horario</label>
                                        <select name="regimen_horas" id="selectRegimen" class="form-select">
                                            <option selected disabled value="">Seleccione régimen</option>
                                            <option value="44">44</option>
                                            <option value="40">40</option>
                                            <option value="35">35</option>
                                            <option value="32">32</option>
                                            <option value="30">30</option>
                                            <option value="24">24</option>
                                            <option value="22">22</option>
                                            <option value="20">20</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 col-sm-2">
                                        <label class="form-label">Horas diarias</label>
                                        <input type="text" name="horas_diarias" id="horasDiarias"
                                            class="form-control" readonly required title="Horas Diarias">
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Es coordinador</label>
                                        <select name="es_coordinador" id="es_coordinador" class="form-select">
                                            <option selected disabled value="">Seleccione opción</option>
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-2">
                                        <label class="form-label">Afiliado al sindicato</label>
                                        <select name="es_afiliado" id="es_afiliado" class="form-select">
                                            <option selected disabled value="">Seleccione opción</option>
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal()">
                    Cancelar
                </button>
                <button type="submit" form="formAltaColaborador" class="btn btn-primary"
                    id="btnGuardarColaborador">
                    Dar alta
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLegajoColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloLegajo">
                    <i class="fa-solid fa-id-card me-2"></i> Legajo del colaborador
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalLegajo()"></button>
            </div>

            <div class="modal-body">
                <form id="formEditColaborador">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <div class="section-divider mb-3">
                                <span>Datos Personales</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">Legajo</label>
                                        <input type="text" id="inputLegajo" name="legajo"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Apellido y Nombre</label>
                                        <input type="text" id="inputNombre" name="colaborador"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Estado</label>
                                        <input type="text" id="inputEstado" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">DNI</label>
                                        <input type="text" id="inputDni" name="dni"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">CUIL</label>
                                        <input type="text" id="inputCuil" name="cuil"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Fecha nacimiento</label>
                                        <input type="date" id="inputFechaNacimiento" name="fechaNacimiento"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Edad</label>
                                        <input type="text" id="inputEdad" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="section-divider mb-3">
                                <span>Datos de Contacto</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-7">
                                        <label class="form-label">Correo electrónico</label>
                                        <input id="inputEmail" name="correo" class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input id="inputTelefono" name="telefono" class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input id="inputDomicilio" name="domicilio" class="form-control editAllowed">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <select id="selectLocalidadEdit" name="localidad"
                                            class="form-select editAllowed" style="width:100%">
                                            <option value="">Seleccionar localidad</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Datos Socioeconómicos</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <label class="form-label">Estado civil</label>
                                        <select id="inputEstadoCivil" name="estado_civil"
                                            class="form-select editAllowed">
                                            <option value="SOLTERO/A">SOLTERO/A</option>
                                            <option value="CASADO/A">CASADO/A</option>
                                            <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                            <option value="VIUDO/A">VIUDO/A</option>
                                            <option value="N/D">N/D</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-3">
                                        <label class="form-label">Género</label>
                                        <select name="genero" id="inputGenero" class="form-select editAllowed">
                                            <option value="MASCULINO">MASCULINO</option>
                                            <option value="FEMENINO">FEMENINO</option>
                                            <option value="NO BINARIO">NO BINARIO</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">Obra social</label>
                                        <div class="input-group flex-nowrap">
                                            <select id="selectObraSocialEdit" name="obra_social_id"
                                                class="form-select editAllowed">
                                            </select>

                                            <button type="button" class="btn btn-primary" id="btnAbrirModalOs">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Código OS</label>
                                        <input type="text" id="inputCodigoOS" class="form-control" readonly>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">Título</label>
                                        <input type="text" id="inputTitulo" class="form-control" readonly>
                                    </div>

                                    <div class="col-12 col-md-8 col-lg-6">
                                        <label class="form-label">
                                            Descripción de Título
                                        </label>
                                        <input id="inputDescripTitulo" name="descrip_titulo"
                                            class="form-control editAllowed">
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label">M.P.</label>
                                        <input id="inputMatricula" name="mat_prof" class="form-control editAllowed">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Datos Laborales</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Tipo de contrato</label>
                                        <select id="inputTipoContrato" name="tipo_contrato"
                                            class="form-select editAllowed">
                                            <option value="Tiempo fijo">PLAZO FIJO</option>
                                            <option value="Tiempo indeterminado">PLAZO INDETERMINADO</option>
                                            <option value="Monotributista">MONOTRIBUTISTA</option>
                                            <option value="Pasantía">PASANTÍA</option>
                                            <option value="Práctica profesional">PRÁCTICA PROFESIONAL</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Fecha ingreso</label>
                                        <input type="text" id="inputFechaIngreso" name="fechaIngreso"
                                            class="form-control editAllowed">
                                    </div>
                                    <div class="col-xl-2 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="text" id="inputFechaFinPrueba" class="form-control" readonly>
                                    </div>
                                    <div class="col-xl-2 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Fecha egreso</label>
                                        <input type="text" id="inputFechaEgreso" class="form-control editAllowed">
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Antiguedad</label>
                                        <input type="text" id="inputAntiguedad" class="form-control" readonly>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Área</label>
                                        <select name="area_id" id="inputArea"
                                            class="form-select js-select-area w-100 editAllowed">
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Servicio</label>
                                        <select name="servicio_id" id="inputServicio"
                                            class="form-select w-100 editAllowed">
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Convenio</label>
                                        <select name="posee_convenio" id="inputConvenio"
                                            class="form-select w-100 editAllowed">
                                            <option value="SANIDAD">SANIDAD</option>
                                            <option value="FUERA DE CONVENIO">FUERA DE CONVENIO</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria_id" id="inputCategoria"
                                            class="form-select js-select-categoria w-100 editAllowed">
                                            <option value="">Seleccione categoría</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                        <label class="form-label">Rol</label>
                                        <select name="rol_interno_id" id="inputRol"
                                            class="form-select w-100 editAllowed">
                                            <option value="">Seleccione rol</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-1 col-lg-3 col-md-6 col-12">
                                        <label class="form-label">Régimen</label>
                                        <select name="regimen_horas" id="inputRegimen"
                                            class="form-select editAllowed">
                                            <option selected disabled value="">Seleccione régimen</option>
                                            <option value="44">44</option>
                                            <option value="40">40</option>
                                            <option value="35">35</option>
                                            <option value="32">32</option>
                                            <option value="30">30</option>
                                            <option value="24">24</option>
                                            <option value="22">22</option>
                                            <option value="20">20</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-12">
                                        <label class="form-label">Horas diarias</label>
                                        <input id="inputHorasDiarias" name="horas_diarias" class="form-control"
                                            readonly>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-12">
                                        <label class="form-label">Es coordinador</label>
                                        <select id="inputCordinador" name="es_coordinador"
                                            class="form-select editAllowed">
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-12">
                                        <label class="form-label">Afiliado al sindicato</label>
                                        <select id="inputAfiliado" name="es_afiliado"
                                            class="form-select editAllowed">
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-1 col-lg-2 col-md-6 col-12">
                                        <label class="form-label">Noche</label>
                                        <select id="inputNoche" name="noche" class="form-select">
                                            <option value="1">SI</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-1 col-lg-2 col-md-6 col-12">
                                        <label class="form-label">UTI</label>
                                        <select id="inputUti" name="uti" class="form-select">
                                            <option value="1">SI</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (Auth::user()->rol === 'Administrador/a')
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Información bancaria del colaborador</span>
                            </div>
                            <div class="empleado-box p-3">
                                <button type="button" id="btnMostraDivNuevaCuenta" class="btn btn-primary">
                                    <i class="fa-solid fa-square-plus"></i>
                                    Agregar cuenta
                                </button>
                                <div id="divCrearCuentaBancariaColab" class="row g-1 my-2 d-none">
                                    <div class="empleado-box p-3">
                                        <div class="row align-items-center mb-2">
                                            <div class="col">
                                                <h5 class="mb-0">Crear nueva cuenta</h5>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn"
                                                    id="btnOcultarDivCuentaNueva">
                                                    <i class="fa-solid fa-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <label class="form-label">Banco</label>
                                                <select class="form-select" id="selectBanco" name="banco">
                                                    <option selected value="">Seleccione banco</option>
                                                    <option value="Macro">Macro</option>
                                                    <option value="Francés">Francés</option>
                                                    <option value="Nación">Nación</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <label class="form-label">Cuenta N°</label>
                                                <input name="numero_cuenta" type="number" id="inputNumeroCuenta"
                                                    class="form-control" />
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <label class="form-label">CBU</label>
                                                <input name="cbu" type="number" id="inputCbu"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" type="button"
                                            id="btnGuardarCuenta">Crear
                                            cuenta
                                        </button>
                                    </div>
                                </div>
                                <div id="contenedorCuentasBancarias" class="row g-1 my-2">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Grupo familiar</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2">
                                        <label class="form-label">Padre</label>
                                        <input id="padreColaboradorEdit" name="padre" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Madre</label>
                                        <input id="madreColaboradorEdit" name="madre" class="form-control">
                                    </div>

                                    <div class="col-lg-2 d-flex flex-column">
                                        <label class="form-label">Agregar hijo/a</label>
                                        <button type="button" id="btnAgregarHijoEdit" class="btn btn-primary">
                                            <i class="fa-solid fa-plus"></i> Hijo/a
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <div id="divHijosEdit" class="row g-3" hidden>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Contactos de emergencia</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">

                                    <!-- CONTACTO 1 -->
                                    <div class="col-lg-6 col-12 p-2">
                                        <div class="card p-2">
                                            <h6 class="mb-3 text-muted">Contacto de emergencia 1</h6>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Nombre</label>
                                                    <input id="personaEmergencia1Edit" name="personaEmerg1"
                                                        class="form-control editAllowed">
                                                </div>
                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Teléfono</label>
                                                    <input id="contactoEmergencia1Edit" name="telefEmerg1"
                                                        class="form-control editAllowed">
                                                </div>
                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Parentesco</label>
                                                    <input id="parentescoEmergencia1Edit" name="parentesco1"
                                                        class="form-control editAllowed">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-12 p-2">
                                        <div class="card p-2">
                                            <h6 class="mb-3 text-muted">Contacto de emergencia 2</h6>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Nombre</label>
                                                    <input id="personaEmergencia2Edit" name="personaEmerg2"
                                                        class="form-control editAllowed">
                                                </div>

                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Teléfono</label>
                                                    <input id="contactoEmergencia2Edit" name="telefEmerg2"
                                                        class="form-control editAllowed">
                                                </div>

                                                <div class="col-lg-6 col-12">
                                                    <label class="form-label">Parentesco</label>
                                                    <input id="parentescoEmergencia2Edit" name="parentesco2"
                                                        class="form-control editAllowed">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Requerimientos alimentarios</span>
                            </div>
                            <div class="empleado-box p-3">
                                <i class="fa-solid fa-circle-info text-primary" style="cursor: pointer;"
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    title="Si no tiene ningún requerimiento específico, no
                                    seleccione nada. Si no encuentra un requerimiento específico,
                                    seleccione Otro y complete Observaciones">
                                </i>
                                <div class="row g-1">
                                    <div class="row g-1" id="contenedorRequerimientos"></div>
                                    <div class="row mt-3" id="rowObservacionReq" style="display:none;">
                                        <div class="col-12">
                                            <label class="form-label">Observaciones</label>
                                            <input type="text" name="req_alimenticio_otro" id="inputReqOtro"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="file" id="inputComprobantes" accept="pdf,image/*" multiple hidden>

                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="section-divider mb-3">
                                <span>Historial de Novedades</span>
                            </div>
                            <div class="card table-responsive">
                                <table id="tb_historialNovedades"
                                    class="table table-striped table-bordered table-hover align-middle nowrap">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>FECHA REGISTRO</th>
                                            <th>NOVEDAD</th>
                                            <th>DESDE</th>
                                            <th>HASTA</th>
                                            <th>VALOR</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>COMPROBANTES</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-tertiary" type="button" id="btnGenerarConstancia">
                    Generar constancia de trabajo
                </button>
                <button class="btn btn-primary" type="submit" form="formEditColaborador">
                    Guardar cambios
                </button>
                <button class="btn btn-secondary" onclick="cerrarModalLegajo()">
                    Atrás
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegNovedadColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Registrar novedad de sueldo
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalNovedad()"></button>
            </div>

            <div class="modal-body">
                <form id="formCargaNovedad" action="{{ route('novedades.store') }}" method="POST">
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

<div class="modal fade" id="modalNuevaOs" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
    data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-plus me-2"></i> Registrar nueva OS
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalOs()"></button>
            </div>

            <div class="modal-body">
                <form id="formNuevaOs" action="{{ route('obraSocial.registraNuevaOs') }}" method="POST">
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

<div class="modal fade" id="modalDarBajaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloBajaColab">
                    <i class="fa-solid fa-plus me-2"></i> Dar de baja colaborador
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalBaja()"></button>
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
                <button type="button" class="btn btn-secondary" onclick="cerrarModalBaja()">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formBajaColaborador"
                    id="btnBajaColaborador">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Dar de baja
                </button>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const logoHp3c = "{{ asset('img/logo_2.png') }}";
    const firma = "{{ asset('img/ffernandez-firma.png') }}"
</script>

<script src="{{ asset('js/novedades/abmNovedades.js') }}"></script>
<script src="{{ asset('js/personal/administrarHijosColab.js') }}"></script>
<script src="{{ asset('js/personal/administrarCuentas.js') }}"></script>
<script src="{{ asset('js/personal/scriptComun.js') }}"></script>
<script src="{{ asset('js/personal/editColaborador.js') }}"></script>
<script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
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