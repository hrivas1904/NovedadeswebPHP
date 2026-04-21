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
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary" id="btnCrearNovedad">
                                    <i class="fa-solid fa-plus me-1"></i>
                                    Crear nueva novedad
                                </button>
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

            <form id="formEditNovedades">
                @csrf
                <div class="modal-body">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
                            <div class="empleado-box p-3">
                                <div class="row g-3">
                                    <div class="col-lg-2 col-12">
                                        <label class="form-label">CÓDIGO</label>
                                        <input type="text" id="inputCodigoEdit" name="codigoEdit" class="form-control">
                                        <input type="hidden" id="idNovedadEdit" name="idNovedad">
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <label class="form-label">CONCEPTO</label>
                                        <input type="text" id="inputNovedadEdit" name="novedadEdit" class="form-control">
                                    </div>
                                    <div class="col-lg-2 col-12">
                                        <label class="form-label">TIPO DE VALOR</label>
                                        <select id="selectEditTipoValor" name="tipoValorEdit" class="form-select">
                                            <option value="Días">DÍAS</option>
                                            <option value="Horas">HORAS</option>
                                            <option value="Pesos">PESOS</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">LÍMITE</label>
                                        <input type="text" id="inputCantidadMaxima" name="limiteEdit" class="form-control">
                                    </div>

                                    <div class="col-12 col-md-3 col-lg-3 col-xl-3">
                                        <label class="form-label">PARA FINNEGANS</label>
                                        <select class="form-select" id="paraFinnegansEdit" name="paraFinnegansEdit">
                                            <option value=1>SI</option>
                                            <option value=0>NO</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3 col-lg-3 col-xl-3">
                                        <label class="form-label">ACTIVA</label>
                                        <select class="form-select" name="activaEdit" id="">
                                            <option value=1>SI</option>
                                            <option value=0>NO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="cerrarModalNovedadEdit()">
                        Atrás
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarNovedad">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/novedades/configNovedades.js') }}"></script>
@endpush