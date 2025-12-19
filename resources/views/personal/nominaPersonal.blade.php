@extends('layouts.app')

@section('title', 'Nómina de personal')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h2 class="text-center pill-heading">Gestión del personal</h2>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tb_personal" class="table">
                        <thead>
                            <tr>
                                <th>Legajo N°</th>
                                <th>Empleado</th>
                                <th>DNI</th>
                                <th>CUIL</th>
                                <th>Fecha ingreso</th>
                                <th>Antiguüedad</th>
                                <th>Área</th>
                                <th>Categoría</th>
                                <th>Área</th>
                                <th>Servicio</th>
                                <th>Régimen</th>
                                <th>Horas diarias</th>
                                <th>Convenio</th>
                                <th>Teléfono</th>
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