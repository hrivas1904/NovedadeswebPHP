@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="container-fluid">       
        <div class="text-start mb-4">
            <h3 class="pill-heading tituloVista">ADMINISTRAR USUARIOS</h3>
        </div>

        <div class="card p-1" style="border-radius: 20px;">
            <div class="card-header">
                <button type="button"></button>
            </div>

            <div class="card-body">
                <div class="table-responsive px-2">
                    <table id="tb_personal" class="table table-bordered table-hover align-middle">
                        <thead class="thead-dark">
                            <tr>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>ÁREA</th>
                                <th>USUARIO</th>
                                <th>FECHA ALTA</th>
                                <th>FECHA BAJA</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>        

    </div>
@endsection

@push('modals')

@endpush

@push('scripts')
    <script src="{{ asset('js/personal/administrarUsuarios.js') }}"></script>
@endpush
