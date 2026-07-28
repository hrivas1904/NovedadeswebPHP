let bancoSeleccionado = "MACRO";
let formatoSeleccionado = "PEGADO";
let filasBancos = [];

const COLUMNAS_POR_COMBINACION = {
    "MACRO|PEGADO": [
        "Fecha",
        "Nro",
        "CódOp",
        "Descripción",
        "Importe",
        "Saldo",
    ],
    "MACRO|CONCILIACION": [
        "Fecha",
        "Operación",
        "Concepto",
        "Detalle",
        "Nro",
        "Importe",
        "—",
        "Saldo",
    ],
    "NACION|PEGADO": ["Fecha+Hora", "Descripción", "Crédito", "Débito"],
    "NACION|CONCILIACION": [
        "Fecha",
        "—",
        "Concepto",
        "Detalle",
        "Importe (con signo)",
        "Saldo",
    ],
    "FRANCES (986)|PEGADO": [
        "Fecha",
        "Descripción",
        "CódOp",
        "Crédito",
        "Débito",
        "Saldo",
    ],
    "FRANCES (986)|CONCILIACION": [
        "Fecha",
        "—",
        "Concepto",
        "Detalle",
        "Importe (con signo)",
        "Saldo",
    ],
    "FRANCES (1001)|PEGADO": [
        "Fecha",
        "Descripción",
        "CódOp",
        "Crédito",
        "Débito",
        "Saldo",
    ],
    "FRANCES (1001)|CONCILIACION": [
        "Fecha",
        "—",
        "Concepto",
        "Detalle",
        "Importe (con signo)",
        "Saldo",
    ],
};

const PLACEHOLDER_POR_BANCO = {
    MACRO: "Pegá los movimientos de MACRO...",
    NACION: "Pegá los movimientos de NACIÓN...",
    "FRANCES (986)": "Pegá los movimientos de FRANCÉS (986)...",
    "FRANCES (1001)": "Pegá los movimientos de FRANCÉS (1001)...",
};

function actualizarHeaderColumnas() {
    const key = bancoSeleccionado + "|" + formatoSeleccionado;
    const columnas = COLUMNAS_POR_COMBINACION[key] || [];
    $("#headerColumnasBancos").html(
        columnas.length
            ? "<strong></strong>&nbsp; " +
                  columnas.join(" &nbsp;|&nbsp; ")
            : "",
    );
}

function actualizarPlaceholder() {
    $("#contenidoBancos").attr(
        "placeholder",
        PLACEHOLDER_POR_BANCO[bancoSeleccionado] || "Pegá los movimientos...",
    );
}

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

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

function renderPreviewBancos() {
    if (!filasBancos.length) {
        $("#previewBancosWrapper").remove();
        return;
    }

    let opts = "";
    CONCEPTOS_CATALOGO.forEach(function (c) {
        opts += '<option value="' + c + '">' + c + "</option>";
    });

    let html =
        '<div id="previewBancosWrapper" class="mt-3">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="text-muted small">' +
        filasBancos.length +
        " movimientos</span>" +
        '<button type="button" id="btnConfirmarBancos" class="btn btn-primary btn-sm">Importar ' +
        filasBancos.length +
        " movimientos</button>" +
        "</div>" +
        '<div class="table-responsive" style="max-height:400px; overflow-y:auto;">' +
        '<table class="table table-sm">' +
        '<thead><tr><th>Fecha</th><th>Cuenta</th><th>Concepto</th><th>Sub-concepto</th><th>Detalle</th><th class="text-end">Importe</th></tr></thead>' +
        "<tbody>";

    filasBancos.forEach(function (r, i) {
        let optsFila = opts.replace(
            'value="' + r.concepto + '"',
            'value="' + r.concepto + '" selected',
        );
        html +=
            "<tr>" +
            "<td>" +
            r.fecha +
            "</td>" +
            "<td>" +
            r.banco +
            "</td>" +
            '<td><select class="form-select form-select-sm select-concepto-banco" data-idx="' +
            i +
            '">' +
            optsFila +
            "</select></td>" +
            '<td><input type="text" class="form-control form-control-sm input-subconcepto-banco" data-idx="' +
            i +
            '" value="' +
            (r.subconcepto || "") +
            '"></td>' +
            "<td>" +
            r.detalle +
            "</td>" +
            '<td class="text-end fw-bold ' +
            (r.importe >= 0 ? "text-success" : "text-danger") +
            '">' +
            fmtPesos(r.importe) +
            "</td>" +
            "</tr>";
    });

    html += "</tbody></table></div></div>";

    $("#previewBancosWrapper").remove();
    $("#contenidoBancos").closest(".mt-2").after(html);
}

function procesarBancos() {
    const contenido = $("#contenidoBancos").val();
    if (!contenido.trim()) {
        filasBancos = [];
        renderPreviewBancos();
        $("#msgBancos").text("");
        return;
    }

    $.post(
        IMPORTACION_ROUTES.bancosPreview,
        {
            contenido: contenido,
            banco: bancoSeleccionado,
            formato: formatoSeleccionado,
        },
        function (data) {
            filasBancos = data.rows;
            $("#msgBancos")
                .text(data.mensaje)
                .css("color", filasBancos.length ? "green" : "inherit");
            renderPreviewBancos();
        },
    );
}

$(document).ready(function () {
    if (!$("#msgBancos").length) {
        $("#contenidoBancos").after(
            '<label class="mt-2" id="msgBancos"></label>',
        );
    }

    actualizarHeaderColumnas();
    actualizarPlaceholder();

    // Cada vez que la sub-vista de Importacion se recarga (venir de Caja/TSV
    // y volver a Bancos), si la que quedo en pantalla es esta, reseteamos el
    // estado interno para que coincida con lo que el HTML muestra por default.
    $(document).on("subvista:cargada", function () {
        if (!$("#contenidoBancos").length) return; // no es la sub-vista de Bancos

        bancoSeleccionado = "MACRO";
        formatoSeleccionado = "PEGADO";
        filasBancos = [];

        actualizarHeaderColumnas();
        actualizarPlaceholder();
    });

    $(document).on("click", ".btn-opcion-banco", function () {
        $(".btn-opcion-banco").removeClass("active");
        $(this).addClass("active");
        bancoSeleccionado = $(this).data("banco");
        actualizarHeaderColumnas();
        actualizarPlaceholder();
        procesarBancos();
    });

    $(document).on("click", ".btn-opcion-import", function () {
        $(".btn-opcion-import").removeClass("active");
        $(this).addClass("active");
        formatoSeleccionado = $(this).data("formato");
        actualizarHeaderColumnas();
        procesarBancos();
    });

    $(document).on("input", "#contenidoBancos", debounce(procesarBancos, 400));

    $(document).on("change", ".select-concepto-banco", function () {
        filasBancos[$(this).data("idx")].concepto = $(this).val();
    });

    $(document).on("input", ".input-subconcepto-banco", function () {
        filasBancos[$(this).data("idx")].subconcepto = $(this).val();
    });

    $(document).on("click", "#btnConfirmarBancos", function () {
        if (!filasBancos.length) return;

        $.post(
            IMPORTACION_ROUTES.bancosConfirmar,
            { rows: filasBancos },
            function (data) {
                $("#msgBancos")
                    .text("✓ " + data.insertados + " movimientos importados.")
                    .css("color", "green");
                $("#contenidoBancos").val("");
                filasBancos = [];
                renderPreviewBancos();
            },
        ).fail(function (xhr) {
            alert(
                "Error al importar: " +
                    (xhr.responseJSON?.message || "revisá los datos."),
            );
        });
    });
});
