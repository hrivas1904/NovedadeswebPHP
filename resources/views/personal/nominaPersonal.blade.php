@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
<div class="container-fluid">

    <div class="text-start mb-4">
        <h3 class="pill-heading tituloVista">GESTIÓN DEL PERSONAL ACTIVO</h3>
    </div>

    <div class="card" style="border-radius:15px;">
        <div class="card-header">
            <div class="row d-flex align-item-center">
                <div class="col-12 d-flex justify-content-start align-items-center gap-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto">
                            <select id="filtroArea" class="form-select js-select-area w-100"
                                {{ !in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a L2']) ? 'disabled' : '' }}>
                            </select>

                            @if (!in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a L2']))
                            <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                            @endif
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                            <select id="filtroCategoria" class="form-select js-select-categFiltro w-100"></select>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                            <select id="filtroRegimen" class="form-select js-select-regFiltro w-100"></select>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                            <select id="filtroConvenio" class="form-select js-select-convenioFiltro w-100"></select>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto d-none d-md-block">
                            <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary w-100">
                                <i class="fa-solid fa-eraser"></i> Limpiar
                            </button>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-auto text-xl-end">
                            @if (Auth::user()->rol === 'Administrador/a')
                            <button type="button" class="btn btn-primary w-100" onclick="abrirModal()">
                                <i class="fa-solid fa-user me-2"></i> Nuevo Colaborador
                            </button>
                            @endif
                        </div>
                    </div>

                    <script>
                        const USER_ROLE = "{{ Auth::user()->rol }}";
                        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                    </script>

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
                            <th>RÉG</th>
                            <th>HD</th>
                            <th>CONVENIO</th>
                            <th>ESTADO</th>
                            <th>UTI</th>
                            <th>NOCHE</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

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
                                                min="0" max="9" style="max-width: 60px;" maxlength="1"
                                                required>
                                        </div>
                                        <input name="cuil" id="cuil" hidden>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Fecha nacimiento</label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                            class="form-control" required>
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
                                    <div class="col-lg-2">
                                        <label class="form-label">Nombre</label>
                                        <input id="personaEmergencia1" name="persona_emerg1"
                                            class="form-control"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Teléfono</label>
                                        <input id="contactoEmergencia1" name="contacto_emerg1" class="form-control"
                                            type="number"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Parentesco</label>
                                        <input id="parentescoEmergencia1" name="parentesco_emerg1"
                                            class="form-control"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Nombre</label>
                                        <input id="personaEmergencia2" name="persona_emerg2"
                                            class="form-control"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Teléfono</label>
                                        <input id="contactoEmergencia2" name="contacto_emerg2" class="form-control"
                                            type="number"></input>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Parentesco</label>
                                        <input id="parentescoEmergencia2" name="parentesco_emerg2"
                                            class="form-control"></input>
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
                                        <button id="btnAgregarHijo" class="btn btn-primary">
                                            <i class="fa-solid fa-plus"></i> Hijo/a
                                        </button>
                                    </div>

                                    <div id="divHijos" class="col-lg-12 empleado-box p-3" hidden>

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
                                        <input type="text" id="inputLegajo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Apellido y Nombre</label>
                                        <input type="text" id="inputNombre" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Estado</label>
                                        <input type="text" id="inputEstado" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">DNI</label>
                                        <input type="text" id="inputDni" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">CUIL</label>
                                        <input type="text" id="inputCuil" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Fecha nacimiento</label>
                                        <input type="text" id="inputFechaNacimiento" class="form-control"
                                            readonly>
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
                                        <input id="inputEmail" name="correo" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input id="inputTelefono" name="telefono" class="form-control">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input id="inputDomicilio" name="domicilio" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <select id="selectLocalidadEdit" name="localidad" class="form-select"
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
                                    <div class="col-lg-2">
                                        <label class="form-label">Nombre</label>
                                        <input id="personaEmergencia1Edit" name="personaEmerg1" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Teléfono</label>
                                        <input id="contactoEmergencia1Edit" name="telefEmerg1" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Parentesco</label>
                                        <input id="parentescoEmergencia1Edit" name="parentesco1" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Nombre</label>
                                        <input id="personaEmergencia2Edit" name="personaEmerg2" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Teléfono</label>
                                        <input id="contactoEmergencia2Edit" name="telefEmerg2" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Parentesco</label>
                                        <input id="parentescoEmergencia2Edit" name="parentesco2" class="form-control">
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
                                        <input id="padreColaboradorEdit" name="padre" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Madre</label>
                                        <input id="madreColaboradorEdit" name="madre" class="form-control">
                                    </div>

                                    <div class="col-lg-2 d-flex flex-column">
                                        <label class="form-label">Agregar hijo/a</label>
                                        <button id="btnAgregarHijo" class="btn btn-primary">
                                            <i class="fa-solid fa-plus"></i> Hijo/a
                                        </button>
                                    </div>

                                    <div id="divHijosEdit" class="col-lg-12 empleado-box p-3" hidden>

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
                                        <select id="inputEstadoCivil" name="estado_civil" class="form-select">
                                            <option value="SOLTERO/A">SOLTERO/A</option>
                                            <option value="CASADO/A">CASADO/A</option>
                                            <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                            <option value="VIUDO/A">VIUDO/A</option>
                                            <option value="N/D">N/D</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Género</label>
                                        <select name="genero" id="inputGenero" class="form-select">
                                            <option value="MASCULINO">MASCULINO</option>
                                            <option value="FEMENINO">FEMENINO</option>
                                            <option value="NO BINARIO">NO BINARIO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Obra social</label>
                                        <div class="input-group flex-nowrap">
                                            <select id="selectObraSocialEdit" name="obra_social_id" class="form-select">
                                            </select>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                id="btnAbrirModalOs">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Código OS</label>
                                        <input type="text" id="inputCodigoOS" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Título</label>
                                        <input type="text" id="inputTitulo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Descripción de Título</label>
                                        <input id="inputDescripTitulo" name="descrip_titulo" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">M.P.</label>
                                        <input id="inputMatricula" name="mat_prof" class="form-control">
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
                                        <select id="inputTipoContrato" name="tipo_contrato" class="form-select">
                                            <option value="Tiempo fijo">PLAZO FIJO</option>
                                            <option value="Tiempo indeterminado">PLAZO INDETERMINADO</option>
                                            <option value="Monotributista">MONOTRIBUTISTA</option>
                                            <option value="Pasantía">PASANTÍA</option>
                                            <option value="Práctica profesional">PRÁCTICA PROFESIONAL</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Fecha ingreso</label>
                                        <input type="text" id="inputFechaIngreso" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="text" id="inputFechaFinPrueba" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Fecha egreso</label>
                                        <input type="text" id="inputFechaEgreso" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Antiguedad</label>
                                        <input type="text" id="inputAntiguedad" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Área</label>
                                        <select name="area_id" id="inputArea" class="form-select js-select-area">
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Servicio</label>
                                        <select name="servicio_id" id="inputServicio"
                                            class="form-select">
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Convenio</label>
                                        <select name="posee_convenio" id="inputConvenio" class="form-select">
                                            <option value="SANIDAD">SANIDAD</option>
                                            <option value="FUERA DE CONVENIO">FUERA DE CONVENIO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria_id" id="inputCategoria"
                                            class="form-select js-select-categoria">
                                            <option value="">Seleccione categoría</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Rol</label>
                                        <select name="rol_interno_id" id="inputRol"
                                            class="form-select">
                                            <option value="">Seleccione rol</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Régimen (hs)</label>
                                        <select name="regimen_horas" id="inputRegimen" class="form-select">
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
                                    <div class="col-lg-1">
                                        <label class="form-label">Horas diarias</label>
                                        <input id="inputHorasDiarias" name="horas_diarias" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Es coordinador</label>
                                        <select id="inputCordinador" name="es_coordinador" class="form-select">
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Afiliado al sindicato</label>
                                        <select id="inputAfiliado" name="es_afiliado" class="form-select">
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Noche</label>
                                        <select id="inputNoche" name="noche" class="form-select">
                                            <option value="1">SI</option>
                                            <option value="0">NO</option>
                                        </select>
                                        <!--<input type="text" id="inputNoche" class="form-control" readonly>-->
                                    </div>
                                    <div class="col-lg-1">
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
                                            <th>Fecha registro</th>
                                            <th>Novedad</th>
                                            <th>Fecha desde</th>
                                            <th>Fecha hasta</th>
                                            <th>Días/Horas</th>
                                            <th>Comprobantes</th>
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

                    <!--<div class="empleado-box p-3">
                                <div class="row g-3">

                                    <div class="col-lg-3">
                                        <label for="" class="form-label">Novedad</label>
                                        <select id="selectNovedad" name="idNovedad" class="form-select selectjs" required>
                                            <option value="">Seleccionar novedad</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2">
                                        <label class="form-label">Código Novedad</label>
                                        <input type="text" class="form-control" id="codigoFinnegans" readonly>
                                    </div>

                                    <input type="hidden" id="idNovedad" name="idNovedad" required readonly>

                                    

                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha desde</label>
                                        <input type="date" class="form-control" name="fechaDesde" id="fechaDesdeNovedad"
                                            required>
                                    </div>

                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha hasta</label>
                                        <input type="date" class="form-control" name="fechaHasta" id="fechaHastaNovedad"
                                            required>
                                    </div>

                                    <div class="col-lg-1" id="divPeriodoDias" hidden>
                                        <label class="form-label">Período (días)</label>
                                        <input id="inputDias" name="duracionDias" type="text" class="form-control">
                                    </div>

                                    <div class="col-lg-1" id="divCantidadHoras" hidden>
                                        <label class="form-label">Horas</label>
                                        <input id="inputHoras" name="horas" type="text" class="form-control">
                                    </div>

                                    <div class="col-lg-1" id="divCantidadPesos" hidden>
                                        <label class="form-label">Importe</label>
                                        <input id="inputImporte" type="text" class="form-control text-end">
                                    </div>

                                    <input type="hidden" name="cantidadFinal" id="cantidadFinal" required>

                                    <div class="col-lg-2 d-none" id="divSelectTipoVacaciones">
                                        <label class="form-label">Tipo de vacaciones</label>
                                        <select id="selectTipoVacaciones" name="tipoVacaciones" class="form-control"
                                            {{ Auth::user()->rol !== 'Administrador/a' ? 'disabled' : '' }}>
                                            <option value="">Seleccione tipo de vacaciones</option>
                                            <option value="2">Gozadas</option>
                                            <option value="3" selected
                                                {{ Auth::user()->rol !== 'Administrador/a' ? 'selected' : '' }}>
                                                Gozadas pagadas
                                            </option>
                                            <option value="4">Pagadas</option>
                                            <option value="5">Vencidas</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-1 d-none" id="divSelectAnnioVacaciones">
                                        <label class="form-label">Año</label>
                                        <input type="number" class="form-control" name="annio">
                                    </div>

                                    <div class="col-lg-1 d-none" id="divNumeroAtencion">
                                        <label class="form-label">N° de atención</label>
                                        <input type="number" class="form-control" id="numAtencion" name="numAtencion">
                                    </div>

                                    <div class="col-lg-2 d-none" id="divIngresarPacienteAtencion">
                                        <label class="form-label">Paciente</label>
                                        <input type="text" class="form-control" id="paciente"
                                            name="pacienteAtencion">
                                    </div>

                                    <div class="col-lg-4 d-none" id="divConceptoAtencion">
                                        <label class="form-label">Concepto</label>
                                        <input type="text" class="form-control" id="conceptoAtencion"
                                            name="conceptoAtencion">
                                    </div>

                                    <div class="col-lg-1 d-none" id="divCantidadCuotas">
                                        <label class="form-label">Cuotas</label>
                                        <input type="number" class="form-control" id="cantidadCuotas"
                                            name="cantidadCuotas">
                                    </div>

                                    <div class="col-lg-4 d-none">
                                        <label class="form-label">Comprobante</label>
                                        <input type="file" class="form-control">
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="form-label">Descripción/Observaciones</label>
                                        <input type="text" class="form-control" name="descripcion"
                                            placeholder="Descripción de la novedad (opcional)">
                                    </div>
                                </div>
                            </div>-->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalNovedad()">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="formCargaNovedad"
                    id="btnRegistrarNovedad">
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

<div class="modal fade" id="modalNuevaOs" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloRegNovedad">
                    <i class="fa-solid fa-plus me-2"></i> Registrar nueva OS
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalOs()"></button>
            </div>

            <div class="modal-body">
                <form id="formNuevaOs" action="{{ route('novedades.store') }}" method="POST">
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
                <button type="submit" class="btn btn-primary" form="formCargaNovedad" id="btnRegistrarOs">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Registrar OS
                </button>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/novedades/abmNovedades.js') }}"></script>
<script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
@endpush