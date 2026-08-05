function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
function fmtUsd(v) {
    return 'USD ' + Math.abs(Number(v || 0)).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function fmtCompacto(v, prefijo) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    const abs = Math.abs(n);
 
    let valor, sufijo;
    if (abs >= 1000000) {
        valor = abs / 1000000;
        sufijo = 'M';
    } else if (abs >= 1000) {
        valor = abs / 1000;
        sufijo = 'K';
    } else {
        valor = abs;
        sufijo = '';
    }
 
    return signo + prefijo + valor.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + sufijo;
}
 
function fmtPesosCompacto(v) {
    return fmtCompacto(v, '$\u202f');
}
 
function fmtUsdCompacto(v) {
    return fmtCompacto(v, 'USD\u202f');
}

function cargarPosicion(fecha) {
    $.get(POSICION_ROUTES.data, { fecha: fecha }, function (data) {
        const c = data.cuentas;
 
        $('#cardBodyMacro').text(fmtPesosCompacto(c['MACRO']?.saldo));
        $('#cardBodyNacion').text(fmtPesosCompacto(c['NACION']?.saldo));
        $('#cardBodyFrances9').text(fmtPesosCompacto(c['FRANCES (986)']?.saldo));
        $('#cardBodyFrances1').text(fmtPesosCompacto(c['FRANCES (1001)']?.saldo));
        $('#cardBodyFciPesos').text(fmtPesosCompacto(c['FONDO COMUN DE INVERSION']?.saldo));
        $('#cardBodyFciF3c').text(fmtPesosCompacto(c['FCI FARMACIA']?.saldo));
        $('#cardBodyEfectivoCaja').text(fmtPesosCompacto(c['CAJA']?.saldo));
 
        const usd = Number(c['EFECTIVO USD']?.saldo || 0) + Number(c['FCI USD']?.saldo || 0);
        $('#cardBodyTotalUsdFci').text(fmtUsdCompacto(usd));
        $('#cardBodyTotalUsd').text(fmtUsdCompacto(data.total_usd));
        $('#cardBodyTotalPesos').text(fmtPesosCompacto(data.total_pesos));
 
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