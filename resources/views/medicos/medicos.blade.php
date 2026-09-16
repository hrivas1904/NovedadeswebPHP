@extends('layouts.app')

@section('title', 'Registro de Novedades')

@section('content')
<h3 class="tituloVista mb-3">
    MÉDICOS
</h3>

<div class="row g-3">
    <div class="col-2 d-none d-lg-block">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                        Filtros
                    </h5>
                    <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <div class="filtro-box">
                    <div class="filtro-header" id="toggleNov">
                        <span>SERVICIO</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="filtro-body d-none" id="listaNov">
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-10">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-9">
                        <div class="input-group">
                            <input class="form-control" id="buscadorMedicos" type="text" placeholder="Buscar médico...">
                            <button type="button" id="btnLimpiarBuscadorMedico" class="btn btn-secondary">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-3">
                        <button type="button" id="btnModalNuevoMedico" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalNuevoMedico">Nuevo médico</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="tbMedicos" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>MÉDICO</th>
                            <th>MP</th>
                            <th>DNI</th>
                            <th>SERVICIO</th>
                            <th>CONTACTO</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalNuevoMedico" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Alta nuevo médico</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoMedico">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="nombreMedico">
                                <label>MÉDICO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="form-floating">
                                <input class="form-control" type="number" name="cuitMedico">
                                <label>C.U.I.T./DNI</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="matriculaMedico">
                                <label>MP</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="domicilioMedico">
                                <label>DOMICILIO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="correoMedico">
                                <label>CORREO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="telefonoMedico">
                                <label>TELÉFONO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating">
                                <select class="form-control" id="selectServicioAltaMedico" name="servicioMedico">
                                    <option value="">Seleccione servicio</option>
                                </select>
                                <label>SERVICIO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-8">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="razonSocialMedico">
                                <label>RAZÓN SOCIAL</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleMedico" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="lblNombreMedico">-</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoMedico">
                    <div class="row g-4">
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="idMedico" readonly>
                                <label>ID</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="nombreMedico">
                                <label>MEDICO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="form-floating">
                                <input class="form-control" type="number" id="cuitMedico">
                                <label>C.U.I.T./DNI</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="matriculaMedico">
                                <label>MP</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="domicilioMedico">
                                <label>DOMICILIO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="correoMedico">
                                <label>CORREO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="telefonoMedico">
                                <label>TELÉFONO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-floating">
                                <select class="form-control" id="servicioMedico">
                                    <option value="">Seleccione servicio</option>
                                </select>
                                <label>SERVICIO</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="razonSocialMedico">
                                <label>RAZÓN SOCIAL</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2">
                            <div class="form-floating">
                                <input class="form-control" type="text" id="fechaAltaMedico" readonly>
                                <label>FECHA ALTA</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/medicos/medicos.js') }}">
</script>
@endpush