let chartNovedadesTipo = null;
let chartNovedadesArea = null;
let chartTopEmpleados = null;
let tablaNovedadesTipo = null;
let tablaNovedadesArea = null;
let chartNovedadesMes = null;

let filtrosDashboard = {
    desde: null,
    hasta: null,
};

document
    .getElementById("btnExportarPDF")
    .addEventListener("click", function () {
        const elemento = document.querySelector(".dashboard-container");

        Swal.fire({
            title: "Generando reporte...",
            html: "Por favor espere unos segundos",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        setTimeout(() => {
            document.querySelectorAll('input[type="date"]').forEach((input) => {
                input.setAttribute("value", input.value);
            });

            const opt = {
                margin: 0.5,
                filename: "dashboard-reporte.pdf",
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 3 },
                jsPDF: { unit: "in", format: "a4", orientation: "landscape" },
            };

            html2pdf()
                .set(opt)
                .from(elemento)
                .save()
                .then(() => {
                    Swal.close();

                    Swal.fire({
                        icon: "success",
                        title: "Reporte generado",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                });
        }, 300); // pequeño delay para que renderice el spinner
    });

$("#btnImprimirDashboard").on("click", function () {
    window.print();
});

function capitalizar(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function obtenerFiltrosFechas() {
    const desde = document.getElementById("filtroDesde").value;
    const hasta = document.getElementById("filtroHasta").value;

    console.log("Desde:", desde, "Hasta:", hasta);

    return {
        desde: desde,
        hasta: hasta,
    };
}

function cargarHistoricoColab() {
    $.ajax({
        url: "/dashboard/historico-colaboradores",
        type: "GET",
        dataType: "json",

        success: function (data) {
            $("#kpiEmpleadosHistoricos").text(data.TOTAL);
        },

        error: function (xhr) {
            console.error("Error colaboradores:", xhr.responseText);
            $("#kpiEmpleadosHistoricos").text("—");
        },
    });
}

function cargarIndiceRotacional() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/tasa-rotacional",
        type: "GET",
        dataType: "json",

        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiTasaRotacional").text(data.tasaRotacional);
        },

        error: function (xhr) {
            console.error("Error colaboradores activos:", xhr.responseText);
            $("#kpiTasaRotacional").text("—");
        },
    });
}

function cargarColaboradoresActivos() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/colaboradores-activos",
        type: "GET",
        dataType: "json",

        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiEmpleadosActivos").text(data.total);
        },

        error: function (xhr) {
            console.error("Error colaboradores activos:", xhr.responseText);
            $("#kpiEmpleadosActivos").text("—");
        },
    });
}

function cargarColaboradoresBaja() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/colaboradores-baja",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiEmpleadosBaja").text(data.total);
        },

        error: function (xhr, status, error) {
            console.error("Error colaboradores de baja:", error);
            console.log(xhr.responseText);

            $("#kpiEmpleadosBaja").text("—");
        },
    });
}

function cargarHistoricoNovedades() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-historicos",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiTotalNovedades").text(data.total);
        },

        error: function (xhr, status, error) {
            console.error("Error historial de novedades", error);
            console.log(xhr.responseText);

            $("#kpiTotalNovedades").text("—");
        },
    });
}

function cargarNovedadesMesActual() {
    $.ajax({
        url: "/dashboard/novedades-mes-actual",
        type: "GET",
        dataType: "json",

        success: function (data) {
            $("#kpiNovedadesMes").text(data.total);
        },

        error: function (xhr, status, error) {
            console.error("Error historial de novedades", error);
            console.log(xhr.responseText);

            $("#kpiNovedadesMes").text("—");
        },
    });
}

function cargarNovedadesMasFrecuente() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-mas-frec",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiNovedadMasFrecuente").text(data.novedad);
            $("#kpiNovedadMasFrecuenteCantidad").text(data.cantidad);
        },

        error: function (xhr, status, error) {
            console.error("Error novedad mas frecuente", error);
            console.log(xhr.responseText);

            $("#kpiNovedadMasFrecuente").text("—");
            $("#kpiNovedadMasFrecuenteCantidad").text("—");
        },
    });
}

function cargarNovedadesMenosFrecuente() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-menos-frec",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiNovedadMenosFrecuente").text(data.novedad);
            $("#kpiNovedadMenosFrecuenteCantidad").text(data.cantidad);
        },

        error: function (xhr, status, error) {
            console.error("Error novedad mas frecuente", error);
            console.log(xhr.responseText);

            $("#kpiNovedadMenosFrecuente").text("—");
            $("#kpiNovedadMenosFrecuenteCantidad").text("—");
        },
    });
}

function cargarAreaMasNovedades() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/area-mas-novedades",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiAreaMasNovedades").text(data.area);
            $("#kpiAreaMasNovedadesDetalle").text(data.cantidad);
        },

        error: function (xhr, status, error) {
            console.error("Error novedad mas frecuente", error);
            console.log(xhr.responseText);

            $("#kpiAreaMasNovedades").text("—");
            $("#kpiAreaMasNovedadesDetalle").text("—");
        },
    });
}

function cargarAreaMenosNovedades() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/area-menos-novedades",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            $("#kpiAreaMenosNovedades").text(data.area);
            $("#kpiAreaMenosNovedadesDetalle").text(data.cantidad);
        },

        error: function (xhr, status, error) {
            console.error("Error novedad mas frecuente", error);
            console.log(xhr.responseText);

            $("#kpiAreaMenosNovedades").text("—");
            $("#kpiAreaMenosNovedadesDetalle").text("—");
        },
    });
}

function cargarChartNovedadesPorTipo() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-por-tipo",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            // data = [{ novedad: "...", cantidad: 12 }, ...]

            const labels = data.map((x) => x.novedad);
            const values = data.map((x) => parseInt(x.cantidad));

            const ctx = document.getElementById("chartNovedadesTipo");

            // Destruir chart previo
            if (chartNovedadesTipo) {
                chartNovedadesTipo.destroy();
            }

            chartNovedadesTipo = new Chart(ctx, {
                type: "pie", // 👈 CAMBIO CLAVE

                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: values,
                            // colores automáticos (Chart.js genera paleta)
                        },
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: "right",
                        },
                        datalabels: {
                            color: "#fff",
                            font: {
                                weight: "bold",
                                size: 12,
                            },
                            formatter: (value, ctx) => {
                                const data = ctx.chart.data.datasets[0].data;
                                const total = data.reduce((a, b) => a + b, 0);

                                const porcentaje = (
                                    (value / total) *
                                    100
                                ).toFixed(1);

                                return porcentaje + "%";
                            },
                        },
                    },
                    datalabels: {
                        color: "#000",
                        anchor: "end",
                        align: "end",
                        offset: 10,
                        formatter: (value, ctx) => {
                            const data = ctx.chart.data.datasets[0].data;
                            const total = data.reduce((a, b) => a + b, 0);

                            return ((value / total) * 100).toFixed(1) + "%";
                        },
                    },
                },
            });
        },

        error: function (xhr, status, error) {
            console.error("Error gráfico torta novedades:", error);
            console.log(xhr.responseText);
        },
    });
}

function cargarChartNovedadesPorArea() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-por-area",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            // data = [{ area: "...", cantidad: 20 }]

            const labels = data.map((x) => x.area);
            const values = data.map((x) => parseInt(x.cantidad));

            const ctx = document.getElementById("chartNovedadesAreas");

            if (chartNovedadesArea) {
                chartNovedadesArea.destroy();
            }

            chartNovedadesArea = new Chart(ctx, {
                type: "pie",

                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: values,
                        },
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: "right",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || "";
                                    const value = context.raw || 0;

                                    const total = context.dataset.data.reduce(
                                        (a, b) => a + b,
                                        0,
                                    );

                                    const percent = (
                                        (value / total) *
                                        100
                                    ).toFixed(1);

                                    return `${label}: ${value} (${percent}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        error: function (xhr, status, error) {
            console.error("Error gráfico novedades por área:", error);
            console.log(xhr.responseText);
        },
    });
}

function cargarChartNovedadesMes() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/novedades-por-mes",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            const labels = [];
            const values = [];

            let maxCantidad = 0;
            let mesMax = "";

            data.forEach((item) => {
                labels.push(capitalizar(item.mes + " " + item.anio));
                values.push(item.cantidad);

                if (item.cantidad > maxCantidad) {
                    maxCantidad = item.cantidad;
                    mesMax = item.mes;
                }
            });

            $("#lblMesMaxNovedades").text(mesMax || "—");

            const ctx = document
                .getElementById("chartNovedadesMes")
                .getContext("2d");

            // 🔥 Destruir instancia anterior
            if (chartNovedadesMes) {
                chartNovedadesMes.destroy();
            }

            chartNovedadesMes = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Cantidad de novedades",
                            data: values,
                            tension: 0.3,
                            fill: false,
                            borderWidth: 2,
                            pointRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },

                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                    },
                },
            });
        },

        error: function (xhr) {
            console.error(
                "Error cargando novedades por mes:",
                xhr.responseText,
            );
        },
    });
}

function cargarTopEmpleadosNovedades() {
    const filtros = obtenerFiltrosFechas();
    console.log("Llamando AJAX con:", filtros);
    $.ajax({
        url: "/dashboard/top-empleados-novedades",
        type: "GET",
        dataType: "json",
        data: {
            desde: filtros.desde,
            hasta: filtros.hasta,
        },

        success: function (data) {
            if (!data || data.length === 0) {
                return;
            }

            const labels = [];
            const values = [];

            data.forEach((item) => {
                labels.push(item.colaborador);
                values.push(Number(item.cantidad));
            });

            const ctx = document
                .getElementById("chartTopEmpleados")
                .getContext("2d");

            if (chartTopEmpleados) {
                chartTopEmpleados.destroy();
                chartTopEmpleados = null;
            }

            chartTopEmpleados = new Chart(ctx, {
                type: "bar",

                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: values,
                            borderRadius: 6,
                            barThickness: 22,
                        },
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: "y",

                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },

                    plugins: {
                        legend: { display: false },
                    },
                },
            });

            $("#lblEmpleadoTop").text(labels[0] ?? "—");
        },
    });
}

function cargarTablaNovedadesTipo() {
    if (tablaNovedadesTipo) {
        tablaNovedadesTipo.destroy();
        $("#tblNovedadesTipo tbody").empty();
    }

    tablaNovedadesTipo = $("#tblNovedadesTipo").DataTable({
        ajax: {
            url: "/dashboard/novedades-por-tipo",
            type: "GET",

            data: function (d) {
                // 🔥 inyecta filtros actuales
                return filtrosDashboard;
            },

            dataSrc: "",
        },

        paging: false,
        searching: false,
        info: false,
        ordering: false,

        columns: [
            { data: "novedad" },

            {
                data: "cantidad",
                className: "text-end",
            },

            {
                data: null,
                className: "text-end",
                render: function (data, type, row) {
                    const total = tablaNovedadesTipo
                        .column(1)
                        .data()
                        .reduce((a, b) => a + parseInt(b), 0);

                    const porcentaje = ((row.cantidad / total) * 100).toFixed(
                        1,
                    );

                    return porcentaje + " %";
                },
            },
        ],
    });
}

function cargarTablaNovedadesArea() {
    if (tablaNovedadesArea) {
        tablaNovedadesArea.destroy();
        $("#tblNovedadesArea tbody").empty();
    }

    tablaNovedadesArea = $("#tblNovedadesArea").DataTable({
        ajax: {
            url: "/dashboard/novedades-por-area",
            dataSrc: "",
            data: function (d) {
                return filtrosDashboard;
            },
        },

        paging: false,
        searching: false,
        info: false,
        ordering: false,

        columns: [
            {
                data: "area",
            },

            {
                data: "cantidad",
                className: "text-end",
            },

            {
                data: null,
                className: "text-end",
                render: function (data, type, row, meta) {
                    const total = tablaNovedadesArea
                        .column(1)
                        .data()
                        .reduce((a, b) => a + parseInt(b), 0);

                    const porcentaje = ((row.cantidad / total) * 100).toFixed(
                        1,
                    );

                    return porcentaje + " %";
                },
            },
        ],
    });
}

function recargarDashboard() {
    obtenerFiltrosFechas();
    cargarHistoricoColab();
    cargarColaboradoresActivos();
    cargarColaboradoresBaja();
    cargarHistoricoNovedades();
    cargarNovedadesMesActual();
    cargarNovedadesMasFrecuente();
    cargarNovedadesMenosFrecuente();
    cargarAreaMasNovedades();
    cargarAreaMenosNovedades();
    cargarIndiceRotacional();
    cargarChartNovedadesPorTipo();
    cargarChartNovedadesPorArea();
    cargarChartNovedadesMes();
    cargarTopEmpleadosNovedades();
    cargarTablaNovedadesTipo();
    cargarTablaNovedadesArea();
}

$("#btnAplicarFiltros").on("click", function () {
    filtrosDashboard.desde = $("#filtroDesde").val() || null;
    filtrosDashboard.hasta = $("#filtroHasta").val() || null;

    console.log("Filtros activos:", filtrosDashboard);

    recargarDashboard();
});

$("#btnLimpiarFiltros").on("click", function () {
    $("#filtroDesde").val("");
    $("#filtroHasta").val("");

    recargarDashboard();
});

$(document).ready(function () {
    obtenerFiltrosFechas();
    cargarHistoricoColab();
    cargarColaboradoresActivos();
    cargarColaboradoresBaja();
    cargarHistoricoNovedades();
    cargarNovedadesMesActual();
    cargarNovedadesMasFrecuente();
    cargarNovedadesMenosFrecuente();
    cargarAreaMasNovedades();
    cargarAreaMenosNovedades();
    cargarIndiceRotacional();
    cargarChartNovedadesPorTipo();
    cargarChartNovedadesPorArea();
    cargarTablaNovedadesTipo();
    cargarTablaNovedadesArea();
    cargarChartNovedadesMes();
    cargarTopEmpleadosNovedades();
});
