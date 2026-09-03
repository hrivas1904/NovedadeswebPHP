const CSRF_CRONO_GRILLA = $('meta[name="csrf-token"]').attr('content');

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

    $.get(RUTAS_CRONO_GRILLA.grilla, { periodo, id_area: idArea, id_servicio: idServicio }, function (resp) {
        renderGrilla(resp.data);
    });
}

function renderGrilla(filas) {
    const $cont = $('#contenedorGrilla');
    if (!filas.length) {
        $cont.html('<p class="text-muted text-center mb-0">No hay puestos definidos para este período/área/servicio. Usá "Agregar puesto" para empezar.</p>');
        return;
    }

    const puestos = {};
    filas.forEach(f => {
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

        html += `
        <div class="card mb-2" data-puesto-id="${p.id}">
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
          <div class="card-body py-2 d-flex flex-wrap gap-2">
            ${p.slots.map(s => `
              <span class="badge ${s.legajo ? 'bg-primary' : 'bg-light text-dark border'} slot-chip" data-slot-id="${s.id}">
                ${s.legajo ? s.empleado_nombre : 'Vacante'}${s.rol === 'apoyo' ? ' <small>(apoyo)</small>' : ''}
              </span>
            `).join('')}
          </div>
        </div>`;
    });

    $cont.html(html);
}

// Único punto de escucha: reacciona a cambios en los 3 selectores que ya maneja cronogramaTrabajo.js
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