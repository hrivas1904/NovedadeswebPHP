function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fmtUsd(v) {
    return 'USD ' + Math.abs(Number(v || 0)).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function renderFilaPesos(r) {
    const estiloInput = r.carga_manual ? 'style="background:#FFF9E0;"' : '';
    const asterisco = r.carga_manual ? ' *' : '';
    const valor = Number(r.saldo_inicial).toFixed(2);

    return '<tr data-cuenta="' + r.nombre + '">' +
        '<td class="fw-semibold" style="color:#1B2A4A;">' + r.nombre + asterisco + '</td>' +
        '<td><input type="text" class="form-control form-control-sm text-end input-saldo-inicial" ' + estiloInput + ' value="' + valor + '" data-original="' + valor + '"></td>' +
        '<td class="text-end" style="color:#28a745;">' + fmtPesos(r.movimientos_ejecutados) + '</td>' +
        '<td class="text-end fw-bold" style="color:#28a745;">' + fmtPesos(r.saldo_actual) + '</td>' +
        '</tr>';
}

function renderFilaUsd(r) {
    const valor = Number(r.saldo_inicial).toFixed(2);

    return '<tr data-cuenta="' + r.nombre + '">' +
        '<td class="fw-semibold" style="color:#1B2A4A;">' + r.nombre + ' *</td>' +
        '<td><input type="text" class="form-control form-control-sm text-end input-saldo-inicial" style="background:#FFF9E0;" value="' + valor + '" data-original="' + valor + '"></td>' +
        '<td class="text-end text-muted">—</td>' +
        '<td class="text-end fw-bold" style="color:#28a745;">' + fmtPesos(r.saldo_actual) + '</td>' +
        '</tr>';
}

function cargarSaldosCuenta(mes) {
    $.get(SALDOS_CUENTA_ROUTES.data, { mes: mes }, function (data) {
        $('#bodySaldosCuentaPesos').html(data.pesos.map(renderFilaPesos).join(''));
        $('#bodySaldosCuentaUsd').html(data.usd.map(renderFilaUsd).join(''));

        const totPesosInicial = data.pesos.reduce((a, r) => a + Number(r.saldo_inicial), 0);
        const totPesosMov     = data.pesos.reduce((a, r) => a + Number(r.movimientos_ejecutados), 0);
        const totPesosActual  = data.pesos.reduce((a, r) => a + Number(r.saldo_actual), 0);
        $('#totalPesosInicial').text(fmtPesos(totPesosInicial));
        $('#totalPesosMov').text(fmtPesos(totPesosMov));
        $('#totalPesosActual').text(fmtPesos(totPesosActual));

        const totUsdInicial = data.usd.reduce((a, r) => a + Number(r.saldo_inicial), 0);
        const totUsdActual  = data.usd.reduce((a, r) => a + Number(r.saldo_actual), 0);
        $('#totalUsdInicial').text(fmtUsd(totUsdInicial));
        $('#totalUsdActual').text(fmtUsd(totUsdActual));
    });
}

$(document).ready(function () {
    cargarSaldosCuenta($('#mesSaldosCuenta').val());

    $('#mesSaldosCuenta').on('change', function () {
        cargarSaldosCuenta($(this).val());
    });

    // Autoguardado al salir del campo -- SOLO si el valor cambio realmente
    $(document).on('blur', '.input-saldo-inicial', function () {
        const $input = $(this);
        const raw = $input.val().trim();
        const original = $input.data('original');
        const mes = $('#mesSaldosCuenta').val();

        if (raw === String(original)) {
            return; // no hubo cambio, no disparamos ningun request
        }

        const $tr = $input.closest('tr');
        const cuenta = $tr.data('cuenta');

        if (raw === '') {
            $.post(SALDOS_CUENTA_ROUTES.eliminar, { cuenta: cuenta, mes: mes }, function () {
                cargarSaldosCuenta(mes);
            });
            return;
        }

        const monto = raw.replace(/\./g, '').replace(',', '.');
        $.post(SALDOS_CUENTA_ROUTES.guardar, { cuenta: cuenta, mes: mes, monto: monto }, function () {
            cargarSaldosCuenta(mes);
        });
    });
});