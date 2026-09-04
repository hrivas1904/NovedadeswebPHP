const CSRF_CRONO_CELDA = $('meta[name="csrf-token"]').attr('content');
let celdaSeleccionada = null;
let novedadesCache = null;

function cargarNovedadesActivas(callback) {
    if (novedadesCache) return callback(novedadesCache);
    $.get(RUTAS_CRONO_GRILLA.novedadesActivas, function (resp) {
        novedadesCache = resp.data;
        callback(novedadesCache);
    });
}

$(document).on('click', '.dia-cell', function () {
    const $td = $(this);
    celdaSeleccionada = {
        slotId: $td.data('slot-id'),
        fecha: $td.data('fecha'),
        idNovedadActual: $td.data('id-novedad') || null,
        updatedAt: $td.data('updated-at') || null
    };

    cargarNovedadesActivas(function (novedades) {
        const $sel = $('#selNovedadCelda').empty().append('<option value="">— Sin definir —</option>');
        novedades.forEach(n => $sel.append(`<option value="${n.ID_NOVEDAD}">${n.CODIGO_NOVEDAD} — ${n.NOMBRE}</option>`));
        $sel.val(celdaSeleccionada.idNovedadActual || '');

        $('#tituloCeldaNovedad').text(`${$td.data('empleado')} — ${celdaSeleccionada.fecha}`);
        $('#modalCeldaNovedad').modal('show');
    });
});

$('#btnGuardarCeldaNovedad').on('click', function () {
    $.ajax({
        url: RUTAS_CRONO_GRILLA.actualizarCelda,
        type: 'PATCH',
        data: {
            _token: CSRF_CRONO_CELDA,
            slot_id: celdaSeleccionada.slotId,
            fecha: celdaSeleccionada.fecha,
            id_novedad: $('#selNovedadCelda').val() || null,
            updated_at_esperado: celdaSeleccionada.updatedAt
        }
    }).done(function () {
        $('#modalCeldaNovedad').modal('hide');
        cargarGrilla();
    }).fail(function (xhr) {
        Swal.fire(xhr.status === 409 ? 'Atención' : 'Error', xhr.responseJSON?.message || 'No se pudo guardar la novedad.', xhr.status === 409 ? 'warning' : 'error');
        cargarGrilla();
    });
});