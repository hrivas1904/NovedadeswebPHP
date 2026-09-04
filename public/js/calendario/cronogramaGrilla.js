const CSRF_CRONO_GRILLA = $('meta[name="csrf-token"]').attr('content');
let periodoActualInfo = null;
let asignacionesPorSlotFecha = {};

function actualizarEstadoBoton() {
    const listo = $('#selectorMesCrono').val() && $('#selectorArea').val();
    $('#btnAgregarPuesto').prop('disabled', !listo);
}

function cargarGrilla() {
    actualizarEstadoBoton();
    const periodo = $('#selectorMesCrono').val();
    const idArea = $('#selectorArea').val();
    const idServicio = $('#selectorServicio').val() || '';

    if (!periodo || !idArea) {
        $('#contenedorGrilla').html('<p class="text-muted text-center mb-0">Elegí período y área para ver la grilla.</p>');
        return;
    }

    periodoActualInfo = periodosDisponibles.find(p => p.periodo === periodo) || null;

    $.when(
        $.get(RUTAS_CRONO_GRILLA.grilla, { periodo, id_area: idArea, id_servicio: idServicio }),
        $.get(RUTAS_CRONO_GRILLA.asignacionesDia, { periodo, id_area: idArea, id_servicio: idServicio })
    ).done(function (respPuestos, respAsignaciones) {
        asignacionesPorSlotFecha = {};
        respAsignaciones[0].data.forEach(a => {
            asignacionesPorSlotFecha[`${a.slot_id}_${a.fecha}`] = a;
        });
        renderGrilla(respPuestos[0].data);
    });
}

function renderGrilla(puestosFilas) {
    const $cont = $('#contenedorGrilla');
    if (!puestosFilas.length) {
        $cont.html('<p class="text-muted text-center mb-0">No hay puestos definidos para este período/área/servicio. Usá "Agregar puesto" para empezar.</p>');
        return;
    }
    if (!periodoActualInfo) {
        $cont.html('<p class="text-muted text-center mb-0">No se pudo determinar la cantidad de días del período.</p>');
        return;
    }

    const periodo = $('#selectorMesCrono').val();
    const dias = periodoActualInfo.dias;
    const primerDia = periodoActualInfo.primer_dia;
    const DOW_CORTO = ['D','L','M','M','J','V','S'];

    const puestos = {};
    puestosFilas.forEach(f => {
        if (!puestos[f.puesto_id]) {
            puestos[f.puesto_id] = {
                id: f.puesto_id, turno_nombre: f.turno_nombre, turno_codigo: f.turno_codigo,
                hora_inicio: f.hora_inicio, hora_fin: f.hora_fin, cruza: f.cruza,
                cantidad: f.cantidad, dotacion_minima: f.dotacion_minima, slots: []
            };
        }
        if (f.slot_id) {
            puestos[f.puesto_id].slots.push({
                id: f.slot_id, legajo: f.legajo, rol: f.rol, empleado_nombre: f.empleado_nombre
            });
        }
    });

    let html = '';
    Object.values(puestos).sort((a, b) => (a.turno_codigo || '').localeCompare(b.turno_codigo || '')).forEach(p => {
        const ocupados = p.slots.filter(s => s.legajo).length;

        let headerDias = '';
        for (let d = 1; d <= dias; d++) {
            const dow = (primerDia + (d - 1)) % 7;
            const esFinde = (dow === 0 || dow === 6);
            headerDias += `<th class="text-center small ${esFinde ? 'table-secondary' : ''}" style="min-width:34px">${d}<br><span class="text-muted" style="font-size:10px">${DOW_CORTO[dow]}</span></th>`;
        }

        let filasSlots = '';
        p.slots.forEach(s => {
            let celdas = '';
            for (let d = 1; d <= dias; d++) {
                const fecha = `${periodo}-${String(d).padStart(2, '0')}`;
                const dow = (primerDia + (d - 1)) % 7;
                const esFinde = (dow === 0 || dow === 6);
                const asign = asignacionesPorSlotFecha[`${s.id}_${fecha}`];
                const vacante = !s.legajo;

                if (vacante) {
                    celdas += `<td class="text-center ${esFinde ? 'table-secondary' : ''} bg-light"></td>`;
                } else {
                    celdas += `<td class="text-center dia-cell ${esFinde ? 'table-secondary' : ''}"
                        data-slot-id="${s.id}" data-fecha="${fecha}"
                        data-id-novedad="${asign ? asign.id_novedad : ''}"
                        data-updated-at="${asign ? asign.updated_at_ts : ''}"
                        data-empleado="${s.empleado_nombre}"
                        title="${asign ? asign.novedad_nombre : ''}"
                        style="cursor:pointer">${asign ? (asign.CODIGO_NOVEDAD || '') : ''}</td>`;
                }
            }

            filasSlots += `
              <tr>
                <td class="text-nowrap sticky-col bg-white">
                  ${s.legajo ? s.empleado_nombre : '<span class="text-muted fst-italic">Vacante</span>'}
                  ${s.rol === 'apoyo' ? ' <small class="text-muted">(apoyo)</small>' : ''}
                  <span class="badge bg-light text-dark border ms-1 slot-chip" data-slot-id="${s.id}" style="cursor:pointer" title="Cambiar persona">
                    <i class="fa-solid fa-user-pen"></i>
                  </span>
                </td>
                ${celdas}
              </tr>`;
        });

        html += `
        <div class="card mb-3" data-puesto-id="${p.id}">
          <div class="card-header d-flex justify-content-between align-items-center py-2 flex-wrap gap-2">
            <div>
              <strong>${p.turno_nombre}</strong>
              <span class="text-muted small">(${p.hora_inicio.substring(0,5)}–${p.hora_fin.substring(0,5)}${p.cruza == 1 ? ' +1' : ''})</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-secondary">${ocupados}/${p.slots.length} ocupados</span>
              <div class="input-group input-group-sm" style="width:110px">
                <button class="btn btn-outline-secondary btn-cantidad" data-puesto-id="${p.id}" data-delta="-1" type="button">−</button>
                <span class="input-group-text">${p.cantidad}</span>
                <button class="btn btn-outline-secondary btn-cantidad" data-puesto-id="${p.id}" data-delta="1" type="button">+</button>
              </div>
              <div class="input-group input-group-sm" style="width:150px">
                <span class="input-group-text">Dot. mín.</span>
                <input type="number" class="form-control input-dotacion" data-puesto-id="${p.id}" value="${p.dotacion_minima}" min="0">
              </div>
              <button class="btn btn-sm btn-outline-danger btn-eliminar-puesto" data-puesto-id="${p.id}" ${ocupados > 0 ? 'disabled title="Tiene personas asignadas"' : ''}>
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0" style="font-size:12px">
                <thead>
                  <tr>
                    <th class="sticky-col bg-white">Persona</th>
                    ${headerDias}
                  </tr>
                </thead>
                <tbody>
                  ${filasSlots}
                </tbody>
              </table>
            </div>
          </div>
        </div>`;
    });

    $cont.html(html);
}

$(document).on('change', '#selectorMesCrono, #selectorArea, #selectorServicio', cargarGrilla);

$(document).on('click', '.btn-cantidad', function () {
    const puestoId = $(this).data('puesto-id'), delta = $(this).data('delta');
    $.ajax({
        url: RUTAS_CRONO_GRILLA.ajustarCantidad, type: 'PATCH',
        data: { _token: CSRF_CRONO_GRILLA, puesto_id: puestoId, delta }
    }).done(resp => resp.success ? cargarGrilla() : Swal.fire('Atención', resp.message, 'warning'));
});

$(document).on('change', '.input-dotacion', function () {
    $.ajax({
        url: RUTAS_CRONO_GRILLA.ajustarDotacion, type: 'PATCH',
        data: { _token: CSRF_CRONO_GRILLA, puesto_id: $(this).data('puesto-id'), dotacion_minima: $(this).val() }
    });
});

$(document).on('click', '.btn-eliminar-puesto', function () {
    const puestoId = $(this).data('puesto-id');
    Swal.fire({ title: '¿Eliminar puesto?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar' })
        .then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: RUTAS_CRONO_GRILLA.eliminarPuesto, type: 'DELETE',
                data: { _token: CSRF_CRONO_GRILLA, puesto_id: puestoId }
            }).done(resp => resp.success ? cargarGrilla() : Swal.fire('Atención', resp.message, 'warning'));
        });
});

$('#btnAgregarPuesto').on('click', function () {
    const idArea = $('#selectorArea').val();
    $.get(RUTAS_CRONO_GRILLA.turnosPorArea.replace(':idArea', idArea), function (resp) {
        const $sel = $('#selTurnoNuevoPuesto').empty();
        resp.data.forEach(t => $sel.append(`<option value="${t.id}">${t.nombre} (${t.hora_inicio.substring(0,5)}-${t.hora_fin.substring(0,5)})</option>`));
        $('#modalAgregarPuesto').modal('show');
    });
});

$('#btnConfirmarNuevoPuesto').on('click', function () {
    $.post(RUTAS_CRONO_GRILLA.crearPuesto, {
        _token: CSRF_CRONO_GRILLA,
        periodo: $('#selectorMesCrono').val(),
        id_area: $('#selectorArea').val(),
        id_servicio: $('#selectorServicio').val() || null,
        id_turno: $('#selTurnoNuevoPuesto').val(),
        cantidad: $('#inputCantidadNuevoPuesto').val(),
        dotacion_minima: $('#inputDotacionNuevoPuesto').val()
    }).done(resp => {
        if (resp.success) { $('#modalAgregarPuesto').modal('hide'); cargarGrilla(); }
        else Swal.fire('Atención', resp.message, 'warning');
    }).fail(xhr => Swal.fire('Atención', xhr.responseJSON?.message || 'No se pudo crear el puesto.', 'warning'));
});