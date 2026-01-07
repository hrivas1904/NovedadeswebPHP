@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="pill-heading tituloVista">GESTIÓN DEL PERSONAL</h3>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row d-flex align-item-center">
                    <div class="col-9 d-flex justify-content-start align-items-center gap-3">
                        <h6>Nómina del Personal</h6>
                        <select id="area" name="area" class="form-select js-select-area" style="width: auto;"
                            {{ Auth::user()->rol !== 'Administrador' ? 'disabled' : '' }}>
                        </select>

                        @if (Auth::user()->rol !== 'Administrador')
                            <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                        @endif


                        <script>
                            const USER_ROLE = "{{ Auth::user()->rol }}";
                            const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                        </script>

                    </div>
                    <div class="col-3 text-end">
                        @if (Auth::user()->rol === 'Administrador/a')
                            <button type="button" class="btn btn-primario" onclick="abrirModal()">
                                <i class="fa-solid fa-user me-2"></i> Nuevo Colaborador
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive px-2">
                    <table id="tb_personal" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>LEG N°</th>
                                <th>COLABORADOR</th>
                                <th>DNI</th>
                                <th>ANTIGÜEDAD</th>
                                <th>ÁREA</th>
                                <th>CATEGORÍA</th>
                                <th>RÉG</th>
                                <th>HD</th>
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
@endsection

@push('modals')
    <div class="modal fade" id="modalAltaColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

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
                                        <div class="col-lg-3">
                                            <label class="form-label">Legajo</label>
                                            <input type="text" name="legajo" id="legajo" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-lg-7">
                                            <label class="form-label">Apellido y Nombre</label>
                                            <input type="text" name="nombre_completo" id="nombre_completo"
                                                class="form-control" required>
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
                                            <input type="text" name="dni" id="dni" class="form-control"
                                                inputmode="numeric" maxlength="8" required>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">CUIL</label>
                                            <input type="text" name="cuil" id="cuil" class="form-control"
                                                inputmode="numeric" maxlength="13" required>
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
                                            <input type="text" name="codigo_os" id="codigoOS" class="form-control"
                                                readonly>
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
                                            <input type="text" name="titulo_descripcion" id="input-descripcion"
                                                class="form-control" disabled>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">M.P.</label>
                                            <input type="text" name="matricula_profesional" id="input-mp"
                                                class="form-control" disabled>
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
                                                <option value="Tiempo indeterminado">Tiempo indeterminado</option>
                                                <option value="Plazo fijo">Plazo fijo</option>
                                                <option value="Pasantía">Pasantía</option>
                                                <option value="Práctica profesional">Práctica profesional</option>
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
                                                class="form-control">
                                        </div>
                                        <div class="col-lg-2">
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
                                        <div class="col-lg-2 col-sm-2">
                                            <label class="form-label">Convenio</label>
                                            <select name="posee_convenio" id="convenio" class="form-select">
                                                <option selected value="">Seleccione opción</option>
                                                <option value="Sanidad">Sanidad</option>
                                                <option value="Fuera de convenio">Fuera de convenio</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
                                            <label class="form-label">Categoría</label>
                                            <select name="categoria_id" id="categoria"
                                                class="form-select js-select-categoria">
                                                <option selected disabled value="">Seleccione categoría</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-sm-2" >
                                            <label class="form-label">Rol</label>
                                            <select name="rol_interno_id" id="rol_interno"
                                                class="form-select js-select-rol">
                                                <option selected disabled value="">Seleccione rol</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
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
                                        <div class="col-lg-1 col-sm-2">
                                            <label class="form-label">Horas diarias</label>
                                            <input type="text" name="horas_diarias" id="horasDiarias"
                                                class="form-control" readonly required title="Horas Diarias">
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
                                            <label class="form-label">Es coordinador</label>
                                            <select name="es_coordinador" id="es_coordinador" class="form-select">
                                                <option selected value="">Seleccione opción</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
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
                    <button type="submit" form="formAltaColaborador" class="btn btn-primary"
                        id="btnGuardarColaborador">
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
                    <h5 class="modal-title" id="tituloLegajo">
                        <i class="fa-solid fa-id-card me-2"></i> Legajo del colaborador
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
                                            <label class="form-label">Fecha egreso</label>
                                            <input type="text" id="inputFechaEgreso" class="form-control" readonly>
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
                                        <div class="col-lg-2">
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
                                        <div class="col-lg-1">
                                            <label class="form-label">Régimen (hs)</label>
                                            <input type="text" id="inputRegimen" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Horas diarias</label>
                                            <input type="text" id="inputHorasDiarias" class="form-control" readonly
                                                required title="Horas Diarias">
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Es coordinador</label>
                                            <input type="text" id="inputCordinador" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Afiliado</label>
                                            <input type="text" id="inputAfiliado" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="section-divider mb-3">
                                    <span>Historial de Novedades</span>
                                </div>
                                <div class="card table-responsive">
                                    <table id="tb_historialNovedades" class="table table-striped table-bordered table-hover align-middle nowrap">
                                        <thead class="text-start">
                                            <tr>
                                                <th>Fecha registro</th>
                                                <th>Categoría</th>
                                                <th>Novedad</th>
                                                <th>Fecha desde</th>
                                                <th>Fecha hasta</th>
                                                <th>Periodo</th>
                                                <th>Justificatorios</th>
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
                    <button class="btn btn-primary" onclick="cerrarModalLegajo()">
                        Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRegNovedadColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

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

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Registrante</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}"
                                        name="registrante" readonly>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Rol</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->rol }}" readonly>
                                </div>

                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Información del empleado</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-lg-1">
                                    <label class="form-label">Legajo</label>
                                    <input type="number" class="form-control" name="legajo" id="inputLegajo" required
                                        readonly>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Empleado</label>
                                    <input type="text" class="form-control" name="colaborador" id="inputColaborador"
                                        required readonly>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Servicio</label>
                                    <input type="text" class="form-control" name="servicio" readonly>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Antigüedad</label>
                                    <input type="text" class="form-control" name="antiguedad" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Régimen</label>
                                    <input type="text" class="form-control" name="regimen" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Hs diarias</label>
                                    <input type="text" class="form-control" name="horasDiarias" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Convenio</label>
                                    <input type="text" class="form-control" name="convenio" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Título</label>
                                    <input type="text" class="form-control" name="titulo" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Afiliado</label>
                                    <input type="text" class="form-control" name="afiliado" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Registrar novedad de sueldo</span>
                        </div>

                        <div class="empleado-box p-3">
                            <div class="row g-3">

                                <div class="col-lg-2">
                                    <label class="form-label">Tipo de novedad</label>
                                    <select id="selectCategoriaNovedades" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Novedad</label>
                                    <select id="selectNovedad" name="idNovedad" class="form-select" disabled required>
                                        <option value="">Seleccionar novedad</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Código Finnegans</label>
                                    <input type="text" class="form-control" id="codigoFinnegans" readonly>
                                </div>

                                <input type="hidden" id="idNovedad" name="idNovedad" required readonly>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha aplicación</label>
                                    <input type="date" class="form-control" name="fechaAplicacion" onchange=""
                                        required readonly>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha desde</label>
                                    <input type="date" class="form-control" name="fechaDesde" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha hasta</label>
                                    <input type="date" class="form-control" name="fechaHasta" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Período (días)</label>
                                    <input type="text" class="form-control" name="duracion" readonly required>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Descipción</label>
                                    <input type="text" class="form-control" name="descripcion">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secundario" onclick="cerrarModalNovedad()">Cancelar</button>
                    <button type="submit" class="btn btn-primario" form="formCargaNovedad" id="btnRegistrarNovedad">
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
@endpush

@push('scripts')
    <script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
@endpush
