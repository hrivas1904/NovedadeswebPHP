$(document).on('click', '.celda-detalle-comparativo', function () {
    const concepto  = $(this).data('concepto');
    const label     = $(this).data('label');
    const periodo   = $(this).data('periodo');                       // 'YYYY-MM'
    const ejecucion = $(this).data('ejecucion') || 'EJECUTADO';

    const fmt = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
    const esc = function (s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    const [y, m] = String(periodo).split('-').map(Number);
    const mesTxt = new Date(y, m - 1, 1).toLocaleDateString('es-AR', { month: 'long', year: 'numeric' });

    $('#compNombreConcepto').text(label);
    $('#compMesVista').text(
        mesTxt.charAt(0).toUpperCase() + mesTxt.slice(1) +
        (ejecucion === 'PRESUPUESTO' ? ' · Presupuestado' : '')
    );
    $('#compBodyDetalle').html('<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>');
    $('#compTotalDetalle').text('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleComparativo')).show();

    $.get(COMPARATIVO_ROUTES.detalle, { concepto: concepto, periodo: periodo, ejecucion: ejecucion }, function (resp) {
        const rows = Array.isArray(resp) ? resp : (resp.data || resp.rows || []);
        if (!rows.length) {
            $('#compBodyDetalle').html('<tr><td colspan="4" class="text-center text-muted py-3">Sin movimientos.</td></tr>');
            return;
        }
        let html = '', total = 0;
        rows.forEach(function (r) {
            const imp = Number(r.importe || 0);
            total += imp;
            const f = String(r.fecha || '').substring(0, 10).split('-').reverse().join('/');
            html += '<tr>' +
                '<td class="text-nowrap">' + f + '</td>' +
                '<td>' + esc(r.subconcepto) + '</td>' +
                '<td>' + esc(r.detalle) + '</td>' +
                '<td class="text-end fw-bold ' + (imp >= 0 ? 'text-success' : 'text-danger') + '">' + fmt.format(imp) + '</td>' +
                '</tr>';
        });
        $('#compBodyDetalle').html(html);
        $('#compTotalDetalle').text(fmt.format(total)).toggleClass('text-danger', total < 0).toggleClass('text-success', total >= 0);
    }).fail(function (xhr) {
        console.log('DETALLE ERROR', xhr.status, xhr.responseText);
        $('#compBodyDetalle').html('<tr><td colspan="4" class="text-center text-danger py-3">No se pudo cargar el detalle (' + xhr.status + ').</td></tr>');
    });
});