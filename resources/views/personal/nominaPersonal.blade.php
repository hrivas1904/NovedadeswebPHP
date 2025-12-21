@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
<div class="container-fluid">
    <div class="text-start mb-4">
        <h3 class="pill-heading">GESTIÓN DE PERSONAL</h3>
    </div>

    <div class="card" style="border-radius:15px;">
        <div class="card-header">
            <div class="row d-flex">
                <div class="col-9 d-flex justify-content-start align-items-center">
                    <h6>Nómina del Personal</h6>
                    <select class="form-select mx-2" style="width: auto;">
                        <option value="1">Enfermería</option>
                        <option value="2">Limpieza</option>
                        <option value="3">RRHH</option>
                        <option value="3">Mantenimiento</option>
                        <option value="3">Sistemas</option>
                        <option value="3">Administración</option>
                    </select>
                </div>
                <div class="col-3 text-end">
                    <button type="button" class="btn btn-outline-primary">Alta de Colaborador</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tb_personal" class="table">
                    <thead>
                        <tr>
                            <th>Legajo N°</th>
                            <th>Empleado</th>
                            <th>DNI</th>
                            <th>Antigüedad</th>
                            <th>Área</th>
                            <th>Categoría</th>
                            <th>Servicio</th>
                            <th>Régimen</th>
                            <th>Horas diarias</th>
                            <th>Convenio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/personal/nominaPersonal.js') }}"></script>
@endpush