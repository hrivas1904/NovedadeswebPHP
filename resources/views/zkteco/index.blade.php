@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="tituloVista mb-0">MARCACIONES</h3>
        </div>
        <button type="button" class="btn btn-primary" id="btnConsultarZkteco">
            <i class="fa-solid fa-rotate me-1"></i>
            Consultar reloj
        </button>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="text-muted">
                    Estado:
                </span>

                <span class="badge text-bg-secondary" id="estadoZkteco">
                    Sin consultar
                </span>
            </div>


            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="tbMarcacionesZkteco">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>ID Usuario</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    let tablaMarcacionesZkteco = null;

    $(document).ready(function() {

        tablaMarcacionesZkteco = $('#tbMarcacionesZkteco').DataTable({

            data: [],

            language: {
                url: '/js/es-ES.json'
            },

            paging: true,
            pageLength: 25,
            searching: true,
            info: true,

            order: [
                [2, 'desc'],
                [3, 'desc']
            ],

            columns: [

                {
                    data: 'uid',
                    className: 'text-start',
                    defaultContent: '-'
                },

                {
                    data: 'user_id',
                    className: 'text-start',
                    defaultContent: '-'
                },

                {
                    data: 'record_time',
                    className: 'text-start',

                    render: function(data, type) {

                        if (!data) return '-';

                        // Para ordenar, usamos el timestamp original
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        const [fecha] = data.split(' ');

                        if (!fecha) return '-';

                        const [anio, mes, dia] = fecha.split('-');

                        return `${dia}/${mes}/${anio}`;
                    }
                },

                {
                    data: 'record_time',
                    className: 'text-start',

                    render: function(data, type) {

                        if (!data) return '-';

                        // Para ordenar, usamos el timestamp completo
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        const partes = data.split(' ');

                        return partes[1] ?? '-';
                    }
                },

                {
                    data: 'state',
                    className: 'text-start',
                    defaultContent: '-'
                },

                {
                    data: 'type',
                    className: 'text-start',
                    defaultContent: '-'
                }

            ]
        });


        $('#btnConsultarZkteco').on('click', function() {
            consultarMarcaciones();
        });

    });


    function consultarMarcaciones() {

        const btn = $('#btnConsultarZkteco');
        const estado = $('#estadoZkteco');

        btn.prop('disabled', true);

        estado
            .removeClass()
            .addClass('badge text-bg-warning')
            .text('Consultando...');


        $.ajax({

            url: '/rrhh/zkteco/marcaciones',

            type: 'GET',

            success: function(response) {

                if (!response.success) {

                    tablaMarcacionesZkteco.clear().draw();

                    estado
                        .removeClass()
                        .addClass('badge text-bg-danger')
                        .text('Error');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message ?? 'No se pudieron obtener las marcaciones.'
                    });

                    return;
                }


                const marcaciones = response.data ?? [];


                tablaMarcacionesZkteco
                    .clear()
                    .rows.add(marcaciones)
                    .draw();


                if (response.mock) {

                    estado
                        .removeClass()
                        .addClass('badge text-bg-warning')
                        .text(`Modo simulación (${marcaciones.length})`);

                } else {

                    estado
                        .removeClass()
                        .addClass('badge text-bg-success')
                        .text(`Reloj conectado (${marcaciones.length})`);
                }

            },

            error: function(xhr) {

                const response = xhr.responseJSON;

                tablaMarcacionesZkteco
                    .clear()
                    .draw();

                estado
                    .removeClass()
                    .addClass('badge text-bg-danger')
                    .text('Sin conexión');


                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: response?.detalle ??
                        response?.message ??
                        'No se pudo consultar el reloj ZKTeco.'
                });

            },

            complete: function() {

                btn.prop('disabled', false);

            }

        });

    }
</script>

@endpush