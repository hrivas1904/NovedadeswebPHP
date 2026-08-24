let tablaFeriados = $("#tbFeriados");
const CSRF_CRONO = $('meta[name="csrf-token"]').attr('content');

const OPCIONES_CARACTER = ['', 'Inamovible', 'Trasladable', 'Puente turístico', 'No laborable'];

$(document).ready(function () {
    tablaFeriados.DataTable({
        ajax: { url: RUTAS_CRONO_FERIADOS.listar, type: "GET", dataSrc: "data" },
        language: { url: "/js/es-ES.json" },
        paging: true,
        scrollX: false,
        scrollY: getScrollY(),
        autoWidth: true,
        dom: "tp",
        columns: [
            { data: "idEvento", visible: false },
            { data: "fechaEvento", className: "text-start",
                render: (data, type) => (type === 'sort' || type === 'type') ? data : data.substring(0, 10)
            },
            { data: "fechaEvento", className: "text-start",
                render: (data, type) => (type === 'sort' || type === 'type') ? data : obtenerNombreDia(data)
            },
            { data: "tituloEvento", className: "text-start" },
            { data: "tipoEvento", className: "text-start" },
            { data: "caracter", className: "text-start",
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return data || '';
                    const opts = OPCIONES_CARACTER.map(o =>
                        `<option value="${o}" ${(data || '') === o ? 'selected' : ''}>${o || '—'}</option>`
                    ).join('');
                    return `<select class="form-select form-select-sm select-caracter" data-id="${row.idEvento}">${opts}</select>`;
                }
            },
            { data: "verificado", className: "text-center align-middle",
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return data;
                    const checked = (data === true || data == 1) ? 'checked' : '';
                    return `<div class="form-check form-switch m-0 p-0 d-flex justify-content-start">
                        <input class="form-check-input switch-verificado" type="checkbox" role="switch"
                            data-id="${row.idEvento}" ${checked}
                            style="cursor:pointer;transform:scale(1.1);margin-left:2em;"></div>`;
                }
            },
            { data: null, className: "text-center",
                render: (data, type, row) =>
                    `<button type="button" class="btn btnEliminarEventoCrono" data-id="${row.idEvento}" style="color:red;" title="Eliminar evento">
                        <i class="fs-5 fa-regular fa-trash-can"></i></button>`
            },
        ]
    });
});

$('#modalFeriados').on('shown.bs.modal', function () {
    tablaFeriados.DataTable().columns.adjust().draw();
});

$(document).on('change', '.select-caracter', function () {
    const idEvento = $(this).data('id'), caracter = $(this).val() || null;
    $.ajax({
        url: RUTAS_CRONO_FERIADOS.actualizarCaracter,
        type: 'PATCH',
        data: { _token: CSRF_CRONO, idEvento, caracter }
    }).fail(() => Swal.fire('Error', 'No se pudo actualizar el carácter.', 'error'));
});

$(document).on('change', '.switch-verificado', function () {
    const idEvento = $(this).data('id'), verificado = $(this).is(':checked') ? 1 : 0;
    $.ajax({
        url: RUTAS_CRONO_FERIADOS.actualizarVerificado,
        type: 'PATCH',
        data: { _token: CSRF_CRONO, idEvento, verificado }
    }).fail(() => Swal.fire('Error', 'No se pudo actualizar la verificación.', 'error'));
});

$(document).on('click', '.btnEliminarEventoCrono', function () {
    const idEvento = $(this).data('id');
    Swal.fire({
        title: '¿Eliminar evento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: RUTAS_CRONO_FERIADOS.eliminar,
            type: 'POST',
            data: { idEvento, _token: CSRF_CRONO }
        }).done(res => {
            if (res.success) {
                Swal.fire('Éxito', res.message, 'success');
                tablaFeriados.DataTable().ajax.reload();
            }
        }).fail(() => Swal.fire('Error', 'No se pudo eliminar el evento.', 'error'));
    });
});