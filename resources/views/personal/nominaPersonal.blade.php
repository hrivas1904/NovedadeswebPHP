@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="pill-heading">GESTIÓN DE PERSONAL</h3>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row d-flex">
                    <div class="col-9 d-flex justify-content-start align-items-center">
                        <h6>Nómina del Personal</h6>
                        <select id="area" name="area" class="form-select mx-2 js-select-area" style="width: auto;">
                            <option value=""></option>
                        </select>
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
                        <thead>
                            <tr>
                                <th>Legajo N°</th>
                                <th>Empleado</th>
                                <th>DNI</th>
                                <th>Antigüedad</th>
                                <th>Área</th>
                                <th>Categoría</th>
                                <th>Servicio</th>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="formAltaColaborador">
                        <div class="row g-3 mb-4 d-flex align-item-center">
                            <div class="col-auto">
                                <div class="empleado-box">
                                    <div class="row g-3 mb-1 d-flex align-item-center">
                                        <div class="col-lg-auto text-start">
                                            <img id="previewFoto" src="{{ asset('img/avatar-default.png') }}"
                                                class="img-thumbnail mb-2"
                                                style="width: 120px; height: 120px; object-fit: cover;">
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Foto del colaborador</label>
                                            <input type="file" class="form-control" accept="image/*"
                                                onchange="previewImagen(this)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="col-12 mb-4">
                                    <div class="section-divider">
                                        <span>Datos Personales</span>
                                    </div>
                                </div>
                                <div class="empleado-box">
                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-4">
                                            <label class="form-label">Apellido y Nombre</label>
                                            <input type="text" class="form-control" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">DNI</label>
                                            <input type="text" class="form-control" inputmode="numeric" maxlength="8"
                                                required>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">CUIL</label>
                                            <input type="text" class="form-control" inputmode="numeric" maxlength="11"
                                                required>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha nacimiento</label>
                                            <input type="date" class="form-control" required>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Edad</label>
                                            <input type="text" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="col-12 mb-4">
                                    <div class="section-divider">
                                        <span>Datos Socioeconómicos</span>
                                    </div>
                                </div>
                                <div class="empleado-box">
                                    <div class="row g-3 mb-4">
                                        <div class="col-lg-2">
                                            <label class="form-label">Estado civil</label>
                                            <select class="form-select" required>
                                                <option selected value="">Seleccione estado civil</option>
                                                <option value="1">Soltero/a</option>
                                                <option value="2">Casado/a</option>
                                                <option value="3">Divorciado/a</option>
                                                <option value="4">Viudo/a</option>
                                                <option value="5">Unión convivencial</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label">Obra social</label>
                                            <select name="obra_social_id" id="obraSocial" class="form-control">
                                                <option value="">Seleccione obra social</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Cód. OS</label>
                                            <input type="text" id="codigoOS" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Título</label>
                                            <select class="form-select">
                                                <option selected value="">Seleccione opción</option>
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="col-12 mb-4">
                                    <div class="section-divider">
                                        <span>Datos de Contacto</span>
                                    </div>
                                </div>
                                <div class="empleado-box">
                                    <div class="row g-3 mb-4">
                                        <div class="col-lg-4">
                                            <label class="form-label">Correo electrónico</label>
                                            <input type="email" class="form-control" required>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control">
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="form-label">Domicilio</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Localidad</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 mb-3">
                                <div class="section-divider">
                                    <span>Datos Laborales</span>
                                </div>
                            </div>
                            <div class="empleado-box">
                                <div class="row g-3 mb-4">
                                    <div class="col-lg-2">
                                        <label class="form-label">Tipo de contrato</label>
                                        <select class="form-select" required>
                                            <option selected value="">Seleccione tipo de contrato</option>
                                            <option value="M">Tiempo indeterminado</option>
                                            <option value="F">Plazo fijo</option>
                                            <option value="F">Pasantía</option>
                                            <option value="F">Práctica profesional</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha inicio prueba</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Fecha fin prueba</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="col-lg-auto">
                                        <label class="form-label">Área</label>
                                        <select name="area" class="form-select js-select-area" style="width: auto;">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Servicio</label>
                                        <select class="form-select">
                                            <option selected value="">Seleccione servicio</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Convenio</label>
                                        <select class="form-select">
                                            <option selected value="">Seleccione opción</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-lg-auto">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria_id" class="form-select js-select-categoria">
                                            <option selected disabled value="">Seleccione categoría</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2">
                                        <label name="rol_id" class="form-label">Rol</label>
                                        <select class="form-select js-select-rol">
                                            <option selected disabled value="">Seleccione rol</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-auto">
                                        <label class="form-label">Régimen</label>
                                        <select name="regimen" id="selectRegimen" class="form-select">
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
                                        <input type="text" id="horasDiarias" class="form-control" readonly required>
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
                    <button class="btn btn-primary">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
@endpush
