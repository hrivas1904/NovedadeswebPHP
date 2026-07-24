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

function sumaImporte(rows) {
    return (rows || []).reduce((a, r) => a + Number(r.importe || 0), 0);
}

function tablaMovimientos(rows) {
    if (!rows || rows.length === 0)
        return '<label class="text-muted">Sin registros.</label>';
    let html =
        '<div class="table-responsive"><table class="table table-sm mb-0">' +
        '<thead><tr><th>Cuenta</th><th>Concepto</th><th>Sub-concepto</th><th>Detalle</th><th class="text-end">Importe</th></tr></thead><tbody>';
    rows.forEach(function (r) {
        const color = Number(r.importe) >= 0 ? "text-success" : "text-danger";
        html +=
            "<tr><td>" +
            (r.banco || "") +
            "</td><td>" +
            (r.concepto || "") +
            "</td>" +
            "<td>" +
            (r.subconcepto || "") +
            "</td><td>" +
            (r.detalle || "") +
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

        $("#cardBodyMacro").text(fmtPesos(r.ingresos_presupuestados));
        $("#cardBodyNacion").text(fmtPesos(r.egresos_presupuestados));
        $("#cardBodyFrances9").text(fmtPesos(r.neto_presupuestado));
        $("#cardBodyFrances1").text(fmtPesos(r.saldo_bancos));

        if (r.necesidad_rescate < 0)
            $("#lblNecesidadTitulo").text("⚠ Necesidad de rescate de FCI");
        else if (r.necesidad_rescate > 0)
            $("#lblNecesidadTitulo").text("✓ Sin necesidad de rescate");
        else $("#lblNecesidadTitulo").text("POSICIÓN EQUILIBRADA");
        $("#lblNecesidadMonto").text(fmtPesos(r.necesidad_rescate));

        $("#totalIngPresHoy").text(fmtPesos(sumaImporte(data.pres_ing)));
        $("#bodyIngPresHoy").html(tablaMovimientos(data.pres_ing));
        $("#totalEgrPresHoy").text(fmtPesos(sumaImporte(data.pres_egr)));
        $("#bodyEgrPresHoy").html(tablaMovimientos(data.pres_egr));

        if (data.exec_ing.length + data.exec_egr.length > 0) {
            $("#seccionEjecutadoHoy").show();
            $("#totalIngExecHoy").text(fmtPesos(sumaImporte(data.exec_ing)));
            $("#bodyIngExecHoy").html(tablaMovimientos(data.exec_ing));
            $("#totalEgrExecHoy").text(fmtPesos(sumaImporte(data.exec_egr)));
            $("#bodyEgrExecHoy").html(tablaMovimientos(data.exec_egr));
        } else {
            $("#seccionEjecutadoHoy").hide();
        }
    });
}

$(document).ready(function () {
    cargarHoy(new Date().toISOString().slice(0, 7));
});
