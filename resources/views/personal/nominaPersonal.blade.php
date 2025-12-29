@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
<div class="container-fluid">
    <div class="text-start mb-4">
        <h3 class="pill-heading tituloVista">GESTIÓN DEL PERSONAL</h3>
    </div>

    <div class="card" style="border-radius:15px;">
        <div class="card-header">
            <div class="row d-flex">
                <div class="col-9 d-flex justify-content-start align-items-center gap-3">
                    <h6>Nómina del Personal</h6>
                    <select id="area"
                        name="area"
                        class="form-select js-select-area"
                        style="width: auto;"
                        {{ Auth::user()->rol !== 'Administrador' ? 'disabled' : '' }}>
                    </select>

                    @if(Auth::user()->rol !== 'Administrador')
                    <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                    @endif


                    <script>
                        const USER_ROLE = "{{ Auth::user()->rol }}";
                        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                    </script>

                </div>
                <div class="col-3 text-end">
                    <button type="button" class="btn btn-outline-primary" onclick="abrirModal()">
                        Nuevo Colaborador
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tb_personal" class="table">
                    <thead class="text-start">
                        <tr>
                            <th>Legajo N°</th>
                            <th>Empleado</th>
                            <th>DNI</th>
                            <th>Antigüedad</th>
                            <th>Área</th>
                            <th>Categoría</th>
                            <th>Régimen</th>
                            <th>Horas diarias</th>
                            <th>Convenio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('modals')
<div class="modal fade" id="modalAltaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">
                    Dar de alta nuevo colaborador
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
                                    <div class="col-lg-3">
                                        <label class="form-label">Legajo</label>
                                        <input type="text" name="legajo" id="legajo" class="form-control" required>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Apellido y Nombre</label>
                                        <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Estado</label>
                                        <select name="estado" id="estado" class="form-select">
                                            <option value="Activo">Activo</option>
                                            <option value="De baja">De baja</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">DNI</label>
                                        <input type="text" name="dni" id="dni" class="form-control" inputmode="numeric" maxlength="8" required>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">CUIL</label>
                                        <input type="text" name="cuil" id="cuil" class="form-control" inputmode="numeric" maxlength="13" required>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Fecha nacimiento</label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
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
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" name="telefono" id="telefono" class="form-control">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input type="text" name="domicilio" id="domicilio" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <input type="text" name="localidad" id="localidad" class="form-control">
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
                                            <option selected value="">Seleccione estado civil</option>
                                            <option value="Soltero/a">Soltero/a</option>
                                            <option value="Casado/a">Casado/a</option>
                                            <option value="Divorciado/a">Divorciado/a</option>
                                            <option value="Viudo/a">Viudo/a</option>
                                            <option value="Unión convivencial">Unión convivencial</option>
                                            <option value="N/D">N/D</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Género</label>
                                        <select name="genero" id="genero" class="form-select" required>
                                            <option selected value="">Seleccione género</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Obra social</label>
                                        <select name="obra_social_id" id="obraSocial" class="form-select">
                                            <option value="">Seleccione obra social</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Código OS</label>
                                        <input type="text" name="codigo_os" id="codigoOS" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Título</label>
                                        <select name="posee_titulo" id="select-titulo" class="form-select">
                                            <option selected value="">Seleccione...</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Descripción de Título</label>
                                        <input type="text" name="titulo_descripcion" id="input-descripcion" class="form-control" disabled>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">M.P.</label>
                                        <input type="text" name="matricula_profesional" id="input-mp" class="form-control" disabled>
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
                                    <div class="col-lg-3">
                                        <label class="form-label">Tipo de contrato</label>
                                        <select name="tipo_contrato" id="tipoContrato" class="form-select" required>
                                            <option selected="">Seleccione opción</option>
                                            <option value="Tiempo indeterminado">Tiempo indeterminado</option>
                                            <option value="Plazo fijo">Plazo fijo</option>
                                            <option value="Pasantía">Pasantía</option>
                                            <option value="Práctica profesional">Práctica profesional</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha ingreso</label>
                                        <input type="date" name="fecha_ingreso" id="fechaInicio" class="form-control" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="date" name="fecha_fin_prueba" id="fechaFin" class="form-control">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Área</label>
                                        <select name="area_id" id="area" class="form-select js-select-area">
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Servicio</label>
                                        <select name="servicio_id" id="servicio" class="form-select">
                                            <option selected value="">Seleccione servicio</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Convenio</label>
                                        <select name="posee_convenio" id="convenio" class="form-select">
                                            <option selected value="">Seleccione opción</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria_id" id="categoria" class="form-select js-select-categoria">
                                            <option selected disabled value="">Seleccione categoría</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Rol</label>
                                        <select name="rol_interno_id" id="rol_interno" class="form-select js-select-rol">
                                            <option selected disabled value="">Seleccione rol</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Régimen (hs)</label>
                                        <select name="regimen_horas" id="selectRegimen" class="form-select">
                                            <option selected value="">Seleccione régimen</option>
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
                                        <input type="text" name="horas_diarias" id="horasDiarias" class="form-control" readonly required title="Horas Diarias">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Es coordinador</label>
                                        <select name="es_coordinador" id="es_coordinador" class="form-select">
                                            <option selected value="">Seleccione opción</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Afiliado</label>
                                        <select name="es_afiliado" id="es_afiliado" class="form-select">
                                            <option selected value="">Seleccione opción</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" onclick="cerrarModal()">
                    Cancelar
                </button>
                <button type="submit" form="formAltaColaborador" class="btn btn-primary" id="btnGuardarColaborador">
                    Dar alta
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLegajoColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">
                    Dar de alta nuevo colaborador
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalLegajo()"></button>
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
                                        <input type="text" id="inputFechaNacimiento" class="form-control" readonly>
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
                                        <input type="text" id="inputEmail" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" id="inputTelefono" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input type="text" id="inputDomicilio" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <input type="text" id="inputLocalidad" class="form-control" readonly>
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
                                        <input type="text" id="inputEstadoCivil" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Género</label>
                                        <input type="text" id="inputGenero" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Obra social</label>
                                        <input type="text" id="inputObraSocial" class="form-control" readonly>
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
                                        <input type="text" id="inputDescripTitulo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">M.P.</label>
                                        <input type="text" id="inputMatricula" class="form-control" readonly>
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
                                        <input type="text" id="inputTipoContrato" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha ingreso</label>
                                        <input type="text" id="inputFechaIngreso" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="text" id="inputFechaFinPrueba" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Antiguedad</label>
                                        <input type="text" id="inputAntiguedad" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Área</label>
                                        <input type="text" id="inputArea" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Servicio</label>
                                        <input type="text" id="inputServicio" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Convenio</label>
                                        <input type="text" id="inputConvenio" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Categoría</label>
                                        <input type="text" id="inputCategoria" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Rol</label>
                                        <input type="text" id="inputRol" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Régimen (hs)</label>
                                        <input type="text" id="inputRegimen" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-1">
                                        <label class="form-label">Horas diarias</label>
                                        <input type="text" id="inputHorasDiarias" class="form-control" readonly required title="Horas Diarias">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Es coordinador</label>
                                        <input type="text"id="inputCordinador" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Afiliado</label>
                                        <input type="text" id="inputAfiliado" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" onclick="abrirModalLegajo()">
                    Cancelar
                </button>
                <button type="submit" form="formAltaColaborador" class="btn btn-primary" id="btnGuardarColaborador">
                    Dar alta
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
@endpush