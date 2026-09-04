const CSRF_CRONO_COPIAR = $('meta[name="csrf-token"]').attr('content');

function ejecutarCopiarMesAnterior(forzar) {
    $.post(RUTAS_CRONO_GRILLA.copiarMesAnterior, {
        _token: CSRF_CRONO_COPIAR,
        periodo_destino: $('#selectorMesCrono').val(),
        id_area: $('#selectorArea').val(),
        id_servicio: $('#selectorServicio').val() || null,
        forzar: forzar ? 1 : 0
    }).done(function (resp) {
        if (resp.success) {
            Swal.fire('Listo', `Se copió la estructura desde ${resp.periodoOrigen}.`, 'success');
            cargarGrilla();
        }
    }).fail(function (xhr) {
        const data = xhr.responseJSON || {};
        if (xhr.status === 409 && data.requiereConfirmacion) {
            Swal.fire({
                title: 'El período ya tiene puestos cargados',
                text: `Hay ${data.cantidadExistente} puesto(s) ya definidos. Si continuás, se van a sobrescribir (incluyendo los días ya cargados en esos puestos). ¿Continuar copiando desde ${data.periodoOrigen}?`,
                icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, sobrescribir', confirmButtonColor: '#dc3545'
            }).then(r => {
                if (r.isConfirmed) ejecutarCopiarMesAnterior(true);
            });
        } else {
            Swal.fire('Atención', data.message || 'No se pudo copiar el mes anterior.', 'warning');
        }
    });
}

$('#btnReplicarMesAnt').on('click', function () {
    if (!$('#selectorMesCrono').val() || !$('#selectorArea').val()) {
        return Swal.fire('Atención', 'Elegí período y área primero.', 'warning');
    }
    ejecutarCopiarMesAnterior(false);
});