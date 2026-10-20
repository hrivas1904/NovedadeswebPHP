$(document).on('click', '.celda-detalle-diario', function () {
    const concepto = $(this).data('concepto');
    const label    = $(this).data('label');
    const fecha    = $(this).data('fecha');                // 'YYYY-MM-DD'

    const fmt = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
    const esc = function (s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    const [y, m, d] = String(fecha).substring(0, 10).split('-').map(Number);
    const fechaTxt = new Date(y, m - 1, d).toLocaleDateString('es-AR', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
    });

    $('#diaNombreConcepto').text(label);
    $('#diaFechaVista').text(fechaTxt.charAt(0).toUpperCase() + fechaTxt.slice(1) + ' · Presupuestado');
    $('#diaBodyDetalle').html('<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>');
    $('#diaTotalDetalle').text('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleDiario')).show();

    $.get(COMPARATIVO_DIARIO_ROUTES.detalle, { concepto: concepto, fecha: fecha }, function (rows) {
        if (!rows.length) {
            $('#diaBodyDetalle').html('<tr><td colspan="4" class="text-center text-muted py-3">Sin movimientos.</td></tr>');
            return;
        }
        let html = '', total = 0;
        rows.forEach(function (r) {
            const imp = Number(r.importe || 0);
            total += imp;
            html += '<tr>' +
                '<td>' + esc(r.banco) + '</td>' +
                '<td>' + esc(r.subconcepto) + '</td>' +
                '<td>' + esc(r.detalle) + '</td>' +
                '<td class="text-end fw-bold ' + (imp >= 0 ? 'text-success' : 'text-danger') + '">' + fmt.format(imp) + '</td>' +
                '</tr>';
        });
        $('#diaBodyDetalle').html(html);
        $('#diaTotalDetalle').text(fmt.format(total)).toggleClass('text-danger', total < 0).toggleClass('text-success', total >= 0);
    }).fail(function (xhr) {
        console.log('DETALLE DIARIO ERROR', xhr.status, xhr.responseText);
        $('#diaBodyDetalle').html('<tr><td colspan="4" class="text-center text-danger py-3">No se pudo cargar el detalle (' + xhr.status + ').</td></tr>');
    });
});