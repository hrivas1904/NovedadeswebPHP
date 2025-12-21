@extends('layouts.app')

@section('title', 'Registro de Asistencia')

@section('content')

    <div class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="pill-heading">REGISTRO DE ASISTENCIA</h3>
        </div>

        <div class="row">
            <div class="card" style="border-radius:15px;">
                <div class="card-body">
                    <form id="formNuevaNovedad">
                        <div class="row g-3 mb-3">
                            <div class="col-lg-2 col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control" name="Legajo"
                                    value="{{ now()->format('Y-m-d') }}" readonly />
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <div class="section-divider">
                                    <span>Información del Empleado</span>
                                </div>
                            </div>
                            <div class="empleado-box">
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-1 col-md-3">
                                        <label class="form-label">Legajo N°</label>
                                        <input type="text" class="form-control" name="Legajo" />
                                    </div>
                                    <div class="col-lg-3 col-md-4">
                                        <label class="form-label">Empleado</label>
                                        <input type="text" class="form-control" name="Empleado" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">DNI</label>
                                        <input type="text" class="form-control" name="Servicio" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">Servicio</label>
                                        <input type="text" class="form-control" name="Servicio" readonly />
                                    </div>
                                    <div class="col-lg-1 col-md-3">
                                        <label class="form-label">Régimen</label>
                                        <input type="text" class="form-control" name="Regimen" readonly />
                                    </div>
                                    <div class="col-lg-1 col-md-3">
                                        <label class="form-label">Hs diarias</label>
                                        <input type="text" class="form-control" name="HorasDiarias" readonly />
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-5">
                                    <div class="col-12 mt-4 mb-3">
                                        <div class="section-divider">
                                            <span>Información de asistencia</span>
                                        </div>
                                    </div>
                                    <div class="empleado-box">
                                        <div class="row g-3 mb-3">
                                            <div class="col-lg-5 col-md-3">
                                                <label class="form-label">Acción</label>
                                                <select class="form-select" style="width: auto;" required>
                                                    <option selected>Seleccionar acción</option>
                                                    <option value="Ingreso">Ingreso</option>
                                                    <option value="Egreso">Egreso</option>
                                                </select>
                                            </div>

                                            <div class="col-lg-3 col-md-3">
                                                <label class="form-label">Horario</label>
                                                <input type="text" class="form-control"
                                                    value="{{ now()->format('H:i') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="col-lg-12 col-md-3 text-end" style="margin-top:124px;">
                                        <button type="submit" class="btn btn-primary">Marcar asistencia</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection