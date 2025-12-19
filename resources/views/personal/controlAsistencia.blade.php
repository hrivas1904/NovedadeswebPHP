@extends('layouts.app')

@section('title', 'Control de Asistencia')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h2 class="text-center pill-heading">Control de Asistencia</h2>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-body">
                <table id="tb_asistencia" class="table table-responsive">
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
@endsection
