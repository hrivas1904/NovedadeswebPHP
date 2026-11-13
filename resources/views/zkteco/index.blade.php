@extends('layouts.app')

@section('content')

<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i>
                Inicio
            </a>
        </li>

        <li class="breadcrumb-item">
            Recursos Humanos
        </li>

        <li class="breadcrumb-item">
            Colaboradores
        </li>

        <li class="breadcrumb-item active" aria-current="page">
            Marcaciones reloj
        </li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3 mb-3">

    <h3 class="tituloVista mb-0">Marcaciones</h3>

    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">

        <div class="d-flex gap-3 flex-wrap">

            <div>
                <label class="text-muted">Desde</label>
                <input
                    class="form-control"
                    type="date"
                    id="filtroDesde">
            </div>

            <div>
                <label class="text-muted">Hasta</label>
                <input
                    class="form-control"
                    type="date"
                    id="filtroHasta">
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

        <div class="d-flex gap-2">

            <button
                type="button"
                class="btn btn-outline-secondary"
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

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3
                    d-flex justify-content-between align-items-center">

            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-clock me-2 text-primary"></i>
                Reporte de Tarja
            </h5>

            <button
                type="button"
                class="btn btn-outline-primary btn-sm"
                id="btnExportarTarja">
                <i class="fa-solid fa-download"></i>
                Exportar
            </button>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaMarcaciones"
                    class="table table-hover table-striped align-middle w-100">

                    <thead class="table-light">
                        <tr>
                            <th>Médico</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Tiempo trabajado</th>
                            <th>Estado</th>
                            <th>Dispositivo</th>
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

        // ============================================
        // INICIALIZAR DATATABLE
        // ============================================

        tablaMarcacionesZkteco = $('#tablaMarcaciones').DataTable({

            data: [],

            language: {
                url: '/js/es-ES.json'
            },

            processing: true,
            responsive: true,
            autoWidth: false,

            paging: true,
            pageLength: 25,
            searching: true,
            info: true,

            scrollY: '52vh',
            scrollCollapse: true,

            order: [
                [1, 'desc']
            ],

            columns: [

                // MÉDICO
                {
                    data: 'medico',
                    className: 'text-start',

                    render: function(data, type) {

                        if (type !== 'display') {
                            return data || 'Sin identificar';
                        }

                        return `
                            <span class="fw-semibold">
                                ${escapeHtml(data || 'Sin identificar')}
                            </span>
                        `;
                    }
                },

                // ENTRADA
                {
                    data: 'entrada',
                    className: 'text-center',

                    render: function(data, type) {

                        if (!data) return '-';

                        if (type !== 'display') return data;

                        return formatearFechaHora(data);
                    }
                },

                // SALIDA
                {
                    data: 'salida',
                    className: 'text-center',

                    render: function(data, type) {

                        if (!data) return '-';

                        if (type !== 'display') return data;

                        return formatearFechaHora(data);
                    }
                },

                // TIEMPO TRABAJADO
                {
                    data: 'tiempo_trabajado',
                    className: 'text-center',

                    render: function(data, type) {

                        if (!data) return '-';

                        if (type !== 'display') return data;

                        return `
                            <span class="badge bg-primary-subtle
                                         text-primary border
                                         border-primary-subtle fs-6">

                                <i class="fa-regular fa-clock me-1"></i>
                                ${escapeHtml(data)}

                            </span>
                        `;
                    }
                },

                // ESTADO
                {
                    data: 'estado',
                    className: 'text-center',

                    render: function(data, type) {

                        if (type !== 'display') return data;

                        const estados = {

                            COMPLETA: `
                                <span class="badge bg-success">
                                    Completa
                                </span>
                            `,

                            INCOMPLETA: `
                                <span class="badge bg-warning text-dark">
                                    Incompleta
                                </span>
                            `,

                            REVISAR: `
                                <span class="badge bg-danger">
                                    Revisar
                                </span>
                            `
                        };

                        return estados[data] || '-';
                    }
                },

                // DISPOSITIVO
                {
                    data: 'dispositivo',
                    className: 'text-start',

                    render: function(data, type) {

                        if (type !== 'display') return data || '';

                        return `
                            <small class="text-muted">
                                ${escapeHtml(data || '-')}
                            </small>
                        `;
                    }
                }

            ]

        });


        // ============================================
        // EVENTOS
        // ============================================

        $('#btnConsultarMarcaciones').on('click', function() {
            consultarMarcaciones();
        });

        $('#btnSincronizarZkteco').on('click', function() {
            sincronizarZkteco();
        });

        $('#btnExportarTarja').on('click', function() {
            exportarTarja();
        });

    });


    // ============================================
    // OBTENER FILTROS
    // ============================================

    function obtenerFiltrosMarcaciones() {

        return {
            desde: $('#filtroDesde').val() || '',
            hasta: $('#filtroHasta').val() || '',
            tipo_entidad: $('#filtroTipoEntidad').val() || ''
        };

    }


    // ============================================
    // CONSULTAR MARCACIONES
    // ============================================

    function consultarMarcaciones() {

        const btn = $('#btnConsultarMarcaciones');

        btn.prop('disabled', true);

        $.ajax({

            url: '/rrhh/zkteco/marcaciones',

            type: 'GET',

            dataType: 'json',

            data: obtenerFiltrosMarcaciones(),

            success: function(response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message ||
                            'No se pudieron consultar las marcaciones.'
                    });

                    return;
                }

                const marcaciones = response.data || [];

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


    // ============================================
    // SINCRONIZAR ZKTECO
    // ============================================

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


    // ============================================
    // EXPORTAR TARJA
    // ============================================

    function exportarTarja() {

        const filtros = obtenerFiltrosMarcaciones();

        const parametros = new URLSearchParams(filtros);

        const url = `/rrhh/zkteco/marcaciones/exportar?${parametros.toString()}`;

        window.location.href = url;

    }


    // ============================================
    // FUNCIONES AUXILIARES
    // ============================================

    function escapeHtml(value) {

        return $('<div>')
            .text(value ?? '')
            .html();

    }


    function formatearFechaHora(fecha) {

        if (!fecha) return '-';

        const partes = fecha.split(' ');

        if (partes.length < 2) return fecha;

        const [anio, mes, dia] = partes[0].split('-');

        const hora = partes[1].substring(0, 5);

        return `
            <div class="d-flex flex-column">
                <span class="fw-semibold">
                    ${dia}/${mes}/${anio}
                </span>

                <small class="text-muted">
                    ${hora}
                </small>
            </div>
        `;

    }
</script>
@endpush