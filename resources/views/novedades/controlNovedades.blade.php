@extends('layouts.app')

@section('title', 'Control de Novedades')

@section('content')
<div class="container-fluid">
    <div class="text-start mb-3">
        <h3 class="pill-heading tituloVista">CONTROL DE NOVEDADES</h3>
    </div>

    <div class="card" style="border-radius:15px;">
        <div class="card-header">
            <div class="row d-flex">
                <div class="col-9 d-flex justify-content-start align-items-center">
                    <select id="area" name="area" class="form-select js-select-area" style="width: auto;"
                        {{ Auth::user()->rol !== 'Administrador/a' ? 'hidden' : '' }}>
                    </select>

                    @if (Auth::user()->rol !== 'Administrador/a')
                    <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                    @endif


                    <script>
                        const USER_ROLE = "{{ Auth::user()->rol }}";
                        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                    </script>
                </div>
                <div class="col-3 text-end">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <div class="card-body">
                <table id="tb_control" class="table table-striped table-bordered table-hover align-middle nowrap">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>FECHA</th>
                            <th>AREA</th>
                            <th>REGISTRANTE</th>
                            <th>COLABORADOR</th>
                            <th>LEGAJO</th>
                            <th>CODIGO</th>
                            <th>NOVEDAD</th>
                            <th>DESDE</th>
                            <th>HASTA</th>
                            <th>FECHAAPLICACION</th>
                            <th>CANTIDAD</th>
                            <th>VALOR2</th>
                            <th>CENTROCOSTO</th>
                            <th>DESCRIPCION</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/novedades/controlNovedades.js') }}"></script>
@endpush