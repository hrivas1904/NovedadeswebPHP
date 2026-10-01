@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-column gap-3 mb-3">
        <h3 class="tituloVista mb-0">MARCACIONES</h3>
        <div class="d-flex justify-content-between">
            <div class="d-flex gap-3">
                <div>
                    <label class="text-muted">Desde</label>
                    <input class="form-control" type="date" id="filtroDesde">
                </div>
                <div>
                    <label class="text-muted">Desde</label>
                    <input class="form-control" type="date" id="filtroHasta">
                </div>
                <div>
                    <label class="text-muted">Tipo</label>
                    <select id="filtroTipoEntidad" class="form-select">
                        <option value="">Todos</option>
                        <option value="MEDICO">Médicos</option>
                        <option value="COLABORADOR">Colaboradores</option>
                    </select>
                </div>
            </div>
            <div>
                <div class="d-flex gap-2">

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        id="btnConsultarMarcaciones">

                        <i class="fa-solid fa-magnifying-glass me-1"></i>
                        Consultar marcaciones

                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnSincronizarZkteco">

                        <i class="fa-solid fa-rotate me-1"></i>
                        Sincronizar reloj

                    </button>

                </div>
            </div>
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
                                <th>Médico</th>
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

                layout: {
                    topStart: {
                        buttons: [
                            'copyHtml5',
                            'excelHtml5',
                            'csvHtml5',
                            'pdfHtml5'
                        ]
                    }
                },

                scrollY: '52vh',
                scrollCollapse: true,

                order: [
                    [3, 'desc'],
                    [4, 'desc']
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
                        data: 'medico',
                        className: 'text-start',
                        defaultContent: 'Sin identificar'
                    },

                    {
                        data: 'record_time',
                        className: 'text-start',

                        render: function(data, type) {

                            if (!data) return '-';

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


            $('#btnConsultarMarcaciones').on('click', function() {
                consultarMarcaciones();
            });

        });


        function consultarMarcaciones() {

            const btn = $('#btnConsultarMarcaciones');

            btn.prop('disabled', true);

            $.ajax({

                url: '/rrhh/zkteco/marcaciones',

                type: 'GET',

                data: {
                    desde: $('#filtroDesde').val() || null,
                    hasta: $('#filtroHasta').val() || null,
                    tipo_entidad: $('#filtroTipoEntidad').val() || null
                },

                success: function(response) {

                    const marcaciones = response.data ?? [];

                    tablaMarcacionesZkteco
                        .clear()
                        .rows.add(marcaciones)
                        .draw();
                },

                error: function(xhr) {

                    const response = xhr.responseJSON;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response?.detalle ??
                            response?.message ??
                            'No se pudieron consultar las marcaciones.'
                    });
                },

                complete: function() {

                    btn.prop('disabled', false);
                }
            });
        }

        $('#btnSincronizarZkteco').on('click', function() {
            sincronizarZkteco();
        });

        function sincronizarZkteco() {

            const btn = $('#btnSincronizarZkteco');

            btn.prop('disabled', true);

            Swal.fire({
                title: 'Sincronizando',
                text: 'Consultando marcaciones del reloj...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });


            $.ajax({

                url: '/rrhh/zkteco/sincronizar',

                type: 'POST',

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Sincronización completada',
                        text: 'Las marcaciones fueron actualizadas correctamente.'
                    });

                    // Después de sincronizar,
                    // refrescamos la tabla desde MySQL.
                    consultarMarcaciones();
                },

                error: function(xhr) {

                    const response = xhr.responseJSON;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de sincronización',
                        text: response?.detalle ??
                            response?.message ??
                            'No se pudo sincronizar el reloj.'
                    });
                },

                complete: function() {

                    btn.prop('disabled', false);
                }
            });
        }
    </script>

    @endpush