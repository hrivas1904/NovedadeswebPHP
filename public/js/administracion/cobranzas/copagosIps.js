$(function () {
    const csrf = $('meta[name="csrf-token"]').attr("content");
    const R = window.COPAGOS_IPS_ROUTES;

    function formatMoney(n) {
        return (
            (n < 0 ? "-$" : "$") +
            Math.abs(Math.round(n)).toLocaleString("es-AR")
        );
    }

    const tablaCruce = $("#tablaCruce").DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-AR.json",
        },
        ajax: {
            url: R.cruce,
            dataSrc: function (json) {
                $("#kpisCruce").toggleClass("d-none", json.data.length === 0);
                actualizarKpis(json.data);
                return json.data;
            },
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: "dt-control text-center",
                defaultContent: '<i class="fa-solid fa-chevron-down"></i>',
            },
            { data: "nombreDisplay" },
            { data: "fins", render: (d) => d.join(", ") },
            { data: "telefono", render: (d) => d || "—" },
            { data: "periodos", render: (d) => d.join(", ") },
            {
                data: "totalLiquidado",
                className: "text-end",
                render: formatMoney,
            },
            {
                data: "totalCobrado",
                className: "text-end",
                render: formatMoney,
            },
            {
                data: "diferencia",
                className: "text-end",
                render: (d) =>
                    `<span class="${d > 1000 ? "text-danger" : "text-success"} fw-semibold">${formatMoney(d)}</span>`,
            },
            {
                data: "estado",
                render: (estado, tipo, row) => {
                    const badges = {
                        COBRADO: "success",
                        "COBRO PARCIAL": "warning",
                        "NO COBRADO": "danger",
                    };
                    return `<span class="badge bg-${badges[estado]}">${estado}</span>
                        <div class="form-check form-check-inline ms-2 align-middle">
                            <input class="form-check-input" type="checkbox" data-resuelto="${row.nombreNorm}" ${row.resuelto ? "checked" : ""}>
                            <label class="form-check-label small text-muted">Resuelto</label>
                        </div>`;
                },
            },
            {
                data: "nota",
                render: (nota, tipo, row) =>
                    `<input type="text" class="form-control form-control-sm" data-nota="${row.nombreNorm}" value="${nota ? $("<div>").text(nota).html() : ""}" placeholder="Agregar nota…" />`,
            },
        ],
    });

    function actualizarKpis(pacientes) {
        const totalLiquidado = pacientes.reduce(
            (s, p) => s + p.totalLiquidado,
            0,
        );
        const totalCobrado = pacientes.reduce((s, p) => s + p.totalCobrado, 0);
        const pendientes = pacientes.filter(
            (p) => p.estado !== "COBRADO" && !p.resuelto,
        ).length;
        $("#kpiLiquidado").text(formatMoney(totalLiquidado));
        $("#kpiCobrado").text(formatMoney(totalCobrado));
        $("#kpiDiferencia").text(formatMoney(totalLiquidado - totalCobrado));
        $("#kpiPendientes").text(pendientes);
    }

    $("#tablaCruce tbody").on("click", "td.dt-control", function () {
        const tr = $(this).closest("tr"),
            row = tablaCruce.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass("shown");
        } else {
            row.child(renderDetallePagos(row.data())).show();
            tr.addClass("shown");
        }
    });

    function renderDetallePagos(p) {
        if (!p.pagos.length)
            return '<div class="text-muted small p-2">No se encontró ningún pago de copago en caja con este nombre.</div>';
        let html =
            '<table class="table table-sm mb-0"><thead><tr><th>Fecha</th><th>Nombre en caja</th><th class="text-end">Importe</th></tr></thead><tbody>';
        p.pagos.forEach((pg) => {
            html += `<tr><td>${pg.fecha ?? ""}</td><td>${pg.nombre}</td><td class="text-end">${formatMoney(pg.importe)}</td></tr>`;
        });
        return html + "</tbody></table>";
    }

    $("#tablaCruce").on("blur", "input[data-nota]", function () {
        $.post(R.guardarNota, {
            nombre_norm: $(this).data("nota"),
            nota: $(this).val(),
            _token: csrf,
        });
    });

    $("#tablaCruce").on("change", "input[data-resuelto]", function () {
        $.post(R.marcarResuelto, {
            nombre_norm: $(this).data("resuelto"),
            resuelto: this.checked ? 1 : 0,
            _token: csrf,
        }).then(() => tablaCruce.ajax.reload(null, false));
    });

    function subirArchivo(input, url) {
        input.on("change", async function (e) {
            const file = e.target.files[0];
            if (!file) return;
            try {
                const fd = new FormData();
                fd.append("archivo", file);
                const res = await $.ajax({
                    url,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: { "X-CSRF-TOKEN": csrf },
                });
                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1800,
                    showConfirmButton: false,
                });
                tablaCruce.ajax.reload(null, false);
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        err.responseJSON?.message ||
                        "No se pudo cargar el archivo.",
                });
            }
            input.val("");
        });
    }

    subirArchivo($("#inputFileLiqIps"), R.cargarLiquidacion);
    subirArchivo($("#inputFileComprobantes"), R.cargarCaja);

    $("#inputFilePacientes").on("change", async function (e) {
        const file = e.target.files[0];
        if (!file) return;
        try {
            let res;
            if (/\.pdf$/i.test(file.name)) {
                const filas = await parsePatientListPDF(file);
                res = await $.ajax({
                    url: R.cargarPacientesPdf,
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ nombre_archivo: file.name, filas }),
                    headers: { "X-CSRF-TOKEN": csrf },
                });
            } else {
                const fd = new FormData();
                fd.append("archivo", file);
                res = await $.ajax({
                    url: R.cargarPacientes,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: { "X-CSRF-TOKEN": csrf },
                });
            }
            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1800,
                showConfirmButton: false,
            });
            tablaCruce.ajax.reload(null, false);
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    err.responseJSON?.message ||
                    err.message ||
                    "No se pudo cargar el archivo.",
            });
        }
        $(this).val("");
    });

    async function parsePatientListPDF(file) {
        const buf = await file.arrayBuffer();
        const doc = await window.pdfjsLib.getDocument({ data: buf }).promise;
        const lines = [];
        for (let p = 1; p <= doc.numPages; p++) {
            const page = await doc.getPage(p);
            const content = await page.getTextContent();
            const byY = new Map();
            for (const item of content.items) {
                if (!item.str?.trim()) continue;
                const y = Math.round(item.transform[5]);
                if (!byY.has(y)) byY.set(y, []);
                byY.get(y).push(item);
            }
            for (const y of [...byY.keys()].sort((a, b) => b - a)) {
                const items = byY
                    .get(y)
                    .sort((a, b) => a.transform[4] - b.transform[4]);
                let text = "",
                    lastEndX = null;
                for (const it of items) {
                    if (lastEndX !== null && it.transform[4] - lastEndX > 4)
                        text += " ";
                    text += it.str;
                    lastEndX = it.transform[4] + (it.width || 0);
                }
                if (text.trim()) lines.push(text.trim());
            }
        }
        if (!lines.length)
            throw new Error(
                "No pude extraer texto del PDF (¿es un PDF escaneado como imagen?).",
            );

        const dniRe = /D\.?N\.?I\.?\s*[:\-]?\s*([0-9]{6,10})/i;
        const records = [];
        let current = null;
        for (const line of lines) {
            if (dniRe.test(line)) {
                if (current) records.push(current);
                current = { lines: [line] };
            } else if (current) current.lines.push(line);
        }
        if (current) records.push(current);
        if (!records.length)
            throw new Error(
                "No encontré ninguna línea con 'D.N.I.' en el PDF.",
            );

        const rows = [];
        for (const rec of records) {
            const nameLine = rec.lines[0];
            const dniMatch = nameLine.match(dniRe);
            const nameIdx = nameLine.search(/D\.?N\.?I\./i);
            const nombre = (
                nameIdx > -1 ? nameLine.slice(0, nameIdx) : nameLine
            ).trim();
            if (!nombre) continue;
            let combined = rec.lines.join(" ");
            if (dniMatch) combined = combined.replace(dniMatch[0], " ");
            const phoneMatches = combined.match(/\d{6,10}/g);
            const telefono = phoneMatches?.length
                ? phoneMatches[phoneMatches.length - 1]
                : null;
            if (!telefono) continue;
            rows.push({ nombre, telefono, dni: dniMatch ? dniMatch[1] : null });
        }
        if (!rows.length)
            throw new Error(
                "Encontré registros con D.N.I. pero ninguno con teléfono reconocible.",
            );
        return rows;
    }
});
