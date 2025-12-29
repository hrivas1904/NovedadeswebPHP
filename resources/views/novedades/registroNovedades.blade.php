@extends('layouts.app')

@section('title', 'Registro de Novedades')

@section('content')
<div class="container-fluid">
    <div class="container-fluid text-start">
        <h3 class="pill-heading tituloVista">REGISTRO DE NOVEDADES DE SUELDO</h3>
    </div>
    <div class="container-fluid py-2">
        <div class="card p-1" style="border-radius: 20px;">

            <div class="card-body">
                <form id="formCargaNovedad">
                    <div class="col-12 mb-4">
                        <div class="section-divider">
                            <span>Información general</span>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-lg-auto col-md-3">
                            <label class="form-label">Fecha de carga</label>
                            <input type="text" class="form-control" name="fechaCarga"
                                value="{{ now()->format('Y-m-d') }}" readonly />
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="form-label">Registrante</label>
                            <input type="text" class="form-control" name="registrante" value="{{ Auth::user()->name }}" readonly />
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="form-label">Rol</label>
                            <input type="text" class="form-control" name="rolRegistrante" value="{{ Auth::user()->rol }}" readonly />
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="col-12 mt-4 mb-3">
                                <div class="section-divider">
                                    <span>Información del Empleado</span>
                                </div>
                            </div>
                            <div class="empleado-box">
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-2 col-md-3">
                                        <label class="form-label">Legajo N°</label>
                                        <input type="number" class="form-control" name="legajo" />
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <label class="form-label">Empleado</label>
                                        <select id="selectEmpleado" class="form-control">
                                            <option selected value="">Seleccione empleado</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <label class="form-label">Servicio</label>
                                        <input type="text" class="form-control" name="servicio" readonly />
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label">Antigüedad</label>
                                        <input type="text" class="form-control" name="antiguedad" readonly />
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-lg-2 col-md-3">
                                        <label class="form-label">Régimen</label>
                                        <input type="text" class="form-control" name="regimen" readonly />
                                    </div>
                                    <div class="col-lg-2 col-md-3">
                                        <label class="form-label">Hs diarias</label>
                                        <input type="text" class="form-control" name="horasDiarias" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-4">
                                        <label class="form-label">Convenio</label>
                                        <input type="text" class="form-control" name="convenio" readonly />
                                    </div>
                                    <div class="col-lg-2 col-md-4">
                                        <label class="form-label">Título</label>
                                        <input type="text" class="form-control" name="titulo" readonly />
                                    </div>
                                    <div class="col-lg-2 col-md-4">
                                        <label class="form-label">Afiliado</label>
                                        <input type="text" class="form-control" name="afiliado" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="col-12 mt-4 mb-3">
                                <div class="section-divider">
                                    <span>Registrar novedad de sueldo</span>
                                </div>
                            </div>
                            <div class="empleado-box">
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-4 col-md-3">
                                        <label class="form-label">Tipo de novedad</label>
                                        <select id="selectCategoria" class="form-select">
                                            <option value="">Seleccionar tipo de novedad</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-5 col-md-4">
                                        <label class="form-label">Novedad</label>
                                        <select id="selectNovedad" class="form-select" disabled>
                                            <option value="">Seleccionar novedad</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-2">
                                        <label class="form-label">Código Finnegans</label>
                                        <input type="text" id="codigoFinnegans" name="codigoNovedad" class="form-control" readonly>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-4 col-md-4">
                                            <label class="form-label">Fecha desde</label>
                                            <input type="date" class="form-control" name="fechaDesde" />
                                        </div>
                                        <div class="col-lg-4 col-md-4">
                                            <label class="form-label">Fecha hasta</label>
                                            <input type="date" class="form-control" name="fechaHasta" />
                                        </div>
                                        <div class="col-lg-3 col-md-3">
                                            <label class="form-label">Periodo (días)</label>
                                            <input type="text" class="form-control" name="duracion" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="section-divider">
                                <span>Información adicional</span>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-lg-12 col-md-12">
                                <label class="form-label">Observaciones</label>
                                <input type="text" class="form-control" name="Cuil" />
                            </div>
                        </div>
                </form>
            </div>

        </div>
        <div class="p-4 d-flex gap-3 justify-content-end">
            <button type="button" class="btn btn-primary" onsubmit="formCargaNovedad">Registrar novedad</button>
            <button type="button" class="btn btn-danger">Cancelar</button>
        </div>

    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/novedades/registroNovedades.js') }}"></script>
@endpush