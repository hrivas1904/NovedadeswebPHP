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
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop">
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
    <div class="modal fade" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        Dar de alta nuevo colaborador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- ===================== -->
                    <!-- DATOS PERSONALES -->
                    <!-- ===================== -->
                    <div class="row g-3 mb-4 align-items-center">

                        <div class="col-lg-3 text-center">
                            <img id="previewFoto" src="{{ asset('img/avatar-default.png') }}" class="img-thumbnail mb-2"
                                style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Foto del colaborador</label>
                            <input type="file" class="form-control" accept="image/*" onchange="previewImagen(this)">
                        </div>

                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-lg-4">
                            <label class="form-label">Nombre y apellido</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control" inputmode="numeric" maxlength="8" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">CUIL</label>
                            <input type="text" class="form-control" inputmode="numeric" maxlength="11" required>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Fecha de nac</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-lg-1">
                            <label class="form-label">Edad</label>
                            <input type="text" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">


                        <div class="col-lg-auto">
                            <label class="form-label">Estado civil</label>
                            <select class="form-select" required>
                                <option selected disabled value="">Seleccione estado civil</option>
                                <option value="1">Soltero/a</option>
                                <option value="2">Casado/a</option>
                                <option value="3">Divorciado/a</option>
                                <option value="4">Viudo/a</option>
                                <option value="5">Unión convivencial</option>
                            </select>
                        </div>
                        <div class="col-lg-auto">
                            <label class="form-label">Género</label>
                            <select class="form-select" required>
                                <option selected disabled value="">Seleccione género</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Obra social</label>
                            <select name="obra_social_id" class="form-control js-select-obra-social">
                                <option value="">Seleccione obra social</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">Cód. OS</label>
                            <input type="text" name="codigo_os" class="form-control js-input-codigo-os" readonly>
                        </div>

                    </div>

                    <!-- ===================== -->
                    <!-- CONTACTO -->
                    <!-- ===================== -->
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

                    <div class="row g-3 mb-4">
                        <div class="col-lg-auto">
                            <label class="form-label">Tipo de contrato</label>
                            <select class="form-select" required>
                                <option selected disabled value="">Seleccione género</option>
                                <option value="M">Tiempo indeterminado</option>
                                <option value="F">Plazo fijo</option>
                                <option value="F">Pasantía</option>
                                <option value="F">Práctica profesional</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Fecha inicio prueba</label>
                            <input type="date" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Fecha fin prueba</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">


                        <div class="col-lg-auto">
                            <label class="form-label">Área</label>
                            <select name="area" class="form-select js-select-area" style="width: auto;">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Servicio</label>
                            <select class="form-select">
                                <option selected disabled value="">Seleccione servicio</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-lg-3">
                            <label class="form-label">Convenio</label>
                            <select class="form-select">
                                <option selected disabled value="">Seleccione convenio</option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" class="form-select js-select-categoria">
                                <option selected disabled value="">Seleccione categoría</option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label name="rol_id" class="form-label">Rol</label>
                            <select class="form-select js-select-rol">
                                <option selected disabled value="">Seleccione rol</option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Título</label>
                            <select class="form-select">
                                <option selected disabled value="">Seleccione título</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-2">
                            <label class="form-label">Régimen</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">Hs diarias</label>
                            <input type="number" class="form-control" readonly required>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button class="btn btn-danger" data-bs-dismiss="modal">
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
