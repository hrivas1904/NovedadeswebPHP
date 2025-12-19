@extends('layouts.app')

@section('title', 'Configuraciones')

@section('content')
    <div class="container-fluid ajustes-simple">

        <div class="row container-md">
            <div class="row">
                <div class="col-12">
                    <div class="text-start">
                        <h2 class="text-center pill-heading">Ajustes generales</h2>
                    </div>
                </div>
            </div>

            <div class="text-start row g-4">
                <div class="col-md-6">
                    <div class="card ajustes-card" style="border-radius: 20px;">
                        <div class="card-header">
                            Preferencias del sistema
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Tema del sistema</label>
                                    <select class="form-select">
                                        <option selected>Claro</option>
                                        <option>Oscuro</option>
                                        <option>Automático</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Mes operativo actual</label>
                                    <input type="month" class="form-control">
                                    <small class="text-muted">
                                        Define el mes activo para cronogramas, novedades y reportes.
                                    </small>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card ajustes-card" style="border-radius: 20px;">
                        <div class="card-header">
                            Perfil del usuario
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" value="Héctor Rivas">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" value="usuario@hospital.com">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Idioma</label>
                                    <select class="form-select">
                                        <option selected>Español</option>
                                        <option>English</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card ajustes-card" style="border-radius: 20px;">
                        <div class="card-header">
                            Seguridad y actividad
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Tiempo de inactividad</label>
                                    <select class="form-select">
                                        <option>15 minutos</option>
                                        <option selected>30 minutos</option>
                                        <option>1 hora</option>
                                        <option>4 horas</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Guardar logs del sistema</label>
                                    <select class="form-select">
                                        <option selected>Sí</option>
                                        <option>No</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Registrar movimientos de usuario</label>
                                    <select class="form-select">
                                        <option selected>Sí</option>
                                        <option>No</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-danger">Cancelar</button>
                    <button class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </div>

    </div>

@endsection
