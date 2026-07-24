let finnegansRows = [];

function procesarFinnegans() {
    const contenido = $('#contenidoCuentasPagar').val();
    if (!contenido.trim()) {
        $('#previewFinnegansWrapper').hide();
        $('#msgFinnegans').text('');
        finnegansRows = [];
        return;
    }
    $.post(PRESUPUESTAR_ROUTES.previewFinnegans, { contenido: contenido }, function (data) {
        finnegansRows = data.rows;
        $('#msgFinnegans').text(data.mensaje).css('color', finnegansRows.length ? 'green' : 'inherit');
        renderPreviewFinnegans();
    });
}

function renderPreviewFinnegans() {
    if (!finnegansRows.length) {
        $('#previewFinnegansWrapper').hide();
        return;
    }
    let html = '';
    finnegansRows.forEach(function (r, i) {
        let opts = '';
        CONCEPTOS_CATALOGO.forEach(function (c) {
            opts += '<option value="' + c + '"' + (c === r.concepto ? ' selected' : '') + '>' + c + '</option>';
        });
        html += '<tr>' +
            '<td>' + r.fecha + '</td>' +
            '<td>' + r.detalle + '</td>' +
            '<td><select class="form-select form-select-sm select-concepto-finnegans" data-idx="' + i + '">' + opts + '</select></td>' +
            '<td class="text-end fw-bold text-danger">' + fmtPesos(r.importe) + '</td>' +
            '</tr>';
    });
    $('#previewFinnegansBody').html(html);
    $('#cantidadFinnegans, #cantidadFinnegans2').text(finnegansRows.length);
    $('#previewFinnegansWrapper').show();
}

// delegado porque las filas se inyectan por AJAX
$(document).on('change', '.select-concepto-finnegans', function () {
    finnegansRows[$(this).data('idx')].concepto = $(this).val();
});

$('#contenidoCuentasPagar').on('input', debounce(procesarFinnegans, 400));

$('#btnConfirmarFinnegans').on('click', function () {
    if (!finnegansRows.length) return;
    $.post(PRESUPUESTAR_ROUTES.confirmarFinnegans, { rows: finnegansRows }, function (data) {
        $('#msgFinnegans').text('✓ ' + data.insertados + ' pagos importados.').css('color', 'green');
        $('#contenidoCuentasPagar').val('');
        $('#previewFinnegansWrapper').hide();
        finnegansRows = [];
    });
});