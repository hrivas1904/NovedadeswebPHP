$(function () {
    const csrf = $('meta[name="csrf-token"]').attr("content");
    const R = window.COPAGOS_IPS_ROUTES;

    let filtroCruce = "TODOS";

    // =========================================================
    // HELPERS
    // =========================================================

    function formatMoney(n) {
        n = Number(n) || 0;

        return (
            (n < 0 ? "-$" : "$") +
            Math.abs(Math.round(n)).toLocaleString("es-AR")
        );
    }

    function escapeHtml(value) {
        return $("<div>")
            .text(value ?? "")
            .html();
    }

    // =========================================================
    // FILTRO PERSONALIZADO DATATABLE
    // =========================================================

    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex, rowData) {
            if (settings.nTable.id !== "tablaCruce") {
                return true;
            }

            const paciente = rowData;

            if (!paciente) {
                return true;
            }

            switch (filtroCruce) {
                case "PENDIENTES":
                    return paciente.estado !== "COBRADO" && !paciente.resuelto;

                case "RESUELTOS":
                    return Boolean(paciente.resuelto);

                case "NO COBRADO":
                    return paciente.estado === "NO COBRADO";

                case "COBRO PARCIAL":
                    return paciente.estado === "COBRO PARCIAL";

                case "COBRADO":
                    return paciente.estado === "COBRADO";

                case "TODOS":
                default:
                    return true;
            }
        },
    );

    // =========================================================
    // DATATABLE
    // =========================================================

    const tablaCruce = $("#tablaCruce").DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-AR.json",
        },

        /*
         * No mostramos el buscador propio de DataTables,
         * pero mantenemos activa la capacidad de filtrado
         * para que funcione $.fn.dataTable.ext.search.
         */
        layout: {
            topStart: "pageLength",
            topEnd: null,
            bottomStart: "info",
            bottomEnd: "paging",
        },

        ajax: {
            url: R.cruce,

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

            {
                data: "nombreDisplay",
                render: function (data, type) {
                    if (type !== "display") {
                        return data;
                    }

                    return escapeHtml(data);
                },
            },

            {
                data: "fins",
                render: function (data, type) {
                    const valor = Array.isArray(data) ? data.join(", ") : "";

                    if (type !== "display") {
                        return valor;
                    }

                    return escapeHtml(valor);
                },
            },

            {
                data: "telefono",
                render: function (data, type) {
                    const valor = data || "—";

                    if (type !== "display") {
                        return valor;
                    }

                    return escapeHtml(valor);
                },
            },

            {
                data: "periodos",
                render: function (data, type) {
                    const valor = Array.isArray(data) ? data.join(", ") : "";

                    if (type !== "display") {
                        return valor;
                    }

                    return escapeHtml(valor);
                },
            },

            {
                data: "totalLiquidado",
                className: "text-end",

                render: function (data, type) {
                    const valor = Number(data) || 0;

                    if (type !== "display") {
                        return valor;
                    }

                    return formatMoney(valor);
                },
            },

            {
                data: "totalCobrado",
                className: "text-end",

                render: function (data, type) {
                    const valor = Number(data) || 0;

                    if (type !== "display") {
                        return valor;
                    }

                    return formatMoney(valor);
                },
            },

            {
                data: "diferencia",
                className: "text-end",

                render: function (data, type) {
                    const valor = Number(data) || 0;

                    if (type !== "display") {
                        return valor;
                    }

                    const clase = valor > 1000 ? "text-danger" : "text-success";

                    return `
                        <span class="${clase} fw-semibold">
                            ${formatMoney(valor)}
                        </span>
                    `;
                },
            },

            {
                data: "estado",

                render: function (estado, tipo, row) {
                    if (tipo !== "display") {
                        return estado;
                    }

                    const badges = {
                        COBRADO: "success",
                        "COBRO PARCIAL": "warning",
                        "NO COBRADO": "danger",
                    };

                    const badge = badges[estado] ?? "secondary";

                    return `
                        <span class="badge bg-${badge}">
                            ${escapeHtml(estado)}
                        </span>

                        <div class="form-check form-check-inline ms-2 align-middle">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                data-resuelto="${escapeHtml(row.nombreNorm)}"
                                ${row.resuelto ? "checked" : ""}
                            >

                            <label class="form-check-label small text-muted">
                                Resuelto
                            </label>
                        </div>
                    `;
                },
            },

            {
                data: "nota",

                render: function (nota, tipo, row) {
                    if (tipo !== "display") {
                        return nota ?? "";
                    }

                    return `
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            data-nota="${escapeHtml(row.nombreNorm)}"
                            value="${escapeHtml(nota ?? "")}"
                            placeholder="Agregar nota…"
                        >
                    `;
                },
            },
        ],

        drawCallback: function () {
            actualizarContadorCruce();
        },
    });

    // =========================================================
    // KPIs
    // =========================================================

    function actualizarKpis(pacientes) {
        const totalLiquidado = pacientes.reduce(
            (s, p) => s + Number(p.totalLiquidado || 0),
            0,
        );

        const totalCobrado = pacientes.reduce(
            (s, p) => s + Number(p.totalCobrado || 0),
            0,
        );

        const pendientes = pacientes.filter(
            (p) => p.estado !== "COBRADO" && !p.resuelto,
        ).length;

        $("#kpiLiquidado").text(formatMoney(totalLiquidado));

        $("#kpiCobrado").text(formatMoney(totalCobrado));

        $("#kpiDiferencia").text(formatMoney(totalLiquidado - totalCobrado));

        $("#kpiPendientes").text(pendientes);
    }

    // =========================================================
    // CONTADOR DEL FILTRO
    // =========================================================

    function actualizarContadorCruce() {
        const total = tablaCruce.rows().count();

        const visibles = tablaCruce
            .rows({
                search: "applied",
            })
            .count();

        $("#contadorCruce").text(`${visibles} de ${total}`);
    }

    // =========================================================
    // BOTONES FILTROS
    // =========================================================

    $(".btnFiltroCopago").on("click", function () {
        filtroCruce = $(this).data("filtro");

        $(".btnFiltroCopago")
            .removeClass("btn-dark active")
            .addClass("btn-outline-secondary");

        $(this)
            .removeClass("btn-outline-secondary")
            .addClass("btn-dark active");

        tablaCruce.draw();
    });

    // =========================================================
    // DETALLE MOVIMIENTOS DE CAJA
    // =========================================================

    $("#tablaCruce tbody").on("click", "td.dt-control", function () {
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
            return `
                <div class="text-muted small p-2">
                    No se encontró ningún pago de copago
                    en caja con este nombre.
                </div>
            `;
        }

        let html = `
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre en caja</th>
                        <th class="text-end">
                            Importe
                        </th>
                    </tr>
                </thead>

                <tbody>
        `;

        paciente.pagos.forEach((pg) => {
            html += `
                <tr>
                    <td>
                        ${escapeHtml(pg.fecha ?? "")}
                    </td>

                    <td>
                        ${escapeHtml(pg.nombre ?? "")}
                    </td>

                    <td class="text-end">
                        ${formatMoney(pg.importe)}
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        return html;
    }

    // =========================================================
    // NOTAS
    // =========================================================

    $("#tablaCruce").on("blur", "input[data-nota]", function () {
        const input = $(this);

        $.ajax({
            url: R.guardarNota,
            method: "POST",

            data: {
                nombre_norm: input.data("nota"),

                nota: input.val(),

                _token: csrf,
            },

            error: function (err) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        err.responseJSON?.message ||
                        "No se pudo guardar la nota.",
                });
            },
        });
    });

    // =========================================================
    // RESUELTO
    // =========================================================

    $("#tablaCruce").on("change", "input[data-resuelto]", function () {
        const checkbox = $(this);

        $.ajax({
            url: R.marcarResuelto,
            method: "POST",

            data: {
                nombre_norm: checkbox.data("resuelto"),

                resuelto: this.checked ? 1 : 0,

                _token: csrf,
            },

            success: function () {
                tablaCruce.ajax.reload(null, false);
            },

            error: function (err) {
                /*
                 * Si falla el guardado,
                 * restauramos visualmente
                 * el checkbox.
                 */
                checkbox.prop("checked", !checkbox.prop("checked"));

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        err.responseJSON?.message ||
                        "No se pudo actualizar el estado.",
                });
            },
        });
    });

    // =========================================================
    // SUBIDA DE LIQUIDACIÓN / CAJA
    // =========================================================

    function subirArchivo(input, url) {
        input.on("change", async function (e) {
            const file = e.target.files[0];

            if (!file) {
                return;
            }

            const esCaja = url === R.cargarCaja;

            try {
                const fd = new FormData();
                fd.append("archivo", file);

                if (esCaja) {
                    Swal.fire({
                        title: "Procesando comprobantes de caja",
                        html: `
                        <div class="mb-2">
                            El archivo puede tardar unos segundos en procesarse.
                        </div>
                        <small class="text-muted">
                            Por favor, no cierres ni recargues la página.
                        </small>
                    `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                }

                const res = await $.ajax({
                    url: url,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                    },
                });

                Swal.close();

                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1800,
                    showConfirmButton: false,
                });

                tablaCruce.ajax.reload(null, false);
            } catch (err) {
                Swal.close();

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

    // =========================================================
    // LISTADO DE PACIENTES
    // =========================================================

    $("#inputFilePacientes").on("change", async function (e) {
        const file = e.target.files[0];

        if (!file) {
            return;
        }

        try {
            let res;

            if (/\.pdf$/i.test(file.name)) {
                const filas = await parsePatientListPDF(file);

                res = await $.ajax({
                    url: R.cargarPacientesPdf,

                    method: "POST",

                    contentType: "application/json",

                    data: JSON.stringify({
                        nombre_archivo: file.name,

                        filas: filas,
                    }),

                    headers: {
                        "X-CSRF-TOKEN": csrf,
                    },
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

                    headers: {
                        "X-CSRF-TOKEN": csrf,
                    },
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

    // =========================================================
    // PARSEO PDF PACIENTES
    // =========================================================

    async function parsePatientListPDF(file) {
        const buf = await file.arrayBuffer();

        const doc = await window.pdfjsLib.getDocument({
            data: buf,
        }).promise;

        const lines = [];

        for (let p = 1; p <= doc.numPages; p++) {
            const page = await doc.getPage(p);

            const content = await page.getTextContent();

            const byY = new Map();

            for (const item of content.items) {
                if (!item.str?.trim()) {
                    continue;
                }

                const y = Math.round(item.transform[5]);

                if (!byY.has(y)) {
                    byY.set(y, []);
                }

                byY.get(y).push(item);
            }

            for (const y of [...byY.keys()].sort((a, b) => b - a)) {
                const items = byY
                    .get(y)
                    .sort((a, b) => a.transform[4] - b.transform[4]);

                let text = "";
                let lastEndX = null;

                for (const it of items) {
                    if (lastEndX !== null && it.transform[4] - lastEndX > 4) {
                        text += " ";
                    }

                    text += it.str;

                    lastEndX = it.transform[4] + (it.width || 0);
                }

                if (text.trim()) {
                    lines.push(text.trim());
                }
            }
        }

        if (!lines.length) {
            throw new Error(
                "No pude extraer texto del PDF (¿es un PDF escaneado como imagen?).",
            );
        }

        const dniRe = /D\.?N\.?I\.?\s*[:\-]?\s*([0-9]{6,10})/i;

        const records = [];

        let current = null;

        for (const line of lines) {
            if (dniRe.test(line)) {
                if (current) {
                    records.push(current);
                }

                current = {
                    lines: [line],
                };
            } else if (current) {
                current.lines.push(line);
            }
        }

        if (current) {
            records.push(current);
        }

        if (!records.length) {
            throw new Error(
                "No encontré ninguna línea con 'D.N.I.' en el PDF.",
            );
        }

        const rows = [];

        for (const rec of records) {
            const nameLine = rec.lines[0];

            const dniMatch = nameLine.match(dniRe);

            const nameIdx = nameLine.search(/D\.?N\.?I\./i);

            const nombre = (
                nameIdx > -1 ? nameLine.slice(0, nameIdx) : nameLine
            ).trim();

            if (!nombre) {
                continue;
            }

            let combined = rec.lines.join(" ");

            if (dniMatch) {
                combined = combined.replace(dniMatch[0], " ");
            }

            const phoneMatches = combined.match(/\d{6,10}/g);

            const telefono = phoneMatches?.length
                ? phoneMatches[phoneMatches.length - 1]
                : null;

            if (!telefono) {
                continue;
            }

            rows.push({
                nombre: nombre,

                telefono: telefono,

                dni: dniMatch ? dniMatch[1] : null,
            });
        }

        if (!rows.length) {
            throw new Error(
                "Encontré registros con D.N.I. pero ninguno con teléfono reconocible.",
            );
        }

        return rows;
    }

    // =========================================================
    // SNAPSHOT
    // =========================================================

    $("#btnGuardarSnapshot").on("click", async function () {
        const mes = $("#mesSnapshot").val();

        if (!mes) {
            Swal.fire({
                icon: "warning",
                title: "Seleccioná un mes",
            });

            return;
        }

        const btn = $(this);

        try {
            btn.prop("disabled", true);

            const res = await $.ajax({
                url: R.guardarSnapshot,

                method: "POST",

                data: {
                    mes: mes,

                    _token: csrf,
                },
            });

            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1800,
                showConfirmButton: false,
            });
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",

                text:
                    err.responseJSON?.message ||
                    "No se pudo guardar el snapshot.",
            });
        } finally {
            btn.prop("disabled", false);
        }
    });
});
