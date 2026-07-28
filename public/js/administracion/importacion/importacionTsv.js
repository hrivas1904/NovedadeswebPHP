let filasTsv = [];

function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

function renderPreviewTsv() {
    $('#previewTsvWrapper').remove();
    if (!filasTsv.length) return;

    const validas = filasTsv.filter(r => r.valido).length;

    let html = '<div id="previewTsvWrapper" class="mt-3">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="text-muted small">' + validas + ' de ' + filasTsv.length + ' listos para importar</span>' +
        '<button type="button" id="btnConfirmarTsv" class="btn btn-primary btn-sm" ' + (validas === 0 ? 'disabled' : '') + '>Importar ' + validas + ' movimientos</button>' +
        '</div>' +
        '<div class="table-responsive" style="max-height:400px; overflow-y:auto;">' +
        '<table class="table table-sm">' +
        '<thead><tr><th>Fecha</th><th>Banco</th><th>Estado</th><th>Concepto</th><th>Detalle</th><th class="text-end">Importe</th><th>Errores</th></tr></thead>' +
        '<tbody>';

    filasTsv.forEach(function (r) {
        const fila = r.valido ? '' : ' style="background:#fff3cd;"';
        html += '<tr' + fila + '>' +
            '<td>' + (r.fecha || '') + '</td>' +
            '<td>' + r.banco + '</td>' +
            '<td>' + r.ejecucion + '</td>' +
            '<td>' + r.concepto + '</td>' +
            '<td>' + r.detalle + '</td>' +
            '<td class="text-end fw-bold ' + (r.importe >= 0 ? 'text-success' : 'text-danger') + '">' + fmtPesos(r.importe) + '</td>' +
            '<td class="text-danger small">' + (r.errores || []).join('; ') + '</td>' +
            '</tr>';
    });

    html += '</tbody></table></div></div>';
    $('#contenidoTsv').closest('.mt-2').after(html);
}

function procesarTsv() {
    const contenido = $('#contenidoTsv').val();
    if (!contenido.trim()) {
        filasTsv = [];
        renderPreviewTsv();
        $('#msgTsv').text('');
        return;
    }

    $.post(IMPORTACION_ROUTES.tsvPreview, { contenido: contenido }, function (data) {
        filasTsv = data.rows;
        $('#msgTsv').text(data.mensaje).css('color', filasTsv.some(r => r.valido) ? 'green' : 'inherit');
        renderPreviewTsv();
    });
}

function procesarArchivoTsv(archivo) {
    const formData = new FormData();
    formData.append('archivo', archivo);

    $('#msgTsv').text('Leyendo archivo...').css('color', 'inherit');

    $.ajax({
        url: IMPORTACION_ROUTES.tsvPreview,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            filasTsv = data.rows;
            $('#msgTsv').text(data.mensaje).css('color', filasTsv.some(r => r.valido) ? 'green' : 'inherit');
            renderPreviewTsv();
        },
        error: function () {
            $('#msgTsv').text('Error al leer el archivo.').css('color', 'red');
        }
    });
}

$(document).on('subvista:cargada', function () {
    if (!$('#contenidoTsv').length) return; // no es la sub-vista de TSV
    filasTsv = [];
    renderPreviewTsv();
});

$(document).on('input', '#contenidoTsv', debounce(procesarTsv, 400));

$(document).on('change', '#inputArchivoTsv', function () {
    if (this.files && this.files[0]) {
        $('#contenidoTsv').val('');
        procesarArchivoTsv(this.files[0]);
    }
});

$(document).on('click', '#btnConfirmarTsv', function () {
    const filasValidas = filasTsv.filter(r => r.valido);
    if (!filasValidas.length) return;

    $.post(IMPORTACION_ROUTES.tsvConfirmar, { rows: filasValidas }, function (data) {
        let msg = '✓ ' + data.insertados + ' movimientos importados.';
        if (data.omitidos > 0) msg += ' (' + data.omitidos + ' omitidos por errores.)';
        $('#msgTsv').text(msg).css('color', 'green');
        $('#contenidoTsv').val('');
        filasTsv = [];
        renderPreviewTsv();
    }).fail(function (xhr) {
        alert('Error al importar: ' + (xhr.responseJSON?.message || 'revisá los datos.'));
    });
});