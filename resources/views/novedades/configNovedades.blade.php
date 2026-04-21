@extends('layouts.app')

@section('title', 'Configuración de Novedades')

@section('content')
<div class="container-fluid">
    <div class="container-fluid text-start">
        <h3 class="pill-heading tituloVista">CONFIGURAR NOVEDADES DE SUELDO</h3>
    </div>
    <div class="container-fluid pt-2">
        <div class="row d-flex">
            <div class="col-xl-6 col-12 mb-3">
                <div class="card" style="border-radius:15px;">
                    <div class="card-body">
                        <table id="tb_configuracion" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>CÓDIGO</th>
                                    <th>NOVEDAD</th>
                                    <th>LÍMITE</th>
                                    <th>ACCIONES</th>
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
                                <span>Crear nuevo concepto de liquidación</span>
                            </div>
                        </div>

                        <form id="formNuevaNovedad"> @csrf

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-3 col-xl-3">
                                    <label class="form-label">CÓDIGO</label>
                                    <input type="text" class="form-control" name="codigoNovedad" required />
                                </div>

                                <div class="col-12 col-md-7 col-xl-7">
                                    <label class="form-label">CONCEPTO</label>
                                    <input type="text" class="form-control" name="nombreNovedad" required />
                                </div>

                                <div class="col-12 col-md-2 col-xl-2">
                                    <label class="form-label">TIPO DE VALOR</label>
                                    <select class="form-select" name="tipoValor" required>
                                        <option value="Días">DÍAS</option>
                                        <option value="Horas">HORAS</option>
                                        <option value="Pesos">PESOS</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-2 col-lg-3 col-xl-2">
                                    <label class="form-label">LIMITE</label>
                                    <input type="number" class="form-control" name="limiteNovedad" />
                                </div>

                                <div class="col-12 col-md-3 col-lg-3 col-xl-3">
                                    <label class="form-label">PARA FINNEGANS</label>
                                    <select class="form-select" name="paraFinnegans" required>
                                        <option value=1>SI</option>
                                        <option value=0>NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-12 text-end">
                                    <button type="button" class="btn btn-secondary mx-2"
                                        onclick="abrirModalCateg()">Crear categoría</button>

                                    <button type="submit" class="btn btn-primary">
                                        Crear nueva novedad
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
<div class="modal fade" id="modalEdicionNovedad" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
    data-bs-backdrop="static">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloLegajo">
                    <i class="fa-solid fa-pen-to-square"></i> Edicion de novedad
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalNovedadEdit()"></button>
            </div>

            <div class="modal-body">
                <form id="formEditNovedades">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
                            <div class="section-divider mb-3">
                                <span>Parámtrización</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2">
                                        <label class="form-label">Código</label>
                                        <input type="text" id="inputCodigo" class="form-control" readonly>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Novedad</label>
                                        <input type="text" id="inputNovedad" class="form-control">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Valor</label>
                                        <select id="selectEditTipoValor" class="form-select" required>
                                            <option value="Pesos">Pesos</option>
                                            <option value="Días">Días</option>
                                            <option value="Horas">Horas</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Cantidad máxima</label>
                                        <input type="text" id="inputCantidadMaxima" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalNovedadEdit()">
                    Atrás
                </button>
                <button class="btn btn-primary" onclick="cerrarModalNovedadEdit()">
                    Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAltaCategoria" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
    data-bs-backdrop="static">

    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content p-2">

            <div class="modal-header">
                <h5 class="modal-title" id="tituloLegajo">
                    <i class="fa-solid fa-layer-group"></i> Alta de nueva categoría
                </h5>
                <button type="button" class="btn-close" onclick="cerrarModalCateg()"></button>
            </div>

            <div class="modal-body">
                <form id="formEditNovedades">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
                            <div class="section-divider mb-3">
                                <span>Información general</span>
                            </div>
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-12">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" id="inputNombre" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalCateg()">
                    Atrás
                </button>
                <button class="btn btn-primary" onclick="cerrarModalCateg()">
                    Dar alta
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/novedades/configNovedades.js') }}"></script>
@endpush