function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? "-" : "";
    return (
        signo +
        "$\u202f" +
        Math.abs(n).toLocaleString("es-AR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    );
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

function sumaImporte(rows) {
    return (rows || []).reduce((a, r) => a + Number(r.importe || 0), 0);
}

// Agrupa las filas por subconcepto, sumando los importes de cada grupo.
// Ordenado de mayor a menor importe absoluto (los mas grandes arriba).
function agruparPorSubconcepto(rows) {
    const grupos = {};
    (rows || []).forEach(function (r) {
        const key = r.subconcepto && r.subconcepto.trim() !== '' ? r.subconcepto : '(sin sub-concepto)';
        grupos[key] = (grupos[key] || 0) + Number(r.importe || 0);
    });

    return Object.keys(grupos)
        .map(function (subconcepto) {
            return { subconcepto: subconcepto, importe: grupos[subconcepto] };
        })
        .sort(function (a, b) {
            return Math.abs(b.importe) - Math.abs(a.importe);
        });
}

function tablaMovimientos(rows) {
    const agrupado = agruparPorSubconcepto(rows);

    if (agrupado.length === 0)
        return '<label class="text-muted">Sin registros.</label>';

    let html =
        '<div class="table-responsive"><table class="table table-sm mb-0">' +
        '<thead><tr><th>Sub-concepto</th><th class="text-end">Importe</th></tr></thead><tbody>';

    agrupado.forEach(function (r) {
        const color = Number(r.importe) >= 0 ? "text-success" : "text-danger";
        html +=
            "<tr>" +
            "<td>" +
            r.subconcepto +
            "</td>" +
            '<td class="text-end fw-bold ' +
            color +
            '">' +
            fmtPesos(r.importe) +
            "</td></tr>";
    });

    return html + "</tbody></table></div>";
}

function cargarHoy(mes) {
    $.get(HOY_ROUTES.data, { mes: mes }, function (data) {
        const r = data.resumen;

        if (r.ingresos_presupuestados > 0) {
            $("#cardBodyMacro").css("color", "#1DAC8A");
        } else if (r.ingresos_presupuestados < 0) {
            $("#cardBodyMacro").css("color", "#d64545");
        } else {
            $("#cardBodyMacro").css("color", "#00558C");
        }

        if (r.neto_presupuestado > 0) {
            $("#cardBodyFrances9").css("color", "#1DAC8A");
        } else if (r.neto_presupuestado < 0) {
            $("#cardBodyFrances9").css("color", "#d64545");
        } else {
            $("#cardBodyFrances9").css("color", "#00558C");
        }

        if (r.saldo_bancos > 0) {
            $("#cardBodyFrances1").css("color", "#1DAC8A");
        } else if (r.saldo_bancos < 0) {
            $("#cardBodyFrances1").css("color", "#d64545");
        } else {
            $("#cardBodyFrances1").css("color", "#00558C");
        }

        if (r.necesidad_rescate > 0) {
            $("#lblNecesidadMonto").css("color", "#1DAC8A");
        } else if (r.necesidad_rescate < 0) {
            $("#lblNecesidadMonto").css("color", "#d64545");
        } else {
            $("#lblNecesidadMonto").css("color", "#00558C");
        }

        if (r.egresos_presupuestados===0){
            $("#cardBodyNacion").text(fmtPesosCompacto(r.egresos_presupuestados));
        } else {
            $("#cardBodyNacion").text("-"+fmtPesosCompacto(r.egresos_presupuestados));
            $("#cardBodyNacion").css("color", "#d64545");
        }

        $("#cardBodyMacro").text(fmtPesosCompacto(r.ingresos_presupuestados));        
        $("#cardBodyFrances9").text(fmtPesosCompacto(r.neto_presupuestado));
        $("#cardBodyFrances1").text(fmtPesosCompacto(r.saldo_bancos));

        if (r.necesidad_rescate < 0)
            $("#lblNecesidadTitulo").text("⚠ Necesidad de rescate de FCI");
        else if (r.necesidad_rescate > 0)
            $("#lblNecesidadTitulo").text("✓ Sin necesidad de rescate");
        else $("#lblNecesidadTitulo").text("POSICIÓN EQUILIBRADA");
        $("#lblNecesidadMonto").text(fmtPesosCompacto(r.necesidad_rescate));

        $("#totalIngPresHoy").text(fmtPesosCompacto(sumaImporte(data.pres_ing)));
        $("#bodyIngPresHoy").html(tablaMovimientos(data.pres_ing));
        $("#totalEgrPresHoy").text(fmtPesosCompacto(sumaImporte(data.pres_egr)));
        $("#bodyEgrPresHoy").html(tablaMovimientos(data.pres_egr));

        if (data.exec_ing.length + data.exec_egr.length > 0) {
            $("#seccionEjecutadoHoy").show();
            $("#totalIngExecHoy").text(fmtPesosCompacto(sumaImporte(data.exec_ing)));
            $("#bodyIngExecHoy").html(tablaMovimientos(data.exec_ing));
            $("#totalEgrExecHoy").text(fmtPesosCompacto(sumaImporte(data.exec_egr)));
            $("#bodyEgrExecHoy").html(tablaMovimientos(data.exec_egr));
        } else {
            $("#seccionEjecutadoHoy").hide();
        }
    });
}

$(document).ready(function () {
    cargarHoy(new Date().toISOString().slice(0, 7));
});