@extends('layouts.app')

@section('title', 'Mi Legajo')

@section('content')
<div id="legajoColaborador" class="d-flex flex-column gap-3">

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
                Mi legajo
            </li>

        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <h3 class="tituloVista mb-0">Mi legajo</h3>
        <button class="btn btn-primary" type="submit" form="formAltaColaborador">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
        </button>
    </div>

    <div class="card p-3">
        <div class="d-flex gap-3">
            <div class="legajo-foto-container">
                <div class="legajo-avatar">
                    <img id="previewFotoLegajo"
                        class="d-none"
                        alt="Foto del colaborador">
                    <i id="iconoFotoLegajo"
                        class="fa-solid fa-user"></i>
                </div>
                <label for="inputFotoLegajo"
                    class="legajo-foto-btn"
                    title="Cambiar foto de perfil">
                    <i class="fa-solid fa-camera"></i>
                </label>
                <input type="file"
                    id="inputFotoLegajo"
                    name="foto"
                    accept="image/jpeg,image/png,image/webp"
                    class="visually-hidden">
            </div>
            <div class="d-flex flex-column">
                <h5 class="fw-bold" style="color: var(--color-default);" id="lblNombreColab">-</h5>
                <h6 class="text-muted">Legajo N° <span class="fw-bold" id="lblLegajoColab">-</span></h6>
                <h6 class="text-muted" id="lblEstadoColab">-</h6>
                <small class="text-muted">Hacé clic en la cámara para cambiar la foto.</small>
            </div>
        </div>
    </div>

    <form id="formAltaColaborador">
        @csrf
        <div class="card-body">
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card p-3">
                        <div class="row g-3">
                            <h6 class="fw-bold text-muted">Datos personales</h6>
                            <div class="col-lg-6">
                                <label class="form-label">DNI</label>
                                <input type="text" id="inputDni" class="form-control" readonly>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">CUIL</label>
                                <input type="text" id="inputCuil" class="form-control" readonly>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Fecha nacimiento</label>
                                <input type="text" id="inputFechaNacimiento" class="form-control" readonly>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Edad</label>
                                <input type="text" id="inputEdad" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card p-3">
                        <div class="row g-3">
                            <h6 class="fw-bold text-muted">Datos de contacto</h6>
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
                    <div class="card p-3 gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <h6 class="fw-bold text-muted">Requerimientos alimentarios</h6>
                            <i class="fa-solid fa-circle-info text-primary" style="cursor: pointer;"
                                data-bs-toggle="tooltip" data-bs-placement="right"
                                title="Si no tiene ningún requerimiento específico, no
                                        seleccione nada. Si no encuentra un requerimiento específico,
                                        seleccione Otro y complete Observaciones">
                            </i>
                        </div>
                        <div class=" row g-1">
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

            <div class="row mb-4">
                <div class="col-lg-6 col-12">
                    <div class="card p-3">
                        <h6 class="mb-3 fw-bold text-muted">Contacto de emergencia 1</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre</label>
                                <input id="personaEmergencia1Edit" name="personaEmerg1" class="form-control">
                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="form-label">Teléfono</label>
                                <input id="contactoEmergencia1Edit" name="telefEmerg1" class="form-control" type="number">
                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="form-label">Parentesco</label>
                                <input id="parentescoEmergencia1Edit" name="parentesco1" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12">
                    <div class="card p-3">
                        <h6 class="mb-3 fw-bold text-muted">Contacto de emergencia 2</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre</label>
                                <input id="personaEmergencia2Edit" name="personaEmerg2" class="form-control">
                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="form-label">Teléfono</label>
                                <input id="contactoEmergencia2Edit" name="telefEmerg2" class="form-control"
                                    type="number">
                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="form-label">Parentesco</label>
                                <input id="parentescoEmergencia2Edit" name="parentesco2" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card p-3 gap-3">
                        <div class="row g-3">
                            <h6 class="fw-bold text-muted">Grupo familiar</h6>
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
                        </div>
                        <div id="divHijosEdit" class="row g-3" hidden>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card p-3">
                        <div class="row g-3">
                            <h6 class="fw-bold text-muted">Datos socioeconómicos</h6>
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
                    <div class="card p-3">
                        <div class="row g-3">
                            <h6 class="fw-bold text-muted">Datos laborales</h6>
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
@endsection

@push('scripts')
<script src="{{ asset('js/personal/administrarHijosColab.js') }}"></script>
<script src="{{ asset('js/personal/miLegajo.js') }}"></script>
@endpush