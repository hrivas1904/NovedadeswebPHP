@extends('layouts.app')

@section('title', 'Obras Sociales')

@section('content')
<div class="container-fluid">
    <div class="container-fluid text-start">
        <h3 class="pill-heading tituloVista">OBRAS SOCIALES</h3>
    </div>
    <div class="container-fluid pt-2">
        <div class="row d-flex">
            <div class="col-xl-6 col-12 mb-3">
                <div class="card" style="border-radius:15px;">
                    <div class="card-body">
                        <table id="tb_obraSocial" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>OBRA SOCIAL</th>
                                    <th>CÓDIGO OS</th>
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
                                <span>Crear nueva obra social</span>
                            </div>
                        </div>
                        <form id="formNuevaObraSocial">
                            @csrf

                            <div class="row g-3 mb-3">

                                <div class="col-12 col-md-3 col-xl-3">
                                    <label class="form-label">CÓDIGO</label>
                                    <input type="text" class="form-control" name="codigoOs" required />
                                </div>

                                <div class="col-12 col-md-9 col-xl-9">
                                    <label class="form-label">OBRA SOCIAL</label>
                                    <input type="text" class="form-control" name="nombreOs" required />
                                </div>

                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary" id="btnCrearOs">
                                        <i class="fa-solid fa-plus me-1"></i>
                                        Crear nueva OS
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
                                        <input type="text" id="codigoOsEdit" name="codigoOsEdit" class="form-control">
                                        <input type="hidden" id="idOs" name="idOs">
                                    </div>
                                    <div class="col-lg-8 col-12">
                                        <label class="form-label">CONCEPTO</label>
                                        <input type="text" id="nombreOsEdit" name="nombreOsEdit" class="form-control">
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
<script src="{{ asset('js/ajustes/obraSociales.js') }}"></script>
@endpush