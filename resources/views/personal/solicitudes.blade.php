@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
    <div class="container-fluid">

        <div class="text-start mb-4">
            <h3 class="pill-heading tituloVista">SOLICITUDES PRÉSTAMOS Y ADELANTOS</h3>
        </div>

        <button type="button" id="btnAbrirModalSolicitud" class="btn-primario mb-3"> Nueva solicitud </button>

        <div class="card" style="border-radius:15px;">
            <div class="my-2">
            </div>
            @if (Auth::user()->rol === 'Administrador/a')
                <div class="table-responsive px-2">
                    <table id="tb_personal" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>ÁREA</th>
                                <th>TIPO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endif
        </div>

    </div>
@endsection