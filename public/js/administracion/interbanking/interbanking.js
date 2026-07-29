let filasInterbanking = [];

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

function renderPreviewInterbanking() {
    $('#previewInterbankingWrapper').remove();
    if (!filasInterbanking.length) return;

    const validas = filasInterbanking.filter(r => r.valido).length;

    let opts = '';
    CONCEPTOS_CATALOGO.forEach(function (c) {
        opts += '<option value="' + c + '">' + c + '</option>';
    });

    let html = '<div id="previewInterbankingWrapper" class="mt-3">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="text-muted small">' + validas + ' de ' + filasInterbanking.length + ' listos para importar</span>' +
        '<button type="button" id="btnConfirmarInterbanking" class="btn btn-primary btn-sm" ' + (validas === 0 ? 'disabled' : '') + '>Importar ' + validas + ' movimientos</button>' +
        '</div>' +
        '<div class="table-responsive" style="max-height:400px; overflow-y:auto;">' +
        '<table class="table table-sm">' +
        '<thead><tr><th>Fecha</th><th>Banco</th><th>Concepto</th><th>Cód. Op</th><th>Detalle</th><th class="text-end">Importe</th><th>Errores</th></tr></thead>' +
        '<tbody>';

    filasInterbanking.forEach(function (r, i) {
        const fila = r.valido ? '' : ' style="background:#fff3cd;"';
        let optsFila = opts.replace('value="' + r.concepto + '"', 'value="' + r.concepto + '" selected');
        html += '<tr' + fila + '>' +
            '<td>' + r.fecha + '</td>' +
            '<td>' + r.banco + '</td>' +
            '<td><select class="form-select form-select-sm select-concepto-ib" data-idx="' + i + '">' + optsFila + '</select></td>' +
            '<td>' + r.subconcepto + '</td>' +
            '<td>' + r.detalle + '</td>' +
            '<td class="text-end fw-bold ' + (r.importe >= 0 ? 'text-success' : 'text-danger') + '">' + fmtPesos(r.importe) + '</td>' +
            '<td class="text-danger small">' + (r.errores || []).join('; ') + '</td>' +
            '</tr>';
    });

    html += '</tbody></table></div></div>';
    $('#contenidoInterbanking').closest('.mt-2').after(html);
}

function procesarInterbanking() {
    const contenido = $('#contenidoInterbanking').val();
    if (!contenido.trim()) {
        filasInterbanking = [];
        renderPreviewInterbanking();
        $('#msgInterbanking').text('');
        return;
    }

    $.post(INTERBANKING_ROUTES.preview, { contenido: contenido }, function (data) {
        filasInterbanking = data.rows;
        $('#msgInterbanking').text(data.mensaje).css('color', filasInterbanking.some(r => r.valido) ? 'green' : 'inherit');
        renderPreviewInterbanking();
    });
}

function procesarArchivoInterbanking(archivo) {
    const formData = new FormData();
    formData.append('archivo', archivo);

    $('#msgInterbanking').text('Leyendo archivo...').css('color', 'inherit');

    $.ajax({
        url: INTERBANKING_ROUTES.preview,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            filasInterbanking = data.rows;
            $('#msgInterbanking').text(data.mensaje).css('color', filasInterbanking.some(r => r.valido) ? 'green' : 'inherit');
            renderPreviewInterbanking();
        },
        error: function () {
            $('#msgInterbanking').text('Error al leer el archivo.').css('color', 'red');
        }
    });
}

$(document).on('input', '#contenidoInterbanking', debounce(procesarInterbanking, 400));

$(document).on('change', '#inputArchivoInterbanking', function () {
    if (this.files && this.files[0]) {
        $('#contenidoInterbanking').val('');
        procesarArchivoInterbanking(this.files[0]);
    }
});

$(document).on('change', '.select-concepto-ib', function () {
    filasInterbanking[$(this).data('idx')].concepto = $(this).val();
});

$(document).on('click', '#btnConfirmarInterbanking', function () {
    const filasValidas = filasInterbanking.filter(r => r.valido);
    if (!filasValidas.length) return;

    $.post(INTERBANKING_ROUTES.confirmar, { rows: filasValidas }, function (data) {
        let msg = '✓ ' + data.insertados + ' movimientos importados.';
        if (data.omitidos > 0) msg += ' (' + data.omitidos + ' omitidos.)';
        $('#msgInterbanking').text(msg).css('color', 'green');
        $('#contenidoInterbanking').val('');
        filasInterbanking = [];
        renderPreviewInterbanking();
    }).fail(function (xhr) {
        alert('Error al importar: ' + (xhr.responseJSON?.message || 'revisá los datos.'));
    });
});