@extends('layouts.app')

@section('title', 'Control de Novedades')
    
@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h3 class="pill-heading">CONTROL DE NOVEDADES</h3>
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
                    <table id="tb_control" class="table">
                        <thead>
                            <tr>
                                <th>Reg N°</th>
                                <th>Fecha reg</th>
                                <th>Área</th>
                                <th>Empleado</th>
                                <th>Cód Fin</th>
                                <th>Tipo</th>
                                <th>Novedad</th>
                                <th>Fecha desde</th>
                                <th>Fecha hasta</th>
                                <th>Duración</th>
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
    <script src="{{ asset('js/novedades/controlNovedades.js') }}"></script>
@endpush