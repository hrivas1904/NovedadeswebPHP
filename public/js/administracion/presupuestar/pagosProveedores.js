let finnegansRows = [];

function procesarFinnegans() {
    const contenido = $("#contenidoCuentasPagar").val();
    if (!contenido.trim()) {
        $("#previewFinnegansWrapper").hide();
        $("#msgFinnegans").text("");
        finnegansRows = [];
        return;
    }
    $.post(
        PRESUPUESTAR_ROUTES.previewFinnegans,
        { contenido: contenido },
        function (data) {
            finnegansRows = data.rows;
            $("#msgFinnegans")
                .text(data.mensaje)
                .css("color", finnegansRows.length ? "green" : "inherit");
            renderPreviewFinnegans();
        },
    );
}

const OPERACIONES_CATALOGO = [
    "INGRESOS",
    "TRANSFERENCIAS",
    "CHEQUES",
    "EFECTIVO",
];

function optsOperacion(actual) {
    let out = "";
    OPERACIONES_CATALOGO.forEach(function (op) {
        out +=
            '<option value="' +
            op +
            '"' +
            (op === actual ? " selected" : "") +
            ">" +
            op +
            "</option>";
    });
    return out;
}

function optsSubconcepto(concepto, actual) {
    const lista = SUBCONCEPTOS_POR_CONCEPTO[concepto] || [];
    let out = "";
    lista.forEach(function (s) {
        out +=
            '<option value="' +
            s +
            '"' +
            (s === actual ? " selected" : "") +
            ">" +
            s +
            "</option>";
    });
    return out;
}

function renderPreviewFinnegans() {
    if (!finnegansRows.length) {
        $("#previewFinnegansWrapper").hide();
        return;
    }
    let html = "";
    finnegansRows.forEach(function (r, i) {
        let opts = "";
        CONCEPTOS_CATALOGO.forEach(function (c) {
            opts +=
                '<option value="' +
                c +
                '"' +
                (c === r.concepto ? " selected" : "") +
                ">" +
                c +
                "</option>";
        });
        html +=
            "<tr>" +
            "<td>" +
            r.fecha +
            "</td>" +
            "<td>" +
            r.detalle +
            "</td>" +
            '<td><input type="text" class="form-control form-control-sm input-comprobante-finnegans" data-idx="' +
            i +
            '" value="' +
            escAttr(r.comprobante) +
            '" placeholder="N° comprobante"></td>' +
            '<td><select class="form-select form-select-sm select-cuenta-finnegans" data-idx="' +
            i +
            '">' +
            optsCuenta(r.cuenta) +
            "</select></td>" +
            '<td><select class="form-select form-select-sm select-operacion-finnegans" data-idx="' +
            i +
            '">' +
            optsOperacion(r.operacion) +
            "</select></td>" +
            '<td><select class="form-select form-select-sm select-concepto-finnegans" data-idx="' +
            i +
            '">' +
            opts +
            "</select></td>" +
            '<td><select class="form-select form-select-sm select-subconcepto-finnegans" data-idx="' +
            i +
            '">' +
            optsSubconcepto(r.concepto, r.subconcepto) +
            "</select></td>" +
            '<td class="text-end fw-bold text-danger">' +
            fmtPesos(r.importe) +
            "</td>" +
            "</tr>";
    });
    $("#previewFinnegansBody").html(html);
    $("#cantidadFinnegans, #cantidadFinnegans2").text(finnegansRows.length);
    $("#previewFinnegansWrapper").show();
}

// delegados porque las filas se inyectan por AJAX
$(document).on("change", ".select-concepto-finnegans", function () {
    const idx = $(this).data("idx");
    const concepto = $(this).val();
    finnegansRows[idx].concepto = concepto;
    finnegansRows[idx].subconcepto =
        (SUBCONCEPTOS_POR_CONCEPTO[concepto] || [])[0] || "";
    $('.select-subconcepto-finnegans[data-idx="' + idx + '"]').html(
        optsSubconcepto(concepto, finnegansRows[idx].subconcepto),
    );
});

$(document).on("change", ".select-subconcepto-finnegans", function () {
    finnegansRows[$(this).data("idx")].subconcepto = $(this).val();
});

$(document).on("change", ".select-operacion-finnegans", function () {
    finnegansRows[$(this).data("idx")].operacion = $(this).val();
});

$(document).on("change", ".select-cuenta-finnegans", function () {
    const idx = $(this).data("idx");
    const cuenta = $(this).val();
    finnegansRows[idx].cuenta = cuenta;

    // Misma regla que ClasificadorOperacion::resolver (acá los importes siempre son negativos):
    // CAJA => EFECTIVO, cualquier banco => TRANSFERENCIAS
    finnegansRows[idx].operacion =
        cuenta === "CAJA" ? "EFECTIVO" : "TRANSFERENCIAS";
    $('.select-operacion-finnegans[data-idx="' + idx + '"]').val(
        finnegansRows[idx].operacion,
    );
});

$(document).on("input", ".input-comprobante-finnegans", function () {
    finnegansRows[$(this).data("idx")].comprobante = $(this).val();
});

$("#contenidoCuentasPagar").on("input", debounce(procesarFinnegans, 400));

$("#btnConfirmarFinnegans").on("click", function () {
    if (!finnegansRows.length) return;
    $.post(
        PRESUPUESTAR_ROUTES.confirmarFinnegans,
        { rows: finnegansRows },
        function (data) {
            $("#msgFinnegans")
                .text("✓ " + data.insertados + " pagos importados.")
                .css("color", "green");
            $("#contenidoCuentasPagar").val("");
            $("#previewFinnegansWrapper").hide();
            finnegansRows = [];
        },
    );
});

function optsCuenta(actual) {
    let out = "";
    CUENTAS_CATALOGO.forEach(function (c) {
        out +=
            '<option value="' +
            c +
            '"' +
            (c === actual ? " selected" : "") +
            ">" +
            c +
            "</option>";
    });
    return out;
}

function escAttr(s) {
    return String(s || "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;");
}
