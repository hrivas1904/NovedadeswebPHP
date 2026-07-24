function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
function fmtUsd(v) {
    return 'USD ' + Math.abs(Number(v || 0)).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function cargarPosicion(fecha) {
    $.get(POSICION_ROUTES.data, { fecha: fecha }, function (data) {
        const c = data.cuentas;

        $('#cardBodyMacro').text(fmtPesos(c['MACRO']?.saldo));
        $('#cardBodyNacion').text(fmtPesos(c['NACION']?.saldo));
        $('#cardBodyFrances9').text(fmtPesos(c['FRANCES (986)']?.saldo));
        $('#cardBodyFrances1').text(fmtPesos(c['FRANCES (1001)']?.saldo));
        $('#cardBodyFciPesos').text(fmtPesos(c['FONDO COMUN DE INVERSION']?.saldo));
        $('#cardBodyFciF3c').text(fmtPesos(c['FCI FARMACIA']?.saldo));
        $('#cardBodyEfectivoCaja').text(fmtPesos(c['CAJA']?.saldo));

        const usd = Number(c['EFECTIVO USD']?.saldo || 0) + Number(c['FCI USD']?.saldo || 0);
        $('#cardBodyTotalUsdFci').text(fmtUsd(usd));
        $('#cardBodyTotalUsd').text(fmtUsd(data.total_usd));
        $('#cardBodyTotalPesos').text(fmtPesos(data.total_pesos));

        $('#lblDiaDetallado').text(data.fecha_label);
    });
}

$(document).ready(function () {
    const hoy = new Date().toISOString().slice(0, 10);
    $('input[type="date"]').val(hoy);
    cargarPosicion(hoy);

    $('input[type="date"]').on('change', function () {
        cargarPosicion($(this).val());
    });

    $('button:contains("Hoy")').on('click', function () {
        const hoy = new Date().toISOString().slice(0, 10);
        $('input[type="date"]').val(hoy);
        cargarPosicion(hoy);
    });
});