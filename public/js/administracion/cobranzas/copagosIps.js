$("#tabla-liquidacion").DataTable({
    ajax: {
        url: ADMINISTRACION_ROUTES.copagosIps.listarLiquidacion,
        dataSrc: "data",
    },
    columns: [
        { data: "nombre" },
        { data: "fin" },
        { data: "periodo" },
        {
            data: "practicas",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
        {
            data: "internacion",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
        {
            data: "total",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
    ],
});

$("#liq-input").on("change", function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append("archivo", file);
    $.ajax({
        url: ADMINISTRACION_ROUTES.copagosIps.cargarLiquidacion,
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    })
        .done((res) => {
            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1800,
                showConfirmButton: false,
            });
            $("#tabla-liquidacion").DataTable().ajax.reload();
        })
        .fail((xhr) => {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.message ||
                    "No se pudo cargar el archivo.",
            });
        });
});

$("#tabla-caja").DataTable({
    ajax: { url: ADMINISTRACION_ROUTES.copagosIps.listarCaja, dataSrc: "data" },
    columns: [
        { data: "fecha" },
        { data: "nombre" },
        { data: "osplan" },
        {
            data: "importe",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
    ],
});

$("#caja-input").on("change", function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append("archivo", file);
    $.ajax({
        url: ADMINISTRACION_ROUTES.copagosIps.cargarCaja,
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    })
        .done((res) => {
            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1800,
                showConfirmButton: false,
            });
            $("#tabla-caja").DataTable().ajax.reload();
        })
        .fail((xhr) => {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.message ||
                    "No se pudo cargar el archivo.",
            });
        });
});

let tablaCruce = $("#tabla-cruce").DataTable({
    ajax: {
        url: ADMINISTRACION_ROUTES.copagosIps.cruce,
        dataSrc: function (json) {
            actualizarKpis(json.data);
            return json.data;
        },
    },
    columns: [
        {
            data: null,
            orderable: false,
            className: "dt-control",
            defaultContent: "",
        },
        { data: "nombreDisplay" },
        { data: "fins", render: (d) => d.join(", ") },
        { data: "telefono", render: (d) => d || "—" },
        { data: "periodos", render: (d) => d.join(", ") },
        {
            data: "totalLiquidado",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
        {
            data: "totalCobrado",
            render: $.fn.dataTable.render.number(".", ",", 2, "$"),
        },
        {
            data: "diferencia",
            render: (d) =>
                `<span class="${d > 1000 ? "text-danger" : "text-success"} fw-semibold">${$.fn.dataTable.render.number(".", ",", 2, "$").display(d)}</span>`,
        },
        {
            data: "estado",
            render: (estado, tipo, row) => {
                const badges = {
                    COBRADO: "success",
                    "COBRO PARCIAL": "warning",
                    "NO COBRADO": "danger",
                };
                return (
                    `<span class="badge bg-${badges[estado]}">${estado}</span>` +
                    (row.resuelto ? ' <span class="text-success">✓</span>' : "")
                );
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
    const totalLiquidado = pacientes.reduce((s, p) => s + p.totalLiquidado, 0);
    const totalCobrado = pacientes.reduce((s, p) => s + p.totalCobrado, 0);
    const pendientes = pacientes.filter(
        (p) => p.estado !== "COBRADO" && !p.resuelto,
    ).length;
    $("#kpi-liquidado").text(formatMoney(totalLiquidado));
    $("#kpi-cobrado").text(formatMoney(totalCobrado));
    $("#kpi-diferencia").text(formatMoney(totalLiquidado - totalCobrado));
    $("#kpi-pendientes").text(pendientes);
}
function formatMoney(n) {
    return (
        (n < 0 ? "-$" : "$") + Math.abs(Math.round(n)).toLocaleString("es-AR")
    );
}

$("#tabla-cruce tbody").on("click", "td.dt-control", function () {
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

$("#tabla-cruce").on("blur", "input[data-nota]", function () {
    $.post(ADMINISTRACION_ROUTES.copagosIps.guardarNota, {
        nombre_norm: $(this).data("nota"),
        nota: $(this).val(),
        _token: $('meta[name="csrf-token"]').attr("content"),
    });
});
