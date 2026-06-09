@extends('layouts.app')

@section('title', 'Obras Sociales')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-start gap-3 my-3">
            <div class="icon-box">
                <img src="{{ asset('img/icons/logo-os.png') }}" style="height: 32px;" alt="Saludo">
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
                                    <form id="formNuevaObraSocial">
                                        @csrf

                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label class="form-label">CATEGORÍA</label>
                                                <input type="text" class="form-control" name="nombreOs" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearOs">
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
                                    <form id="formNuevaObraSocial">
                                        @csrf

                                        <div class="row g-3 mb-3">

                                            <div class="col-12 col-md-5 col-xl-5">
                                                <label class="form-label">ÁREA</label>
                                                <select id="selectAreaServicios" class="form-select w-100"></select>
                                                <input type="hidden" name="idAreaServicio">
                                            </div>

                                            <div class="col-12 col-md-7 col-xl-7">
                                                <label class="form-label">SERVICIO</label>
                                                <input type="text" class="form-control" name="nombreOs" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearOs">
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
    <div class="modal fade" id="modalEdicionOs" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
        data-bs-backdrop="static">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloLegajo">
                        <i class="fa-solid fa-pen-to-square"></i> Edicion de obra social
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalOsEdit()"></button>
                </div>

                <form id="formEditObraSocial">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">CÓDIGO</label>
                                            <input type="text" id="codigoOsEdit" name="codigoOsEdit"
                                                class="form-control">
                                            <input type="hidden" id="idOs" name="idOs">
                                        </div>
                                        <div class="col-lg-8 col-12">
                                            <label class="form-label">CONCEPTO</label>
                                            <input type="text" id="nombreOsEdit" name="nombreOsEdit"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="cerrarModalOsEdit()">
                            Atrás
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarOs">
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
