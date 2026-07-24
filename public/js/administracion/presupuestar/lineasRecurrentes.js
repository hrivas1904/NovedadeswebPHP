function poblarSelectConceptos() {
    let opts = '';
    CONCEPTOS_CATALOGO.forEach(c => opts += '<option value="' + c + '">' + c + '</option>');
    $('#selConceptoRecurrente').html(opts).val('SUELDOS');
}

function renderRecurrentes(rows) {
    if (!rows.length) {
        $('#wrapperRecurrentes').hide();
        $('#msgSinRecurrentes').show();
        return;
    }
    let html = '';
    rows.forEach(function (r) {
        const color = Number(r.importe) >= 0 ? 'text-success' : 'text-danger';
        html += '<tr data-id="' + r.id + '">' +
            '<td>' + r.banco + '</td><td>' + r.concepto + '</td><td>' + r.detalle + '</td>' +
            '<td class="text-end fw-bold ' + color + '">' + fmtPesos(r.importe) + '</td>' +
            '<td><button class="btn btn-sm btn-outline-danger btn-eliminar-recurrente" data-id="' + r.id + '">×</button></td>' +
            '</tr>';
    });
    $('#bodyRecurrentes').html(html);
    $('#cantidadRecurrentes').text(rows.length);
    $('#wrapperRecurrentes').show();
    $('#msgSinRecurrentes').hide();
}

function cargarRecurrentes() {
    $.get(PRESUPUESTAR_ROUTES.recurrentesListar, function (rows) {
        renderRecurrentes(rows);
    });
}

$('#btnAgregarRowRecurrente').on('click', function () {
    const detalle = $('#inputDetalleRecurrente').val().trim();
    const importeRaw = $('#inputImporteRecurrente').val().trim();
    if (!detalle || !importeRaw) return;

    $.post(PRESUPUESTAR_ROUTES.recurrentesGuardar, {
        banco: $('#selBancoRecurrente').val(),
        seccion: $('#selSeccionRecurrente').val(),
        concepto: $('#selConceptoRecurrente').val(),
        subconcepto: $('#inputSubconceptoRecurrente').val(),
        detalle: detalle,
        importe: importeRaw.replace(/\./g, '').replace(',', '.'),
    }, function () {
        $('#inputSubconceptoRecurrente, #inputDetalleRecurrente, #inputImporteRecurrente').val('');
        cargarRecurrentes();
    });
});

$(document).on('click', '.btn-eliminar-recurrente', function () {
    const id = $(this).data('id');
    $.ajax({
        url: PRESUPUESTAR_ROUTES.recurrentesEliminar.replace(':id', id),
        type: 'DELETE',
        success: cargarRecurrentes,
    });
});

$('#btnAplicarRecurrentes').on('click', function () {
    const mes = $('#inputMesAplicarRecurrentes').val();
    if (!mes) return;
    $.post(PRESUPUESTAR_ROUTES.recurrentesAplicar, { mes: mes }, function (data) {
        alert(data.insertados + ' líneas aplicadas al mes ' + mes + '.');
    });
});

$(document).ready(function () {
    poblarSelectConceptos();
    $('#inputMesAplicarRecurrentes').val(new Date().toISOString().slice(0, 7));
    cargarRecurrentes();
});