let tablaMovimientos;

function getScrollY() {
    return window.innerWidth < 768 ? "28vh" : "50vh";
}

$('#btnLimpiarFiltros').on('click', function () {
    $('#inputFechaDesde').val('');
    $('#inputFechaHasta').val('');
    $('#selectCuentas').val('');
    $('#selectEstados').val('');
    $('#selectOperaciones').val('');
    $('#selectConceptos').val('');
    $('#inputSubconcepto').val('');
    $('#inputBuscador').val('');

    tablaMovimientos.ajax.reload();
});

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

function fmtFecha(f) {
    if (!f) return "";
    return f.split("-").reverse().join("/");
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

$(document).ready(function () {
    tablaMovimientos = $("#tablaMovimientos").DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: MOVIMIENTOS_ROUTES.data,
            data: function (d) {
                d.fecha_desde = $("#inputFechaDesde").val();
                d.fecha_hasta = $("#inputFechaHasta").val();
                d.cuenta = $("#selectCuentas").val();
                d.estado = $("#selectEstados").val();
                d.operacion = $("#selectOperaciones").val();
                d.concepto = $('#selectConceptos').val();
                d.subconcepto = $('#inputSubconcepto').val();
                d.buscar = $("#inputBuscador").val();
            },
        },
        columns: [
            {
                data: "id",
                orderable: false,
                className: "text-center",
                render: (id) =>
                    '<input type="checkbox" class="chk-movimiento" data-id="' +
                    id +
                    '">',
            },
            { data: "fecha", render: fmtFecha },
            { data: "ejecucion" },
            { data: "operacion" },
            { data: "cuenta" },
            { data: "concepto" },
            { data: "subconcepto" },
            { data: "detalle" },
            {
                data: "importe",
                className: "text-end",
                render: (v) => fmtPesos(v),
            },
        ],
        language: { url: "/js/es-ES.json" },
        order: [[1, "desc"]],
        pageLength: 25,
        searching: false,
        scrollY: getScrollY(),
    });

    $(
        "#inputFechaDesde, #inputFechaHasta, #selectCuentas, #selectEstados, #selectOperaciones, #selectConceptos",
    ).on("change", function () {
        tablaMovimientos.ajax.reload();
    });

    $("#inputBuscador, #inputSubconcepto").on(
        "input",
        debounce(function () {
            tablaMovimientos.ajax.reload();
        }, 400),
    );
});

function poblarConceptosManual() {
    let opts = "";
    CONCEPTOS_CATALOGO.forEach(function (c) {
        opts += '<option value="' + c + '">' + c + "</option>";
    });
    $("#manualConcepto").html(opts);
}

function limpiarFormManual() {
    $("#manualFecha").val(new Date().toISOString().slice(0, 10));
    $("#manualEstado").val("PRESUPUESTO");
    $("#manualSeccion").val("3 EGRESOS");
    $("#manualSubconcepto, #manualDetalle, #manualImporte").val("");
}

$("#btnAbrirManual").on("click", function () {
    poblarConceptosManual();
    limpiarFormManual();
});

$("#btnGuardarManual").on("click", function () {
    const detalle = $("#manualDetalle").val().trim();
    const importeRaw = $("#manualImporte").val().trim();
    if (!detalle || !importeRaw) {
        alert("Detalle e importe son obligatorios.");
        return;
    }

    $.post(MOVIMIENTOS_ROUTES.manual, {
        fecha: $("#manualFecha").val(),
        ejecucion: $("#manualEstado").val(),
        cuenta: $("#manualCuenta").val(),
        seccion: $("#manualSeccion").val(),
        concepto: $("#manualConcepto").val(),
        subconcepto: $("#manualSubconcepto").val(),
        detalle: detalle,
        importe: importeRaw.replace(/\./g, "").replace(",", "."),
    })
        .done(function () {
            bootstrap.Modal.getInstance(
                document.getElementById("modalMovimientoManual"),
            ).hide();
            tablaMovimientos.ajax.reload(null, false); // false = no vuelve a pagina 1
        })
        .fail(function (xhr) {
            alert(
                "Error al guardar: " +
                    (xhr.responseJSON?.message || "revisá los datos."),
            );
        });
});
