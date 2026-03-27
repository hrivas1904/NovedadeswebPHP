function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "60vh";
}

$(document).ready(function () {
    cargarDashboard();
});

$("#btnFiltrar").click(function () {
    cargarDashboard();
});

$("#btnLimpiar").click(function () {
    $("#fechaDesde").val("");
    $("#fechaHasta").val("");
    cargarDashboard();
});

function cargarDashboard() {
    let desde = $("#fechaDesde").val() || null;
    let hasta = $("#fechaHasta").val() || null;
    let tipoEncuesta = null;

    $.ajax({
        url: "/dashboard/completo",
        method: "GET",
        data: {
            desde: desde,
            hasta: hasta,
            tipo: tipoEncuesta,
        },

        success: function (res) {
            dashboardData = res;

            if (res.kpi && res.kpi.length > 0) {
                $("#kpiSatisfaccion").text(res.kpi[0].satisfaccion + "%");
                $("#kpiEncuestas").text(res.kpi[0].total_encuestas);
            }

            renderTablaGuardias(res.guardias);
            renderTablaAreas(res.areas);
            renderTablaUti(res.uti);
            renderTablaInternacion(res.internacion);
        },
    });
}

function renderTablaGuardias(data) {
    if ($.fn.DataTable.isDataTable("#tablaGuardias")) {
        $("#tablaGuardias").DataTable().destroy();
    }

    $("#tablaGuardias").DataTable({
        data: data,
        autoWidth: false,
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        responsive: true,
        searching: false,
        info: false,
        columns: [
            { title: "Tipo", data: "tipoEncuesta" },
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "No Aplica", data: "no_aplica" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 80 ? "green" : data >= 60 ? "orange" : "red";
                    return `<span style="color:${color}; font-weight:bold">${data}%</span>`;
                },
            },
        ],
        pageLength: 5,
    });
}

function renderTablaAreas(data) {
    if ($.fn.DataTable.isDataTable("#tablaAreas")) {
        $("#tablaAreas").DataTable().destroy();
    }

    $("#tablaAreas").DataTable({
        data: data,
        autoWidth: false,
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        responsive: true,
        searching: false,
        info: false,
        columns: [
            { title: "Área", data: "area_grupo" },
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "No Aplica", data: "no_aplica" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 80 ? "green" : data >= 60 ? "orange" : "red";
                    return `<span style="color:${color}; font-weight:bold">${data}%</span>`;
                },
            },
        ],
        pageLength: 5,
    });
}

function renderTablaUti(data) {
    if ($.fn.DataTable.isDataTable("#tablaUti")) {
        $("#tablaUti").DataTable().destroy();
    }

    $("#tablaUti").DataTable({
        data: data,
        autoWidth: false,
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        responsive: true,
        searching: false,
        info: false,
        columns: [
            { title: "Tipo UTI", data: "tipo_uti" },
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "No Aplica", data: "no_aplica" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 80 ? "green" : data >= 60 ? "orange" : "red";
                    return `<span style="color:${color}; font-weight:bold">${data}%</span>`;
                },
            },
        ],
        pageLength: 5,
    });
}

function renderTablaInternacion(data) {
    if ($.fn.DataTable.isDataTable("#tablaInternacion")) {
        $("#tablaInternacion").DataTable().destroy();
    }

    $("#tablaInternacion").DataTable({
        data: data,
        autoWidth: false,
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        responsive: true,
        searching: false,
        info: false,
        columns: [
            { title: "Tipo", data: "tipo" },
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "No Aplica", data: "no_aplica" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 80 ? "green" : data >= 60 ? "orange" : "red";
                    return `<span style="color:${color}; font-weight:bold">${data}%</span>`;
                },
            },
        ],
        pageLength: 5,
    });
}

function exportarDashboardPDF(res) {
    let contenido = [];

    // 🔹 Título
    contenido.push({
        text: "Dashboard de Calidad",
        style: "header",
    });

    contenido.push({
        text: `Fecha: ${new Date().toLocaleDateString()}`,
        margin: [0, 0, 0, 10],
    });

    // 🔹 KPIs
    contenido.push({
        text: `Satisfacción: ${res.kpi[0].satisfaccion}%`,
    });

    contenido.push({
        text: `Total Encuestas: ${res.kpi[0].total_encuestas}`,
        margin: [0, 0, 0, 10],
    });

    // 🔹 Función helper para tablas
    function armarTabla(data, columnas, titulo) {
        let headers = columnas.map((c) => c.title);

        let body = data.map((row) => {
            return columnas.map((col) => {
                let val = row[col.data];

                if (col.data === "porc_positivas") {
                    let color =
                        val >= 80 ? "green" : val >= 60 ? "orange" : "red";

                    return { text: val + "%", color: color, bold: true };
                }

                return val;
            });
        });

        return [
            { text: titulo, style: "subheader" },
            {
                table: {
                    headerRows: 1,
                    widths: Array(headers.length).fill("*"),
                    body: [headers, ...body],
                },
                margin: [0, 5, 0, 15],
            },
        ];
    }

    // 🔹 Columnas (las mismas que usás en DataTable)
    const colsGuardias = [
        { title: "Tipo", data: "tipoEncuesta" },
        { title: "Positivas", data: "positivas" },
        { title: "Negativas", data: "negativas" },
        { title: "No Aplica", data: "no_aplica" },
        { title: "% Positivas", data: "porc_positivas" },
    ];

    const colsAreas = [
        { title: "Área", data: "area_grupo" },
        { title: "Positivas", data: "positivas" },
        { title: "Negativas", data: "negativas" },
        { title: "No Aplica", data: "no_aplica" },
        { title: "% Positivas", data: "porc_positivas" },
    ];

    const colsUti = [
        { title: "Tipo UTI", data: "tipo_uti" },
        { title: "Positivas", data: "positivas" },
        { title: "Negativas", data: "negativas" },
        { title: "No Aplica", data: "no_aplica" },
        { title: "% Positivas", data: "porc_positivas" },
    ];

    const colsInternacion = [
        { title: "Tipo", data: "tipo" },
        { title: "Positivas", data: "positivas" },
        { title: "Negativas", data: "negativas" },
        { title: "No Aplica", data: "no_aplica" },
        { title: "% Positivas", data: "porc_positivas" },
    ];

    // 🔹 Agregar tablas
    contenido.push(...armarTabla(res.guardias, colsGuardias, "Guardias"));
    contenido.push(...armarTabla(res.areas, colsAreas, "Áreas"));
    contenido.push(...armarTabla(res.uti, colsUti, "UTI"));
    contenido.push(
        ...armarTabla(res.internacion, colsInternacion, "Internación"),
    );

    let docDefinition = {
        content: contenido,
        styles: {
            header: { fontSize: 18, bold: true },
            subheader: { fontSize: 14, bold: true, margin: [0, 10, 0, 5] },
        },
    };

    pdfMake.createPdf(docDefinition).download("Dashboard.pdf");
}

function renderGrafico(data) {
    let labels = data.map((x) => x.texto);
    let positivas = data.map((x) => x.positivas);
    let negativas = data.map((x) => x.negativas);

    new Chart(document.getElementById("graficoResultados"), {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                { label: "Positivas", data: positivas },
                { label: "Negativas", data: negativas },
            ],
        },
    });
}
