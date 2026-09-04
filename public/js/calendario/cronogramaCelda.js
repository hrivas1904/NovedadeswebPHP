const CSRF_CRONO_CELDA = $('meta[name="csrf-token"]').attr('content');
let celdaSeleccionada = null;
let novedadesCache = null;
let funcionesCache = null;
let funcionesCacheArea = null;

function cargarNovedadesActivas(callback) {
    if (novedadesCache) return callback(novedadesCache);
    $.get(RUTAS_CRONO_GRILLA.novedadesActivas, function (resp) {
        novedadesCache = resp.data;
        callback(novedadesCache);
    });
}

function cargarFuncionesActivas(idArea, callback) {
    if (funcionesCache && funcionesCacheArea === idArea) return callback(funcionesCache);
    $.get(RUTAS_CRONO_GRILLA.funcionesActivas.replace(':idArea', idArea), function (resp) {
        funcionesCache = resp.data;
        funcionesCacheArea = idArea;
        callback(funcionesCache);
    });
}

function actualizarDisponibilidadFuncion() {
    const codigoNovedad = $('#selNovedadCelda option:selected').data('codigo') || '';
    const esDiaTrabajo = (codigoNovedad === '' || codigoNovedad === 'DT');
    $('#selFuncionCelda').prop('disabled', !esDiaTrabajo);
    if (!esDiaTrabajo) $('#selFuncionCelda').val('');
}

$(document).on('click', '.dia-cell', function () {
    if (pincelActivo) return;
    const $td = $(this);
    celdaSeleccionada = {
        slotId: $td.data('slot-id'),
        fecha: $td.data('fecha'),
        idNovedadActual: $td.data('id-novedad') || null,
        updatedAt: $td.data('updated-at') || null,
        idFuncionActual: $td.data('id-funcion') || null
    };
    const idArea = $('#selectorArea').val();

    cargarNovedadesActivas(function (novedades) {
        const $selNov = $('#selNovedadCelda').empty().append('<option value="">— Sin definir —</option>');
        novedades.forEach(n => $selNov.append(`<option value="${n.ID_NOVEDAD}" data-codigo="${n.CODIGO_NOVEDAD}">${n.CODIGO_NOVEDAD} — ${n.NOMBRE}</option>`));
        $selNov.val(celdaSeleccionada.idNovedadActual || '');

        cargarFuncionesActivas(idArea, function (funciones) {
            const $selFun = $('#selFuncionCelda').empty().append('<option value="">— Ninguna —</option>');
            funciones.forEach(f => $selFun.append(`<option value="${f.id}">${f.codigo} — ${f.nombre}</option>`));
            $selFun.val(celdaSeleccionada.idFuncionActual || '');
            actualizarDisponibilidadFuncion();
        });

        $('#tituloCeldaNovedad').text(`${$td.data('empleado')} — ${celdaSeleccionada.fecha}`);
        $('#modalCeldaNovedad').modal('show');
    });
});

$(document).on('change', '#selNovedadCelda', actualizarDisponibilidadFuncion);

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
        const idFuncion = $('#selFuncionCelda').prop('disabled') ? null : ($('#selFuncionCelda').val() || null);
        $.ajax({
            url: RUTAS_CRONO_GRILLA.actualizarFuncionDia,
            type: 'PATCH',
            data: {
                _token: CSRF_CRONO_CELDA,
                slot_id: celdaSeleccionada.slotId,
                fecha: celdaSeleccionada.fecha,
                id_funcion: idFuncion
            }
        }).fail(function (xhr) {
            Swal.fire('Atención', xhr.responseJSON?.message || 'No se pudo guardar la función.', 'warning');
        }).always(function () {
            $('#modalCeldaNovedad').modal('hide');
            cargarGrilla();
        });
    }).fail(function (xhr) {
        Swal.fire(xhr.status === 409 ? 'Atención' : 'Error', xhr.responseJSON?.message || 'No se pudo guardar la novedad.', xhr.status === 409 ? 'warning' : 'error');
        cargarGrilla();
    });
});