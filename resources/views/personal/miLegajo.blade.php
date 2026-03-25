@extends('layouts.app')

@section('title', 'Mi Legajo')

@section('content')
    <div class="container-fluid p-3" id="legajoColaborador">

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="mb-0 pill-heading tituloVista">
                    <i class="fa-solid fa-id-card me-2"></i> Mi Legajo
                </h5>

                <button class="btn btn-primary" type="submit" form="formAltaColaborador">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
                </button>

            </div>

            <form id="formAltaColaborador">
                @csrf
                <div class="card-body">
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
                                        <input type="text" id="inputEmail" name="correo" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" id="inputTelefono" name="telefono" class="form-control">
                                    </div>
                                    <div class="col-lg-7">
                                        <label class="form-label">Domicilio</label>
                                        <input type="text" id="inputDomicilio" name="domicilio" class="form-control">
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Localidad</label>
                                        <select id="selectLocalidad" name="localidad" class="form-select"
                                            style="width:100%">
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
                                        <input id="contactoEmergencia1Edit" name="telefEmerg1" class="form-control"
                                            type="number">
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
                                        <input id="contactoEmergencia2Edit" name="telefEmerg2" class="form-control"
                                            type="number">
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
                                        <button id="btnAgregarHijoEdit" class="btn btn-primary">
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
                                        <select id="inputEstadoCivil" name="estadoCivil" class="form-select">
                                            <option selected disabled>Seleccione estado civil</option>
                                            <option value="SOLTERO/A">SOLTERO/A</option>
                                            <option value="CASADO/A">CASADO/A</option>
                                            <option value="DIVORCIADO/A">DIVORCIADO/A</option>
                                            <option value="VIUDO/A">VIUDO/A</option>
                                            <option value="N/D">N/D</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Género</label>
                                        <select id="inputGenero" name="genero" class="form-select">
                                            <option selected disabled>Seleccione género</option>
                                            <option value="MASCULINO">MASCULINO</option>
                                            <option value="FEMENINO">FEMENINO</option>
                                            <option value="NO BINARIO">NO BINARIO</option>
                                        </select>
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
                                        <label class="form-label">Afiliado al sindicato</label>
                                        <input type="text" id="inputAfiliado" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/personal/miLegajo.js') }}"></script>
@endpush
