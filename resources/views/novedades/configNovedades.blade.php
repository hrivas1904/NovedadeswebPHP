@extends('layouts.app')

@section('title', 'Configuración de Novedades')

@section('content')
<div class="container-fluid">
    <div class="container-fluid text-start">
        <h3 class="pill-heading tituloVista">CONFIGURAR NOVEDADES DE SUELDO</h3>
    </div>
    <div class="container-fluid pt-3">
        <div class="row d-flex">
            <div class="col-lg-6 col-sm-12 mb-3">
                <div class="card" style="border-radius:15px;">
                    <div class="card-body">
                        <table id="tb_configuracion" class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>Cód Finnegan</th>
                                    <th>Novedad</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-sm-12">
                <div class="card" style="border-radius:15px;">
                    <div class="card-body">

                        <div class="col-12 mb-4">
                            <div class="section-divider">
                                <span>Configurar nueva novedad</span>
                            </div>
                        </div>

                        <form id="formNuevaNovedad">
                            <div class="row g-3 mb-3">

                                <div class="col-lg-4 col-md-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" required>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                    <label class="form-label">Cód Finnegan</label>
                                    <input type="text" class="form-control" name="CodigoFinnegan" required />
                                </div>
                                <div class="col-lg-5 col-md-3">
                                    <label class="form-label">Novedad</label>
                                    <input class="form-control" require>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-lg-12 col-md-3 text-end">
                                    <button type="button" class="btn btn-secondary mx-2">Crear categoría</button>
                                    <button type="submit" class="btn btn-primary">Crear nueva novedad</button>
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

@push('scripts')
<script src="{{ asset('js/novedades/configNovedades.js') }}"></script>
@endpush