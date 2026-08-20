let filasCaja = [];

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

function renderPreviewCaja() {
    $('#previewCajaWrapper').remove();
    if (!filasCaja.length) return;

    let opts = '';
    CONCEPTOS_CATALOGO.forEach(function (c) {
        opts += '<option value="' + c + '">' + c + '</option>';
    });

    let html = '<div id="previewCajaWrapper" class="mt-3">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="text-muted small">' + filasCaja.length + ' movimientos</span>' +
        '<button type="button" id="btnConfirmarCaja" class="btn btn-primary btn-sm">Importar ' + filasCaja.length + ' movimientos</button>' +
        '</div>' +
        '<div class="table-responsive" style="max-height:400px; overflow-y:auto;">' +
        '<table class="table table-sm">' +
        '<thead><tr><th>Fecha</th><th>Concepto</th><th>Sub-concepto</th><th>Detalle</th><th class="text-end">Importe</th></tr></thead>' +
        '<tbody>';

    function optsSubconcepto(concepto, actual) {
        const lista = SUBCONCEPTOS_POR_CONCEPTO[concepto] || [];
        let out = '';
        lista.forEach(function (s) {
            out += '<option value="' + s + '"' + (s === actual ? ' selected' : '') + '>' + s + '</option>';
        });
        return out;
    }

    filasCaja.forEach(function (r, i) {
        let optsFila = opts.replace('value="' + r.concepto + '"', 'value="' + r.concepto + '" selected');
        html += '<tr>' +
            '<td>' + r.fecha + '</td>' +
            '<td><select class="form-select form-select-sm select-concepto-caja" data-idx="' + i + '">' + optsFila + '</select></td>' +
            '<td><select class="form-select form-select-sm select-subconcepto-caja" data-idx="' + i + '">' + optsSubconcepto(r.concepto, r.subconcepto) + '</select></td>' +
            '<td>' + r.detalle + '</td>' +
            '<td class="text-end fw-bold ' + (r.importe >= 0 ? 'text-success' : 'text-danger') + '">' + fmtPesos(r.importe) + '</td>' +
            '</tr>';
    });

    html += '</tbody></table></div></div>';
    $('#contenidoCaja').closest('.mt-2').after(html);
}

function procesarCaja() {
    const contenido = $('#contenidoCaja').val();
    if (!contenido.trim()) {
        filasCaja = [];
        renderPreviewCaja();
        $('#msgCaja').text('');
        return;
    }

    $.post(IMPORTACION_ROUTES.cajaPreview, { contenido: contenido }, function (data) {
        filasCaja = data.rows;
        $('#msgCaja').text(data.mensaje).css('color', filasCaja.length ? 'green' : 'inherit');
        renderPreviewCaja();
    });
}

function procesarArchivoCaja(archivo) {
    const formData = new FormData();
    formData.append('archivo', archivo);

    $('#msgCaja').text('Leyendo archivo...').css('color', 'inherit');

    $.ajax({
        url: IMPORTACION_ROUTES.cajaPreview,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            filasCaja = data.rows;
            $('#msgCaja').text(data.mensaje).css('color', filasCaja.length ? 'green' : 'inherit');
            renderPreviewCaja();
        },
        error: function () {
            $('#msgCaja').text('Error al leer el archivo.').css('color', 'red');
        }
    });
}

$(document).on('subvista:cargada', function () {
    if (!$('#contenidoCaja').length) return; // no es la sub-vista de Caja
    filasCaja = [];
    renderPreviewCaja();
});

$(document).on('input', '#contenidoCaja', debounce(procesarCaja, 400));

$(document).on('change', '#inputArchivoCaja', function () {
    if (this.files && this.files[0]) {
        $('#contenidoCaja').val('');
        procesarArchivoCaja(this.files[0]);
    }
});

$(document).on('change', '.select-concepto-caja', function () {
    const idx = $(this).data('idx');
    const nuevoConcepto = $(this).val();
    filasCaja[idx].concepto = nuevoConcepto;

    const lista = SUBCONCEPTOS_POR_CONCEPTO[nuevoConcepto] || [];
    let opts = '';
    lista.forEach(function (s) { opts += '<option value="' + s + '">' + s + '</option>'; });
    $('.select-subconcepto-caja[data-idx="' + idx + '"]').html(opts);
    filasCaja[idx].subconcepto = lista[0] || '';
});

$(document).on('change', '.select-subconcepto-caja', function () {
    filasCaja[$(this).data('idx')].subconcepto = $(this).val();
});

$(document).on('click', '#btnConfirmarCaja', function () {
    if (!filasCaja.length) return;

    $.post(IMPORTACION_ROUTES.cajaConfirmar, { rows: filasCaja }, function (data) {
        $('#msgCaja').text('✓ ' + data.insertados + ' movimientos importados.').css('color', 'green');
        $('#contenidoCaja').val('');
        filasCaja = [];
        renderPreviewCaja();
    }).fail(function (xhr) {
        alert('Error al importar: ' + (xhr.responseJSON?.message || 'revisá los datos.'));
    });
});