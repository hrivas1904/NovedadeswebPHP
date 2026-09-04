const ETIQUETAS_CONFLICTO = {
    doble_asignacion: { label: 'Doble asignación', clase: 'danger' },
    descanso_insuficiente: { label: 'Descanso insuficiente (< 12hs)', clase: 'danger' },
    sin_descanso_semanal: { label: '7+ días corridos sin franco', clase: 'warning' },
};

$('#modalConflictos').on('show.bs.modal', function () {
    const periodo = $('#selectorMesCrono').val();
    const idArea = $('#selectorArea').val();
    const idServicio = $('#selectorServicio').val() || '';
    const $cont = $('#contenedorConflictos').html('<p class="text-muted text-center mb-0">Cargando...</p>');

    if (!periodo || !idArea) {
        $cont.html('<p class="text-muted text-center mb-0">Elegí período y área primero.</p>');
        return;
    }

    $.get(RUTAS_CRONO_GRILLA.conflictos, { periodo, id_area: idArea, id_servicio: idServicio }, function (resp) {
        renderConflictos(resp.data);
    });
});

function renderConflictos(conflictos) {
    const $cont = $('#contenedorConflictos');
    if (!conflictos.length) {
        $cont.html('<p class="text-success text-center mb-0"><i class="fa-solid fa-circle-check"></i> Sin conflictos detectados.</p>');
        return;
    }

    $cont.html(conflictos.map(c => {
        const et = ETIQUETAS_CONFLICTO[c.tipo] || { label: c.tipo, clase: 'secondary' };
        return `
        <div class="d-flex justify-content-between align-items-start border-bottom py-2">
          <div>
            <span class="badge bg-${et.clase} mb-1">${et.label}</span>
            <div><strong>${c.colaborador}</strong> <span class="text-muted small">(legajo ${c.legajo})</span></div>
            <div class="small text-muted">${c.detalle}</div>
          </div>
          <span class="text-muted small text-nowrap">${c.fecha}</span>
        </div>`;
    }).join(''));
}