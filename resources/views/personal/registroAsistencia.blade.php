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
                            <div class="col-lg-2 col-6">
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                            </div>
                            <div class="col-lg-1 col-6">
                                <label class="form-label">Horario</label>
                                <input type="text" class="form-control" value="{{ now()->format('H:i') }}" readonly>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Registro de asistencia</span>
                        </div>

                        <div class="empleado-box p-3">
                            <div class="row g-3 align-items-end">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="radioDefault" id="radioDefault1">
                                    <label class="form-check-label" for="radioDefault1">
                                        Default radio
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="radioDefault" id="radioDefault2" checked>
                                    <label class="form-check-label" for="radioDefault2">
                                        Default checked radio
                                    </label>
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