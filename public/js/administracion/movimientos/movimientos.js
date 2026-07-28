let tablaMovimientos;

function getScrollY() {
    return window.innerWidth < 768 ? "28vh" : "50vh";
}

$("#btnLimpiarFiltros").on("click", function () {
    $("#inputFechaDesde").val("");
    $("#inputFechaHasta").val("");
    $("#selectCuentas").val("");
    $("#selectEstados").val("");
    $("#selectOperaciones").val("");
    $("#selectConceptos").val("");
    $("#inputSubconcepto").val("");
    $("#inputBuscador").val("");

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
                d.concepto = $("#selectConceptos").val();
                d.subconcepto = $("#inputSubconcepto").val();
                d.buscar = $("#inputBuscador").val();
            },
        },
        columns: [
            {
                data: "id",
                orderable: false,
                className: "text-center",
                visible: false,
                render: (id) =>
                    '<input type="checkbox" class="chk-movimiento" data-id="' +
                    id +
                    '">',
            },
            {
                data: "fecha",
                render: function (fecha, type, row) {
                    if (type !== "display") return fecha;
                    return (
                        '<input type="date" class="form-control form-control-sm input-fecha-movimiento" data-id="' +
                        row.id +
                        '" value="' +
                        fecha +
                        '">'
                    );
                },
            },
            {
                data: "ejecucion",
                render: function (valor) {
                    if (valor === "PRESUPUESTO") {
                        return (
                            '<span class="badge" style="background-color: var(--color-second);">' +
                            valor +
                            "</span>"
                        );
                    }
                    if (valor === "CUMPLIDO") {
                        return (
                            '<span class="badge" style="background-color: var( --color-accent-green);">' +
                            valor +
                            "</span>"
                        );
                    }
                    return valor; // EJECUTADO / PENDIENTE quedan como texto plano, sin color pedido
                },
            },
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
            {
                data: null,
                orderable: false,
                className: "text-center",
                render: function (data, type, row) {
                    let html = "";
                    if (row.ejecucion === "PRESUPUESTO") {
                        html +=
                            '<button type="button" class="btn btn-sm btn-primary btn-cambiar-estado" data-id="' +
                            row.id +
                            '" data-nuevo="CUMPLIDO" title="Marcar cumplido"><i class="fa-solid fa-check"></i></button> ';
                    } else if (row.ejecucion === "CUMPLIDO") {
                        html +=
                            '<button type="button" class="btn btn-sm btn-secondary btn-cambiar-estado" data-id="' +
                            row.id +
                            '" data-nuevo="PRESUPUESTO" title="Volver a presupuesto"><i class="fa-solid fa-rotate-left"></i></button> ';
                    }
                    html +=
                        '<button type="button" class="btn btn-sm btn-secondary btn-duplicar" data-id="' +
                        row.id +
                        '" title="Duplicar"><i class="fa-solid fa-copy"></i></button> ';
                    html +=
                        '<button type="button" class="btn btn-sm btn-danger btn-eliminar-movimiento" data-id="' +
                        row.id +
                        '" title="Eliminar"><i class="fa-solid fa-trash"></i></button>';
                    return html;
                },
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

$(document).on("change", ".input-fecha-movimiento", function () {
    const id = $(this).data("id");
    const fecha = $(this).val();

    if (!fecha) return;

    $.post(MOVIMIENTOS_ROUTES.fecha.replace(":id", id), { fecha: fecha })
        .done(function () {
            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Fecha reprogramada correctamente.",
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        })
        .fail(function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo actualizar la fecha.",
            });
        });
});

$(document).on("click", ".btn-cambiar-estado", function () {
    const id = $(this).data("id");
    const nuevo = $(this).data("nuevo");

    $.post(
        MOVIMIENTOS_ROUTES.estado.replace(":id", id),
        { ejecucion: nuevo },
        function () {
            tablaMovimientos.ajax.reload(null, false);
        },
    );
});

$(document).on("click", ".btn-duplicar", function () {
    const id = $(this).data("id");

    $.post(MOVIMIENTOS_ROUTES.duplicar.replace(":id", id), {}, function () {
        tablaMovimientos.ajax.reload(null, false);
    });
});

$(document).on("click", ".btn-eliminar-movimiento", function () {
    const id = $(this).data("id");

    Swal.fire({
        title: "¿Eliminar este movimiento?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn btn-primary me-2",
            cancelButton: "btn btn-secondary",
        },
    }).then(function (result) {
        if (result.isConfirmed) {
            $.post(
                MOVIMIENTOS_ROUTES.eliminar.replace(":id", id),
                {},
                function () {
                    tablaMovimientos.ajax.reload(null, false);
                },
            );
        }
    });
});
