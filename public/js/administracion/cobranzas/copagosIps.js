/* =========================================================
   COPAGOS IPS - GESTIÓN DE COBRANZAS
   Requiere: window.COPAGOS_IPS_ROUTES (inyectado desde Blade)
   ========================================================= */

let tablaCruce = null;
let filtroCruce = "TODOS";

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $.fn.dataTable.ext.search.push(filtrarCruce);

    inicializarTablaCruce();
});

/* =========================================================
   HELPERS
   ========================================================= */

function formatMoney(n) {
    n = Number(n) || 0;
    return (n < 0 ? "-$" : "$") + Math.abs(Math.round(n)).toLocaleString("es-AR");
}

function escapeHtml(value) {
    return $("<div>").text(value ?? "").html();
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: "success",
        title: mensaje,
        timer: 1800,
        showConfirmButton: false,
    });
}

function mostrarError(err, textoPorDefecto) {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: (err && err.responseJSON && err.responseJSON.message) || textoPorDefecto,
    });
}

function mostrarProcesando(titulo) {
    Swal.fire({
        title: titulo,
        html:
            '<div class="mb-2">El archivo puede tardar unos segundos en procesarse.</div>' +
            '<small class="text-muted">Por favor, no cierres ni recargues la página.</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function () {
            Swal.showLoading();
        },
    });
}

/* =========================================================
   RENDERS DE COLUMNAS
   ========================================================= */

function renderTexto(data, type) {
    if (type !== "display") return data ?? "";
    return escapeHtml(data);
}

function renderLista(data, type) {
    const valor = Array.isArray(data) ? data.join(", ") : "";
    if (type !== "display") return valor;
    return escapeHtml(valor);
}

function renderTelefono(data, type) {
    const valor = data || "—";
    if (type !== "display") return valor;
    return escapeHtml(valor);
}

function renderMoneda(data, type) {
    const valor = Number(data) || 0;
    if (type !== "display") return valor;
    return formatMoney(valor);
}

function renderDiferencia(data, type) {
    const valor = Number(data) || 0;
    if (type !== "display") return valor;

    const clase = valor > 1000 ? "text-danger" : "text-success";
    return '<span class="' + clase + ' fw-semibold">' + formatMoney(valor) + "</span>";
}

function renderEstado(estado, type, row) {
    if (type !== "display") return estado;

    const badges = {
        COBRADO: "success",
        "COBRO PARCIAL": "warning",
        "NO COBRADO": "danger",
    };
    const badge = badges[estado] ?? "secondary";

    return (
        '<span class="badge bg-' + badge + '">' + escapeHtml(estado) + "</span>" +
        '<div class="form-check form-check-inline ms-2 align-middle">' +
            '<input class="form-check-input" type="checkbox" ' +
                'data-resuelto="' + escapeHtml(row.nombreNorm) + '" ' +
                (row.resuelto ? "checked" : "") + ">" +
            '<label class="form-check-label small text-muted">Resuelto</label>' +
        "</div>"
    );
}

function renderNota(nota, type, row) {
    if (type !== "display") return nota ?? "";

    return (
        '<input type="text" class="form-control form-control-sm" ' +
            'data-nota="' + escapeHtml(row.nombreNorm) + '" ' +
            'value="' + escapeHtml(nota ?? "") + '" ' +
            'placeholder="Agregar nota…">'
    );
}

/* =========================================================
   FILTRO PERSONALIZADO (botones de estado)
   ========================================================= */

function filtrarCruce(settings, data, dataIndex, paciente) {
    if (settings.nTable.id !== "tablaCruce" || !paciente) return true;

    switch (filtroCruce) {
        case "PENDIENTES":
            return paciente.estado !== "COBRADO" && !paciente.resuelto;
        case "RESUELTOS":
            return Boolean(paciente.resuelto);
        case "NO COBRADO":
        case "COBRO PARCIAL":
        case "COBRADO":
            return paciente.estado === filtroCruce;
        default:
            return true;
    }
}

/* =========================================================
   TABLA DE CRUCE
   ========================================================= */

function inicializarTablaCruce() {
    tablaCruce = $("#tablaCruce").DataTable({
        language: {
            url: "/js/es-ES.json",
        },
        layout: {
            topStart: "pageLength",
            topEnd: { buttons: [botonExcelCruce()] },
            bottomStart: "info",
            bottomEnd: "paging",
        },
        ajax: {
            url: COPAGOS_IPS_ROUTES.cruce,
            dataSrc: function (json) {
                const pacientes = json.data ?? [];
                $("#kpisCruce").toggleClass("d-none", pacientes.length === 0);
                actualizarKpis(pacientes);
                return pacientes;
            },
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "dt-control text-center",
                defaultContent: '<i class="fa-solid fa-chevron-down"></i>',
            },
            { data: "nombreDisplay", render: renderTexto },
            { data: "fins", render: renderLista },
            { data: "telefono", render: renderTelefono },
            { data: "periodos", render: renderLista },
            { data: "totalLiquidado", className: "text-end", render: renderMoneda },
            { data: "totalCobrado", className: "text-end", render: renderMoneda },
            { data: "diferencia", className: "text-end", render: renderDiferencia },
            {
                // Reservada para deuda de medicamentos (punto 3)
                data: null,
                defaultContent: "—",
                orderable: false,
                searchable: false,
            },
            { data: "estado", render: renderEstado },
            { data: "nota", render: renderNota },
        ],
        scrollX: false,
        autoWidth: true,
        drawCallback: function () {
            actualizarContadorCruce();
        },
    });
}

function botonExcelCruce() {
    return {
        extend: "excelHtml5",
        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
        className: "btn btn-sm btn-success",
        title: null,
        filename: function () {
            const hoy = new Date().toISOString().slice(0, 10);
            return "Cobranzas_IPS_" + filtroCruce.replace(" ", "_") + "_" + hoy;
        },
        exportOptions: {
            // Sin la columna del chevron (0) ni la de medicamentos aún vacía (8)
            columns: [1, 2, 3, 4, 5, 6, 7, 9, 10],
            // Los renders devuelven el valor crudo cuando type !== "display"
            orthogonal: "export",
            format: {
                header: function (data, col) {
                    const titulos = { 1: "Paciente", 9: "Estado", 10: "Nota" };
                    return titulos[col] ?? $("<div>").html(data).text().trim();
                },
                body: function (data, rowIdx, col) {
                    if (col !== 9) return data;
                    const p = tablaCruce.row(rowIdx).data();
                    return p.resuelto ? p.estado + " (RESUELTO)" : p.estado;
                },
            },
        },
    };
}

/* =========================================================
   KPIs Y CONTADOR
   ========================================================= */

function actualizarKpis(pacientes) {
    let totalLiquidado = 0;
    let totalCobrado = 0;
    let pendientes = 0;

    pacientes.forEach(function (p) {
        totalLiquidado += Number(p.totalLiquidado || 0);
        totalCobrado += Number(p.totalCobrado || 0);
        if (p.estado !== "COBRADO" && !p.resuelto) pendientes++;
    });

    $("#kpiLiquidado").text(formatMoney(totalLiquidado));
    $("#kpiCobrado").text(formatMoney(totalCobrado));
    $("#kpiDiferencia").text(formatMoney(totalLiquidado - totalCobrado));
    $("#kpiPendientes").text(pendientes);
}

function actualizarContadorCruce() {
    const total = tablaCruce.rows().count();
    const visibles = tablaCruce.rows({ search: "applied" }).count();
    $("#contadorCruce").text(visibles + " de " + total);
}

/* =========================================================
   EVENTOS: FILTROS
   ========================================================= */

$(document).on("click", ".btnFiltroCopago", function () {
    filtroCruce = $(this).data("filtro");

    $(".btnFiltroCopago")
        .removeClass("btn-dark active")
        .addClass("btn-outline-secondary");

    $(this)
        .removeClass("btn-outline-secondary")
        .addClass("btn-dark active");

    tablaCruce.draw();
});

/* =========================================================
   EVENTOS: DETALLE DE PAGOS EN CAJA
   ========================================================= */

$(document).on("click", "#tablaCruce td.dt-control", function () {
    const tr = $(this).closest("tr");
    const row = tablaCruce.row(tr);

    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass("shown");
    } else {
        row.child(renderDetallePagos(row.data())).show();
        tr.addClass("shown");
    }
});

function renderDetallePagos(paciente) {
    if (!paciente.pagos || paciente.pagos.length === 0) {
        return '<div class="text-muted small p-2">No se encontró ningún pago de copago en caja con este nombre.</div>';
    }

    let html =
        '<table class="table table-sm mb-0">' +
            "<thead><tr>" +
                "<th>Fecha</th><th>Nombre en caja</th><th class=\"text-end\">Importe</th>" +
            "</tr></thead><tbody>";

    paciente.pagos.forEach(function (pg) {
        html +=
            "<tr>" +
                "<td>" + escapeHtml(pg.fecha ?? "") + "</td>" +
                "<td>" + escapeHtml(pg.nombre ?? "") + "</td>" +
                '<td class="text-end">' + formatMoney(pg.importe) + "</td>" +
            "</tr>";
    });

    return html + "</tbody></table>";
}

/* =========================================================
   EVENTOS: NOTAS Y RESUELTO
   ========================================================= */

$(document).on("blur", "#tablaCruce input[data-nota]", function () {
    const input = $(this);

    $.ajax({
        url: COPAGOS_IPS_ROUTES.guardarNota,
        method: "POST",
        data: {
            nombre_norm: input.data("nota"),
            nota: input.val(),
        },
        error: function (err) {
            mostrarError(err, "No se pudo guardar la nota.");
        },
    });
});

$(document).on("change", "#tablaCruce input[data-resuelto]", function () {
    const checkbox = $(this);
    const marcado = this.checked;
    const fila = tablaCruce.row(checkbox.closest("tr")).data();

    $.ajax({
        url: COPAGOS_IPS_ROUTES.marcarResuelto,
        method: "POST",
        data: {
            nombre_norm: checkbox.data("resuelto"),
            resuelto: marcado ? 1 : 0,
            total: marcado ? fila.totalLiquidado : null,
        },
        success: function () {
            tablaCruce.ajax.reload(null, false);
        },
        error: function (err) {
            checkbox.prop("checked", !marcado);
            mostrarError(err, "No se pudo actualizar el estado.");
        },
    });
});

/* =========================================================
   EVENTOS: CARGA DE ARCHIVOS
   ========================================================= */

$(document).on("change", "#inputFileLiqIps", function () {
    subirArchivoCopagos(this, COPAGOS_IPS_ROUTES.cargarLiquidacion, null);
});

$(document).on("change", "#inputFileComprobantes", function () {
    subirArchivoCopagos(this, COPAGOS_IPS_ROUTES.cargarCaja, "Procesando comprobantes de caja");
});

$(document).on("change", "#inputFilePacientes", function () {
    const input = this;
    const archivo = input.files[0];

    if (!archivo) return;

    if (!/\.pdf$/i.test(archivo.name)) {
        subirArchivoCopagos(input, COPAGOS_IPS_ROUTES.cargarPacientes, null);
        return;
    }

    parsePatientListPDF(archivo)
        .then(function (filas) {
            enviarPacientesPdf(input, archivo.name, filas);
        })
        .catch(function (e) {
            Swal.fire({ icon: "error", title: "Error", text: e.message || "No se pudo leer el PDF." });
            $(input).val("");
        });
});

function subirArchivoCopagos(input, url, tituloProcesando) {
    const archivo = input.files[0];
    if (!archivo) return;

    const fd = new FormData();
    fd.append("archivo", archivo);

    if (tituloProcesando) mostrarProcesando(tituloProcesando);

    $.ajax({
        url: url,
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            mostrarExito(res.message);
            tablaCruce.ajax.reload(null, false);
        },
        error: function (err) {
            mostrarError(err, "No se pudo cargar el archivo.");
        },
        complete: function () {
            $(input).val("");
        },
    });
}

function enviarPacientesPdf(input, nombreArchivo, filas) {
    $.ajax({
        url: COPAGOS_IPS_ROUTES.cargarPacientesPdf,
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            nombre_archivo: nombreArchivo,
            filas: filas,
        }),
        success: function (res) {
            mostrarExito(res.message);
            tablaCruce.ajax.reload(null, false);
        },
        error: function (err) {
            mostrarError(err, "No se pudo cargar el archivo.");
        },
        complete: function () {
            $(input).val("");
        },
    });
}

/* =========================================================
   EVENTOS: SNAPSHOT MENSUAL
   ========================================================= */

$(document).on("click", "#btnGuardarSnapshot", function () {
    const btn = $(this);
    const mes = $("#mesSnapshot").val();

    if (!mes) {
        Swal.fire({ icon: "warning", title: "Seleccioná un mes" });
        return;
    }

    $.ajax({
        url: COPAGOS_IPS_ROUTES.guardarSnapshot,
        method: "POST",
        data: { mes: mes },
        beforeSend: function () {
            btn.prop("disabled", true);
        },
        success: function (res) {
            mostrarExito(res.message);
        },
        error: function (err) {
            mostrarError(err, "No se pudo guardar el snapshot.");
        },
        complete: function () {
            btn.prop("disabled", false);
        },
    });
});

/* =========================================================
   PARSEO PDF DE PACIENTES (pdf.js)
   Devuelve una Promise con [{ nombre, telefono, dni }]
   ========================================================= */

async function parsePatientListPDF(file) {
    const buf = await file.arrayBuffer();
    const doc = await window.pdfjsLib.getDocument({ data: buf }).promise;
    const lines = [];

    for (let p = 1; p <= doc.numPages; p++) {
        const page = await doc.getPage(p);
        const content = await page.getTextContent();
        const byY = new Map();

        content.items.forEach(function (item) {
            if (!item.str || !item.str.trim()) return;
            const y = Math.round(item.transform[5]);
            if (!byY.has(y)) byY.set(y, []);
            byY.get(y).push(item);
        });

        const ys = Array.from(byY.keys()).sort(function (a, b) { return b - a; });

        ys.forEach(function (y) {
            const items = byY.get(y).sort(function (a, b) {
                return a.transform[4] - b.transform[4];
            });

            let text = "";
            let lastEndX = null;

            items.forEach(function (it) {
                if (lastEndX !== null && it.transform[4] - lastEndX > 4) text += " ";
                text += it.str;
                lastEndX = it.transform[4] + (it.width || 0);
            });

            if (text.trim()) lines.push(text.trim());
        });
    }

    if (!lines.length) {
        throw new Error("No pude extraer texto del PDF (¿es un PDF escaneado como imagen?).");
    }

    const dniRe = /D\.?N\.?I\.?\s*[:\-]?\s*([0-9]{6,10})/i;
    const records = [];
    let current = null;

    lines.forEach(function (line) {
        if (dniRe.test(line)) {
            if (current) records.push(current);
            current = { lines: [line] };
        } else if (current) {
            current.lines.push(line);
        }
    });

    if (current) records.push(current);

    if (!records.length) {
        throw new Error("No encontré ninguna línea con 'D.N.I.' en el PDF.");
    }

    const rows = [];

    records.forEach(function (rec) {
        const nameLine = rec.lines[0];
        const dniMatch = nameLine.match(dniRe);
        const nameIdx = nameLine.search(/D\.?N\.?I\./i);
        const nombre = (nameIdx > -1 ? nameLine.slice(0, nameIdx) : nameLine).trim();

        if (!nombre) return;

        let combined = rec.lines.join(" ");
        if (dniMatch) combined = combined.replace(dniMatch[0], " ");

        const phoneMatches = combined.match(/\d{6,10}/g);
        const telefono = phoneMatches && phoneMatches.length
            ? phoneMatches[phoneMatches.length - 1]
            : null;

        if (!telefono) return;

        rows.push({
            nombre: nombre,
            telefono: telefono,
            dni: dniMatch ? dniMatch[1] : null,
        });
    });

    if (!rows.length) {
        throw new Error("Encontré registros con D.N.I. pero ninguno con teléfono reconocible.");
    }

    return rows;
}