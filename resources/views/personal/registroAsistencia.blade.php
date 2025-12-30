@extends('layouts.app')

@section('title', 'Registro de Asistencia')

@section('content')

<div class="container-fluid">
    <div class="text-start mb-4">
        <h3 class="pill-heading tituloVista">REGISTRO DE ASISTENCIA</h3>
    </div>

    <div class="row">
        <div class="col-lg-9 col-md-6">
            <div class="card" style="border-radius:15px;">
                <div class="card-body">
                    <form id="formNuevaNovedad">

                        <div class="row g-3 mb-4">
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" readonly>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Información del empleado</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">

                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label">Legajo</label>
                                    <input type="text" class="form-control" name="Legajo">
                                </div>

                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label">Empleado</label>
                                    <input type="text" class="form-control" name="Empleado" readonly>
                                </div>

                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label">DNI</label>
                                    <input type="text" class="form-control" name="DNI" readonly>
                                </div>

                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label">Servicio</label>
                                    <input type="text" class="form-control" name="Servicio" readonly>
                                </div>

                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label">Régimen</label>
                                    <input type="text" class="form-control" name="Regimen" readonly>
                                </div>

                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label">Hs diarias</label>
                                    <input type="text" class="form-control" name="HorasDiarias" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Registro de asistencia</span>
                        </div>

                        <div class="empleado-box p-3">
                            <div class="row g-3 align-items-end">

                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label">Acción</label>
                                    <select class="form-select" required>
                                        <option value="">Seleccionar acción</option>
                                        <option value="Ingreso">Ingreso</option>
                                        <option value="Egreso">Egreso</option>
                                    </select>
                                </div>

                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label">Horario</label>
                                    <input type="text"
                                        class="form-control"
                                        value="{{ now()->format('H:i') }}">
                                </div>

                                <div class="col-lg-3 col-md-3 text-end">
                                    <button type="submit" class="btn btn-primario mt-4">
                                        <i class="fa-solid fa-clock me-1"></i> Marcar asistencia
                                    </button>
                                </div>

                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
        <div class="col-lg-auto col-md-auto"></div>
    </div>

</div>

@endsection