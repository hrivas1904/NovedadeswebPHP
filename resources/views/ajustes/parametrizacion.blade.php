@extends('layouts.app')

@section('title', 'Parametrización')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-start gap-3 my-3">
            <div class="icon-box">
                <img src="{{ asset('img/icons/ajustes.png') }}" style="height: 32px;" alt="Logo ajustes">
            </div>
            <div>
                <h3 class="tituloVista mb-0">PARAMETRIZACIÓN</h3>
                <p class="mb-0 text-muted">Configuración de Áreas, Servicios y Categorías.</p>
            </div>
        </div>

        <div class="row d-flex g-3">
            <div class="col-lg-6 col-12">
                <div class="card p-2" style="height: 350px";>
                    <div class="row d-flex">
                        <div class="col-xl-7 col-12 mb-3">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">
                                    <table id="tb_areas" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>ÁREA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5 col-12">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">

                                    <div class="col-12 mb-4">
                                        <div class="section-divider">
                                            <span>Crear nueva área</span>
                                        </div>
                                    </div>
                                    <form id="formNuevaArea">
                                        @csrf

                                        <div class="row g-3 mb-3">

                                            <div class="col-12">
                                                <label class="form-label">ÁREA</label>
                                                <input type="text" class="form-control" name="nombreArea" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearArea">
                                                    <i class="fa-solid fa-plus me-1"></i>
                                                    Crear nueva área
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="card p-2" style="height: 350px";>
                    <div class="row d-flex">
                        <div class="col-xl-7 col-12 mb-3">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">
                                    <table id="tb_categ" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>CATEGORÍA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5 col-12">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">

                                    <div class="col-12 mb-4">
                                        <div class="section-divider">
                                            <span>Crear nueva categoría</span>
                                        </div>
                                    </div>
                                    <form id="formNuevaCategoria">
                                        @csrf

                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label class="form-label">CATEGORÍA</label>
                                                <input type="text" class="form-control" name="nombreCateg" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearCateg">
                                                    <i class="fa-solid fa-plus me-1"></i>
                                                    Crear nueva categoría
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card p-2 mb-3" style="height: 350px";>
                    <div class="row d-flex">
                        <div class="col-xl-6 col-12 mb-3">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">
                                    <table id="tb_servicios" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>SERVICIO</th>
                                                <th>ID ÁREA</th>
                                                <th>ÁREA VINCULADA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-12">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">

                                    <div class="col-12 mb-4">
                                        <div class="section-divider">
                                            <span>Crear nuevo servicio</span>
                                        </div>
                                    </div>
                                    <form id="formNuevoServicio">
                                        @csrf

                                        <div class="row g-3 mb-3">

                                            <div class="col-12 col-md-5 col-xl-5">
                                                <label class="form-label">ÁREA</label>
                                                <select id="selectAreaServicios" name="area" class="form-select w-100" required></select>
                                            </div>

                                            <div class="col-12 col-md-7 col-xl-7">
                                                <label class="form-label">SERVICIO</label>
                                                <input type="text" class="form-control" name="servicio" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearServicio">
                                                    <i class="fa-solid fa-plus me-1"></i>
                                                    Crear nuevo servicio
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="modalEdicionArea" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de área
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalAreaEdit()"></button>
                </div>

                <form id="formEditArea">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idAreaEdit" name="idAreaEdit" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreAreaEdit" name="nombreAreaEdit"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarArea">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarArea">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdicionCateg" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de categoría
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalCategEdit()"></button>
                </div>

                <form id="formEditCateg">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idCategEdit" name="idCategEdit"
                                                class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreCategEdit" name="nombreCategEdit"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarCateg">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarCateg">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdicionServicio" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de servicios
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalServEdit()"></button>
                </div>

                <form id="formEditServicio">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idServEdit" name="idServEdit" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreServEdit" name="nombreServEdit"
                                                class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label for="areaServEdit" class="form-label">
                                                ÁREA VINCULADA
                                            </label>

                                            <select id="areaServEdit" name="areaServEdit" class="form-select" style="width: 100%;">
                                                <option value="">-- Seleccione un área --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarServ">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarServ">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/ajustes/parametrizacion.js') }}"></script>
@endpush
