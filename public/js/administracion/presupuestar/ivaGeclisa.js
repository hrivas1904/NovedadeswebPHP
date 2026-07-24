function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

let geclisaRows = [];

function procesarGeclisa() {
    const contenido = $('#contenidoIvaVentas').val();
    if (!contenido.trim()) {
        $('#previewGeclisaWrapper').hide();
        $('#msgGeclisa').text('');
        geclisaRows = [];
        return;
    }
    $.post(PRESUPUESTAR_ROUTES.previewGeclisa, { contenido: contenido }, function (data) {
        geclisaRows = data.rows;
        $('#msgGeclisa').text(data.mensaje).css('color', geclisaRows.length ? 'green' : 'inherit');

        if (geclisaRows.length > 0) {
            let html = '';
            geclisaRows.slice(0, 5).forEach(function (r) {
                html += '<tr><td>' + r.fecha + '</td><td>' + r.detalle + '</td>' +
                    '<td class="text-end fw-bold text-success">' + fmtPesos(r.importe) + '</td></tr>';
            });
            $('#previewGeclisaBody').html(html);
            $('#cantidadGeclisa').text(geclisaRows.length);
            $('#previewGeclisaWrapper').show();
        } else {
            $('#previewGeclisaWrapper').hide();
        }
    });
}

$('#contenidoIvaVentas').on('input', debounce(procesarGeclisa, 400));

$('#btnConfirmarGeclisa').on('click', function () {
    if (!geclisaRows.length) return;
    $.post(PRESUPUESTAR_ROUTES.confirmarGeclisa, { rows: geclisaRows }, function (data) {
        $('#msgGeclisa').text('✓ ' + data.insertados + ' cobranzas importadas.').css('color', 'green');
        $('#contenidoIvaVentas').val('');
        $('#previewGeclisaWrapper').hide();
        geclisaRows = [];
    });
});