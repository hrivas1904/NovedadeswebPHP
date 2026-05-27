@extends('layouts.app')

@section('title', 'Registro de Asistencia')

@section('content')

    <div style="font-size: 1.2rem" class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="tituloVista">MARCAR ASISTENCIA</h3>
        </div>

        <div class="row d-flex gap-3">
            <div class="col-lg-4 col-12">
                <div class="card" style="border-radius:15px;">
                    <div class="card-body">
                        <form id="formRegistrarAsistencia">
                            <div class="row g-3 mb-4">
                                <div class="col-lg-6 col-6">
                                    <label class="form-label">Fecha</label>
                                    <input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                                </div>
                                <div class="col-lg-6 col-6">
                                    <label class="form-label">Hora</label>
                                    <input type="text" class="form-control" value="{{ now()->format('H:i') }}" readonly>
                                </div>
                            </div>

                            <div class="section-divider mb-3">
                                <span>Registro de asistencia</span>
                            </div>

                            <div class="card p-3 gap-3 mb-3">
                                <label class="form-check-label" for="radioDefault1">
                                    SELECCIONAR TURNO
                                </label>
                                <div class="row d-flex">
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="radioDefault"
                                            id="radioDefault1">
                                        <label class="form-check-label" for="radioDefault1">
                                            Mañana
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="radioDefault"
                                            id="radioDefault2" checked>
                                        <label class="form-check-label" for="radioDefault2">
                                            Tarde
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="radioDefault"
                                            id="radioDefault2" checked>
                                        <label class="form-check-label" for="radioDefault2">
                                            Noche
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="card p-3 gap-3 mb-3">
                                <label class="form-check-label" for="radioDefault1">
                                    SELECCIONAR SERVICIO
                                </label>
                                <div class="row d-flex gap-3">
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="servicio"
                                            id="servicioGuardiaAd">
                                        <label class="form-check-label" for="servicioGuardiaAd">
                                            Guardia adulto
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-6 col-12">
                                        <input class="form-check-input" type="radio" name="servicio"
                                            id="servicioGuardiaPed">
                                        <label class="form-check-label" for="servicioGuardiaPed">
                                            Guardia pediátrica
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="servicio"
                                            id="servicioUtiAd" checked>
                                        <label class="form-check-label" for="servicioUtiAd">
                                            UTI Adulto
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="servicio"
                                            id="servicioUtiPed" checked>
                                        <label class="form-check-label" for="servicioUtiPed">
                                            UTI Pediátrica
                                        </label>
                                    </div>
                                    <div class="form-check col-lg-4 col-12">
                                        <input class="form-check-input" type="radio" name="servicio"
                                            id="servicioUtiNeo" checked>
                                        <label class="form-check-label" for="servicioUtiNeo">
                                            UTI Neo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row d-flex">
                                <div class="col-6">
                                    <button type="button" class="btn btn-primary w-100" id="btnIngresarAsistencia">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i> INGRESO</button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-danger w-100" id="btnEgresarAsistencia">
                                        EGRESO <i class="fa-solid fa-arrow-right-to-bracket"></i> </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-12">

            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/personal/marcarAsistencia.js') }}"></script>
@endpush