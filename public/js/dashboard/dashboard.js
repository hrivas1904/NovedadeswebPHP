let chartNovedadesTipo = null;
let chartNovedadesArea = null;
let chartTopEmpleados = null;
let tablaNovedadesTipo = null;
let tablaNovedadesArea = null;


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
});

function capitalizar(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
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

function cargarChartNovedadesPorMes() {
    $.ajax({
        url: '/dashboard/novedades-por-mes',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            // --- VALIDACIÓN DE DATOS ---
            // Si el error "data.map is not a function" persiste, 
            // revisa si los datos vienen dentro de una propiedad (ej: response.data)
            let data = Array.isArray(response) ? response : (response.data || []);

            if (data.length === 0) {
                console.warn("No se recibieron datos para el gráfico o el formato es incorrecto.");
                return;
            }

            // Procesar etiquetas y valores
            const labels = data.map(x => typeof capitalizar === 'function' ? capitalizar(x.mes) : x.mes);
            const values = data.map(x => Number(x.cantidad));

            const canvas = document.getElementById('chartNovedadesMes');
            if (!canvas) {
                console.error("No se encontró el elemento canvas con ID 'chartNovedadesMes'");
                return;
            }

            // --- LÓGICA DEL GRÁFICO ---
            
            // SI NO EXISTE → CREAR
            if (!chartNovedadesMes) {
                chartNovedadesMes = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Novedades',
                            data: values,
                            tension: 0.3,
                            fill: false,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2,
                            borderColor: '#3b82f6', // Color azul opcional
                            backgroundColor: '#3b82f6'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // ¡Recuerda ponerle altura fija al padre en CSS!
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            } 
            // SI YA EXISTE → ACTUALIZAR
            else {
                chartNovedadesMes.data.labels = labels;
                chartNovedadesMes.data.datasets[0].data = values;
                
                // Forzar actualización sin animaciones raras que afecten el layout
                chartNovedadesMes.update('none'); 
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener datos del dashboard:", error);
        }
    });
}

function cargarTopEmpleadosNovedades() {

    console.count('TOP EMPLEADOS');

    $.ajax({
        url: '/dashboard/top-empleados-novedades',
        type: 'GET',
        dataType: 'json',

        success: function (data) {

            if (!data || data.length === 0) {
                return;
            }

            const labels = data.map(x => x.colaborador);
            const values = data.map(x => parseInt(x.cantidad));

            const canvas = document.getElementById('chartTopEmpleados');

            // ✅ destrucción correcta
            if (chartTopEmpleados) {
                chartTopEmpleados.destroy();
            }

            // ✅ guardar nueva instancia
            chartTopEmpleados = new Chart(canvas, {

                type: 'bar',

                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Novedades',
                        data: values
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',

                    plugins: {
                        legend: { display: false }
                    },

                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });

            // KPI textual
            $('#lblEmpleadoTop').text(labels[0]);

        },

        error: function (xhr, status, error) {
            console.error('Error top empleados novedades:', error);
            console.log(xhr.responseText);
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
