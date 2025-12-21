@extends('layouts.app')

@section('title', 'Control de Asistencia')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="pill-heading">CONTROL DE ASISTENCIA</h3>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row d-flex">
                    <div class="col-9 d-flex justify-content-start align-items-center">
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
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <div class="card-body">
                    <table id="tb_asistencia" class="table">
                        <thead>
                            <tr>
                                <th>Fecha registro</th>
                                <th>Área</th>
                                <th>Empleado</th>
                                <th>Ingreso</th>
                                <th>Egreso</th>
                                <th>Horas</th>
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
<script src="{{ asset('js/personal/controlAsistencia.js') }}"></script>
@endpush
