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
    const signo = n < 0 ? "-" : "";
    const abs = Math.abs(n);

    let valor, sufijo;
    if (abs >= 1000000) {
        valor = abs / 1000000;
        sufijo = "M";
    } else if (abs >= 1000) {
        valor = abs / 1000;
        sufijo = "K";
    } else {
        valor = abs;
        sufijo = "";
    }

    return (
        signo +
        prefijo +
        valor.toLocaleString("es-AR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) +
        sufijo
    );
}

function fmtPesosCompacto(v) {
    return fmtCompacto(v, "$\u202f");
}

function sumaImporte(rows) {
    return (rows || []).reduce((a, r) => a + Number(r.importe || 0), 0);
}

function escHtml(s) {
    return String(s == null ? "" : s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

// Agrupa las filas por subconcepto, sumando los importes de cada grupo.
// Ordenado de mayor a menor importe absoluto (los mas grandes arriba).
function agruparPorSubconcepto(rows, desglosarOS) {
    const grupos = {};
    (rows || []).forEach(function (r) {
        const sub =
            r.subconcepto && r.subconcepto.trim() !== ""
                ? r.subconcepto
                : "(sin sub-concepto)";
        const detalle =
            desglosarOS && r.concepto === "OBRAS SOCIALES"
                ? (r.detalle || "").trim()
                : "";
        const key = sub + "||" + detalle;
        if (!grupos[key])
            grupos[key] = { subconcepto: sub, detalle: detalle, importe: 0 };
        grupos[key].importe += Number(r.importe || 0);
    });

    return Object.keys(grupos)
        .map(function (k) {
            return grupos[k];
        })
        .sort(function (a, b) {
            return Math.abs(b.importe) - Math.abs(a.importe);
        });
}

function tablaMovimientos(rows, desglosarOS) {
    const agrupado = agruparPorSubconcepto(rows, desglosarOS);
    if (agrupado.length === 0)
        return '<label class="text-muted">Sin registros.</label>';

    let html =
        '<div class="table-responsive"><table class="table table-sm mb-0">' +
        '<thead><tr><th>Sub-concepto</th><th class="text-end">Importe</th></tr></thead><tbody>';

    agrupado.forEach(function (r) {
        const color = Number(r.importe) >= 0 ? "text-success" : "text-danger";

        if (r.detalle) {
            // Fila con OS desglosada: nombre + importe juntos
            html +=
                "<tr><td>" +
                escHtml(r.subconcepto) +
                '<br><small class="text-muted">' +
                escHtml(r.detalle) +
                "</small>" +
                ' <small class="fw-bold ' +
                color +
                '">— ' +
                fmtPesos(r.importe) +
                "</small>" +
                "</td>" +
                '<td class="text-end fw-bold ' +
                color +
                '">' +
                fmtPesos(r.importe) +
                "</td></tr>";
        } else {
            html +=
                "<tr><td>" +
                escHtml(r.subconcepto) +
                "</td>" +
                '<td class="text-end fw-bold ' +
                color +
                '">' +
                fmtPesos(r.importe) +
                "</td></tr>";
        }
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

        if (r.egresos_presupuestados === 0) {
            $("#cardBodyNacion").text(
                fmtPesosCompacto(r.egresos_presupuestados),
            );
        } else {
            $("#cardBodyNacion").text(
                "-" + fmtPesosCompacto(r.egresos_presupuestados),
            );
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

        $("#totalIngPresHoy").text(
            fmtPesosCompacto(sumaImporte(data.pres_ing)),
        );
        $("#bodyIngPresHoy").html(tablaMovimientos(data.pres_ing));
        $("#totalEgrPresHoy").text(
            fmtPesosCompacto(sumaImporte(data.pres_egr)),
        );
        $("#bodyEgrPresHoy").html(tablaMovimientos(data.pres_egr));

        if (data.exec_ing.length + data.exec_egr.length > 0) {
            $("#seccionEjecutadoHoy").show();
            $("#totalIngExecHoy").text(
                fmtPesosCompacto(sumaImporte(data.exec_ing)),
            );
            $("#bodyIngExecHoy").html(tablaMovimientos(data.exec_ing, true));
            $("#totalEgrExecHoy").text(
                fmtPesosCompacto(sumaImporte(data.exec_egr)),
            );
            $("#bodyEgrExecHoy").html(tablaMovimientos(data.exec_egr));
        } else {
            $("#seccionEjecutadoHoy").hide();
        }
    });
}

function tablaEgresosEjecutados(rows) {
    if (!rows || rows.length === 0)
        return '<label class="text-muted">Sin registros.</label>';

    const ordenadas = rows.slice().sort(function (a, b) {
        const chA = a.operacion === "CHEQUES" ? 0 : 1;
        const chB = b.operacion === "CHEQUES" ? 0 : 1;
        if (chA !== chB) return chA - chB;
        return Math.abs(Number(b.importe)) - Math.abs(Number(a.importe));
    });

    let html =
        '<div class="table-responsive"><table class="table table-sm mb-0">' +
        '<thead><tr><th>Sub-concepto</th><th>Detalle</th><th>Banco</th><th>Operación</th><th class="text-end">Importe</th></tr></thead><tbody>';

    ordenadas.forEach(function (r) {
        const esCheque = r.operacion === "CHEQUES";
        const operacion = esCheque
            ? '<span class="badge bg-warning text-dark">CHEQUE</span>'
            : '<span class="text-muted">' + escHtml(r.operacion) + "</span>";

        html +=
            "<tr" +
            (esCheque ? ' class="table-warning"' : "") +
            ">" +
            "<td>" +
            escHtml(r.subconcepto) +
            "</td>" +
            "<td>" +
            escHtml(r.detalle) +
            "</td>" +
            "<td>" +
            escHtml(r.banco) +
            "</td>" +
            "<td>" +
            operacion +
            "</td>" +
            '<td class="text-end fw-bold text-danger">' +
            fmtPesos(r.importe) +
            "</td>" +
            "</tr>";
    });

    return html + "</tbody></table></div>";
}

$(document).ready(function () {
    cargarHoy(new Date().toISOString().slice(0, 7));
});
