@extends('layouts.app')

@section('title', 'Control de Novedades')
    
@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h2 class="text-center pill-heading">Control de Novedades</h2>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-body">
                <table id="tb_personal" class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Registro N°</th>
                            <th>Fecha registro</th>
                            <th>Área</th>
                            <th>Registrante</th>
                            <th>Empleado</th>
                            <th>Código Finnegan</th>
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
@endsection
