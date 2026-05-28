@extends('layouts.app')

@section('title', 'Tarja')

@section('content')

    <div style="font-size: 1.2rem" class="container-fluid">
        <div class="text-start mb-4">
            <h3 class="tituloVista">TARJA</h3>
        </div>
        <div class="card p-2">
            <div class="card-header">
                <table id="tb_asistencias" class="table table-hover table-bordered align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>MÉDICO</th>
                            <th>FECHA</th>
                            <th>TURNO</th>
                            <th>ENTRADA</th>
                            <th>SALIDA</th>
                            <th>TOTAL</th>
                            <th>ING. TARDE</th>
                            <th>EGR. TARDE</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>

                </table>
            </div>
            <div class="card-body">

            </div>
        </div>
    @endsection

    @push('scripts')
        <script src="{{ asset('js/personal/marcarAsistencia.js') }}"></script>
    @endpush
