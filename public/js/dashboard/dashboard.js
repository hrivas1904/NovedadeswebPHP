let chartNovedadesTipo = null;
let chartNovedadesArea = null;
let chartTopEmpleados = null;
let tablaNovedadesTipo = null;
let tablaNovedadesArea = null;
let chartNovedadesMes = null;

$(document).ready(function () {
    cargarColaboradoresActivos();
    cargarColaboradoresBaja();
    cargarHistoricoNovedades();
    cargarNovedadesMesActual();
    cargarNovedadesMasFrecuente();
    cargarNovedadesMenosFrecuente();
    cargarAreaMasNovedades();
    cargarAreaMenosNovedades();
    cargarChartNovedadesPorTipo();
    cargarChartNovedadesPorArea();
    cargarTablaNovedadesTipo();
    cargarTablaNovedadesArea();    
    cargarChartNovedadesMes();
    cargarTopEmpleadosNovedades()
});

function capitalizar(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function obtenerFiltrosFechas() {

    return {
        desde: $('#filtroDesde').val(),
        hasta: $('#filtroHasta').val()
    };
}

function cargarColaboradoresActivos() {
    $.ajax({
        url: "/dashboard/colaboradores-activos",
        type: "GET",
        dataType: "json",

        success: function (data) {
            $("#kpiEmpleadosActivos").text(data.total);
        },

        error: function (xhr, status, error) {
            console.error("Error colaboradores activos:", error);
            console.log(xhr.responseText);

            $("#kpiEmpleadosActivos").text("—");
        },
    });
}

function cargarColaboradoresBaja() {
    $.ajax({
        url: "/dashboard/colaboradores-baja",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/novedades-historicos",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/novedades-mas-frec",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/novedades-menos-frec",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/area-mas-novedades",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/area-menos-novedades",
        type: "GET",
        dataType: "json",

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
    $.ajax({
        url: "/dashboard/novedades-por-tipo",
        type: "GET",
        dataType: "json",

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
                            position: "right", // se ve MUY bien en dashboards
                        },
                        tooltip: {
                            callbacks: {
                                // Tooltip personalizado
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
            console.error("Error gráfico torta novedades:", error);
            console.log(xhr.responseText);
        },
    });
}

function cargarChartNovedadesPorArea() {
    $.ajax({
        url: "/dashboard/novedades-por-area",
        type: "GET",
        dataType: "json",

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

    fetch('/dashboard/novedades-por-mes')
        .then(response => response.json())
        .then(data => {

            const labels = [];
            const values = [];

            let maxCantidad = 0;
            let mesMax = '';

            // Limpieza segura
            data.forEach(item => {

                labels.push(item.mes);
                values.push(item.cantidad);

                if (item.cantidad > maxCantidad) {
                    maxCantidad = item.cantidad;
                    mesMax = item.mes;
                }

            });

            document.getElementById('lblMesMaxNovedades').innerText = mesMax || '—';

            const ctx = document.getElementById('chartNovedadesMes').getContext('2d');

            // ⚠️ Destruir gráfico anterior (CLAVE para evitar bug infinito)
            if (chartNovedadesMes) {
                chartNovedadesMes.destroy();
            }

            chartNovedadesMes = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad de novedades',
                        data: values,
                        tension: 0.3,
                        fill: false,
                        borderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0   // evita decimales raros
                            }
                        }
                    },

                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });

        })
        .catch(error => {
            console.error('Error cargando novedades por mes:', error);
        });
}

function cargarTopEmpleadosNovedades() {

    const filtros = obtenerFiltrosFechas();

    $.ajax({
        url: '/dashboard/top-empleados-novedades',
        type: 'GET',
        dataType: 'json',

        data: {
            desde: filtros.desde,
            hasta: filtros.hasta
        },

        success: function (data) {

            if (!data || data.length === 0) {
                return;
            }

            const labels = [];
            const values = [];

            data.forEach(item => {
                labels.push(item.colaborador);
                values.push(Number(item.cantidad));
            });

            const ctx = document.getElementById('chartTopEmpleados').getContext('2d');

            if (chartTopEmpleados) {
                chartTopEmpleados.destroy();
                chartTopEmpleados = null;
            }

            chartTopEmpleados = new Chart(ctx, {
                type: 'bar',

                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        borderRadius: 6,
                        barThickness: 22
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',

                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },

                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            $('#lblEmpleadoTop').text(labels[0] ?? '—');
        }
    });
}

function cargarTablaNovedadesTipo() {

    if (tablaNovedadesTipo) {
        tablaNovedadesTipo.destroy();
        $('#tblNovedadesTipo tbody').empty();
    }

    tablaNovedadesTipo = $('#tblNovedadesTipo').DataTable({

        ajax: {
            url: '/dashboard/novedades-por-tipo',
            dataSrc: ''
        },

        paging: false,
        searching: false,
        info: false,
        ordering: false,

        columns: [

            {
                data: 'novedad'
            },

            {
                data: 'cantidad',
                className: 'text-end'
            },

            {
                data: null,
                className: 'text-end',
                render: function (data, type, row, meta) {

                    const total = tablaNovedadesTipo
                        .column(1)
                        .data()
                        .reduce((a, b) => a + parseInt(b), 0);

                    const porcentaje = ((row.cantidad / total) * 100).toFixed(1);

                    return porcentaje + ' %';
                }
            }

        ]
    });
}

function cargarTablaNovedadesArea() {

    if (tablaNovedadesArea) {
        tablaNovedadesArea.destroy();
        $('#tblNovedadesArea tbody').empty();
    }

    tablaNovedadesArea = $('#tblNovedadesArea').DataTable({

        ajax: {
            url: '/dashboard/novedades-por-area',
            dataSrc: ''
        },

        paging: false,
        searching: false,
        info: false,
        ordering: false,

        columns: [

            {
                data: 'area'
            },

            {
                data: 'cantidad',
                className: 'text-end'
            },

            {
                data: null,
                className: 'text-end',
                render: function (data, type, row, meta) {

                    const total = tablaNovedadesArea
                        .column(1)
                        .data()
                        .reduce((a, b) => a + parseInt(b), 0);

                    const porcentaje = ((row.cantidad / total) * 100).toFixed(1);

                    return porcentaje + ' %';
                }
            }

        ]
    });
}

$('#btnAplicarFiltros').on('click', function () {

    cargarTopEmpleadosNovedades();
    cargarChartNovedadesMes();

});

$('#btnLimpiarFiltros').on('click', function () {

    $('#filtroDesde').val('');
    $('#filtroHasta').val('');

    cargarTopEmpleadosNovedades();
    cargarChartNovedadesMes();

});
