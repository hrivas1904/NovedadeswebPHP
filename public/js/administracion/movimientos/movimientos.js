let tablaMovimientos;

function getScrollY() {
    return window.innerWidth < 768 ? "28vh" : "56vh";
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
                render: function (id, type, row) {
                    const checked = seleccionadosMovimientos.hasOwnProperty(id)
                        ? "checked"
                        : "";
                    return (
                        '<input type="checkbox" class="chk-movimiento" data-id="' +
                        id +
                        '" data-importe="' +
                        row.importe +
                        '" ' +
                        checked +
                        ">"
                    );
                },
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
                    return valor;
                },
            },
            { data: "operacion" },
            {
                data: "cuenta",
                render: function (cuenta, type, row) {
                    if (type !== "display") return cuenta;
                    let opts = "";
                    CUENTAS_CATALOGO.forEach(function (c) {
                        opts += '<option value="' + c + '"' + (c === cuenta ? " selected" : "") + ">" + c + "</option>";
                    });
                    return '<select class="form-select form-select-sm select-cuenta-movimiento" data-id="' + row.id + '">' + opts + "</select>";
                },
            },
            {
                data: "concepto",
                render: function (concepto, type, row) {
                    if (type !== "display") return concepto;
                    let opts = "";
                    CONCEPTOS_CATALOGO.forEach(function (c) {
                        opts += '<option value="' + c + '"' + (c === concepto ? " selected" : "") + ">" + c + "</option>";
                    });
                    return '<select class="form-select form-select-sm select-concepto-movimiento" data-id="' + row.id + '">' + opts + "</select>";
                },
            },
            {
                data: "subconcepto",
                render: function (subconcepto, type, row) {
                    if (type !== "display") return subconcepto;
                    const lista = SUBCONCEPTOS_POR_CONCEPTO[row.concepto] || [];
                    let opts = "";
                    lista.forEach(function (s) {
                        opts += '<option value="' + s + '"' + (s === subconcepto ? " selected" : "") + ">" + s + "</option>";
                    });
                    return '<select class="form-select form-select-sm select-subconcepto-movimiento" data-id="' + row.id + '">' + opts + "</select>";
                },
            },
            {
                data: "detalle",
                render: function (detalle, type, row) {
                    if (type !== "display") return detalle;
                    return '<input type="text" class="form-control form-control-sm input-detalle-movimiento" data-id="' + row.id + '" value="' + (detalle || "").replace(/"/g, "&quot;") + '">';
                },
            },
            {
                data: "importe",
                className: "text-end",
                render: function (importe, type, row) {
                    if (type !== "display") return importe;
                    return '<input type="text" class="form-control form-control-sm text-end input-importe-movimiento" data-id="' + row.id + '" value="' + Number(importe).toFixed(2) + '" data-original="' + Number(importe).toFixed(2) + '">';
                },
            },
            {
                data: null,
                orderable: false,
                className: "text-center",
                render: function (data, type, row) {
                    let html = "";
                    if (row.ejecucion === "PRESUPUESTO") {
                        html +=
                            '<button type="button" class="btn btn-cambiar-estado" data-id="' +
                            row.id +
                            '" data-nuevo="CUMPLIDO" title="Marcar cumplido" style="color: var(--color-accent-green);"><i class="fs-5 fa-regular fa-square-check"></i></button> ';
                    } else if (row.ejecucion === "CUMPLIDO") {
                        html +=
                            '<button type="button" class="btn btn-cambiar-estado" data-id="' +
                            row.id +
                            '" data-nuevo="PRESUPUESTO" title="Volver a presupuesto" style="color: var(--color-default);"><i class="fs-5 fa-solid fa-rotate-left"></i></button> ';
                    }
                    html +=
                        '<button type="button" class="btn btn-duplicar" data-id="' +
                        row.id +
                        '" title="Duplicar" style="color: var(--color-default);"><i class="fs-5 fa-solid fa-copy"></i></button> ';
                    html +=
                        '<button type="button" class="btn btn-eliminar-movimiento" data-id="' +
                        row.id +
                        '" title="Eliminar" style="color: var(--color-accent-red);"><i class="fs-5 fa-regular fa-trash-can"></i></button>';
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

$(document).on("change", ".select-cuenta-movimiento", function () {
    const id = $(this).data("id");
    $.post(MOVIMIENTOS_ROUTES.cuenta.replace(":id", id), { cuenta: $(this).val() });
});

$(document).on("change", ".select-concepto-movimiento", function () {
    const id = $(this).data("id");
    const nuevoConcepto = $(this).val();

    $.post(MOVIMIENTOS_ROUTES.concepto.replace(":id", id), { concepto: nuevoConcepto });

    // El subconcepto ya no es valido para el concepto nuevo -- se repuebla
    // el select de esa misma fila con la lista correcta, sin arrastrar el anterior.
    const lista = SUBCONCEPTOS_POR_CONCEPTO[nuevoConcepto] || [];
    let opts = "";
    lista.forEach(function (s) { opts += '<option value="' + s + '">' + s + "</option>"; });
    const $selectSub = $('.select-subconcepto-movimiento[data-id="' + id + '"]');
    $selectSub.html(opts);

    if (lista.length) {
        $.post(MOVIMIENTOS_ROUTES.texto.replace(":id", id), { campo: "subconcepto", valor: lista[0] });
    }
});

$(document).on("change", ".select-subconcepto-movimiento", function () {
    const id = $(this).data("id");
    $.post(MOVIMIENTOS_ROUTES.texto.replace(":id", id), { campo: "subconcepto", valor: $(this).val() });
});

$(document).on("blur", ".input-detalle-movimiento", function () {
    const id = $(this).data("id");
    $.post(MOVIMIENTOS_ROUTES.texto.replace(":id", id), { campo: "detalle", valor: $(this).val() });
});

$(document).on("blur", ".input-importe-movimiento", function () {
    const $input = $(this);
    const raw = $input.val().trim();
    if (raw === String($input.data("original"))) return; // no cambio, no disparamos nada

    const id = $input.data("id");
    const importe = raw.replace(/\./g, "").replace(",", ".");

    $.post(MOVIMIENTOS_ROUTES.importe.replace(":id", id), { importe: importe }, function () {
        $input.data("original", Number(importe).toFixed(2));
    });
});
// Como la tabla es server-side (paginada), se guarda id+importe de cada
// fila tildada en un objeto que persiste aunque cambies de pagina, para
// que el contador y la suma sean correctos sin importar que pagina mires.
// =====================================================================
let seleccionadosMovimientos = {}; // { id: importe }

function idsSeleccionadosMovimientos() {
    return Object.keys(seleccionadosMovimientos).map(Number);
}

function actualizarResumenSeleccionMovimientos() {
    const ids = idsSeleccionadosMovimientos();
    const suma = Object.values(seleccionadosMovimientos).reduce(
        (a, b) => a + b,
        0,
    );
    $("#labelSeleccionadosMovimientos").text(
        ids.length +
            " SELECCIONADOS" +
            (ids.length ? " · " + fmtPesos(suma) : ""),
    );
}

function limpiarSeleccionMovimientos() {
    seleccionadosMovimientos = {};
    $("#checkSeleccionarTodo").prop("checked", false);
    actualizarResumenSeleccionMovimientos();
}

$(document).on("change", ".chk-movimiento", function () {
    const id = $(this).data("id");
    const importe = Number($(this).data("importe"));

    if ($(this).is(":checked")) {
        seleccionadosMovimientos[id] = importe;
    } else {
        delete seleccionadosMovimientos[id];
    }
    actualizarResumenSeleccionMovimientos();
});

$(document).on("change", "#checkSeleccionarTodo", function () {
    const checked = $(this).is(":checked");
    $(".chk-movimiento").each(function () {
        const id = $(this).data("id");
        const importe = Number($(this).data("importe"));
        $(this).prop("checked", checked);
        if (checked) {
            seleccionadosMovimientos[id] = importe;
        } else {
            delete seleccionadosMovimientos[id];
        }
    });
    actualizarResumenSeleccionMovimientos();
});

$(document).on("click", "#btnMarcarCumplido", function () {
    const ids = idsSeleccionadosMovimientos();
    if (!ids.length) {
        alert("Seleccioná al menos un movimiento.");
        return;
    }

    $.post(
        MOVIMIENTOS_ROUTES.estadoMasivo,
        { ids: ids, ejecucion: "CUMPLIDO" },
        function () {
            limpiarSeleccionMovimientos();
            tablaMovimientos.ajax.reload(null, false);
        },
    );
});

$(document).on("click", "#btnVolverPresupuesto", function () {
    const ids = idsSeleccionadosMovimientos();
    if (!ids.length) {
        alert("Seleccioná al menos un movimiento.");
        return;
    }

    $.post(
        MOVIMIENTOS_ROUTES.estadoMasivo,
        { ids: ids, ejecucion: "PRESUPUESTO" },
        function () {
            limpiarSeleccionMovimientos();
            tablaMovimientos.ajax.reload(null, false);
        },
    );
});

$(document).on("click", "#btnDuplicar", function () {
    const ids = idsSeleccionadosMovimientos();
    if (!ids.length) {
        alert("Seleccioná al menos un movimiento.");
        return;
    }

    $.post(MOVIMIENTOS_ROUTES.duplicarMasivo, { ids: ids }, function (data) {
        limpiarSeleccionMovimientos();
        tablaMovimientos.ajax.reload(null, false);
        Swal.fire({
            title: 'Operación exitosa!',
            text: data.duplicados + " movimiento(s) duplicado(s).",
            icon: 'success',
            timer: 1200,
            showConfirmButton: false
        })
    });
});

$(document).on("click", "#btnCambiarFechaMasiva", function () {
    const ids = idsSeleccionadosMovimientos();
    if (!ids.length) {
        Swal.fire({
            title: 'Atención',
            text: "Seleccioná al menos un movimiento.",
            icon: 'warning',
            timer: 1200,
            showConfirmButton: false
        });
        return;
    }

    const fecha = $("#inputCambioFechaMasiva").val();
    if (!fecha) {
        Swal.fire({
            title: 'Atención',
            text: "Elegí una fecha primero.",
            icon: 'warning',
            timer: 1200,
            showConfirmButton: false
        });
        return;
    }

    $.post(MOVIMIENTOS_ROUTES.fechaMasiva, { ids: ids, fecha: fecha }, function (data) {
        limpiarSeleccionMovimientos();
        tablaMovimientos.ajax.reload(null, false);
        Swal.fire({
            title: 'Operación exitosa!',
            text: data.actualizados + " movimiento(s) reprogramado(s).",
            icon: 'success',
            timer: 1200,
            showConfirmButton: false
        });
    });
});

$(document).on("click", "#btnEliminar", function () {
    const ids = idsSeleccionadosMovimientos();
    if (!ids.length) {
        Swal.fire({
            title: 'Atención',
            text: "Seleccioná al menos un movimiento.",
            icon: 'warning',
            timer: 1200,
            showConfirmButton: false
        });
        return;
    }

    Swal.fire({
        title: "¿Eliminar " + ids.length + " movimiento(s)?",
        text: "Se eliminaran los siguientes registros.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn btn-danger me-2",
            cancelButton: "btn btn-secondary",
        },
    }).then(function (result) {
        if (result.isConfirmed) {
            $.post(
                MOVIMIENTOS_ROUTES.eliminarMasivo,
                { ids: ids },
                function () {
                    limpiarSeleccionMovimientos();
                    tablaMovimientos.ajax.reload(null, false);
                },
            );
        }
    });
});