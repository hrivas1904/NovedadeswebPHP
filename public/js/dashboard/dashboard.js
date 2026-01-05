let chartNovedadesTipo = null;
let chartNovedadesMes = null;
let chartTopEmpleados = null;
let chartNovedadesAreas = null;

$(document).ready(function () {
    inicializarEventosFiltros();
    cargarDashboard();
});

function inicializarEventosFiltros() {
    $("#btnAplicarFiltros").on("click", function () {
        cargarDashboard();
    });

    $("#btnLimpiarFiltros").on("click", function () {
        $("#filtroSucursal").val("");
        $("#filtroDesde").val("");
        $("#filtroHasta").val("");
        cargarDashboard();
    });
}

function cargarDashboard() {
    const data = obtenerMockData();

    actualizarKpis(data.kpis);
    actualizarTablaNovedadesTipo(data.novedadesPorTipo);
    actualizarChartNovedadesTipo(data.novedadesPorTipo);

    actualizarChartNovedadesMes(data.novedadesPorMes);

    actualizarChartTopEmpleados(data.topEmpleados);

    actualizarKpiAreas(data.kpiAreas);
    actualizarTablaAreas(data.areas);
    actualizarChartAreas(data.areas);
}

function actualizarKpis(kpis) {
    $("#kpiEmpleadosActivos").text(kpis.empleadosActivos);
    $("#kpiEmpleadosActivosDetalle").text(kpis.empleadosActivosDetalle);

    $("#kpiEmpleadosBaja").text(kpis.empleadosBaja);
    $("#kpiEmpleadosBajaDetalle").text(kpis.empleadosBajaDetalle);

    $("#kpiTotalNovedades").text(kpis.totalNovedades);
    $("#kpiTotalNovedadesDetalle").text(kpis.totalNovedadesDetalle);

    $("#kpiNovedadesMes").text(kpis.novedadesMesActual);
    $("#kpiNovedadesMesDetalle").text(kpis.novedadesMesDetalle);

    $("#kpiNovedadMasFrecuente").text(kpis.novedadMasFrecuente.descripcion);
    $("#kpiNovedadMasFrecuenteCantidad").text(kpis.novedadMasFrecuente.cantidad + " registros");

    $("#kpiNovedadMenosFrecuente").text(kpis.novedadMenosFrecuente.descripcion);
    $("#kpiNovedadMenosFrecuenteCantidad").text(kpis.novedadMenosFrecuente.cantidad + " registros");
}

function actualizarTablaNovedadesTipo(items) {
    const $tbody = $("#tblNovedadesTipo tbody");
    $tbody.empty();

    items.forEach(n => {
        $tbody.append(`
            <tr>
                <td>${n.tipo}</td>
                <td>${n.categoria}</td>
                <td>${n.cantidad}</td>
                <td>${n.porcentaje.toFixed(1)}%</td>
            </tr>
        `);
    });
}

function actualizarChartNovedadesTipo(items) {
    const ctx = document.getElementById("chartNovedadesTipo");

    if (chartNovedadesTipo) chartNovedadesTipo.destroy();

    chartNovedadesTipo = new Chart(ctx, {
        type: "pie",
        data: {
            labels: items.map(i => i.tipo),
            datasets: [{
                data: items.map(i => i.cantidad),
                backgroundColor: [
                    "#0d6efd", "#20c997", "#ffc107",
                    "#fd7e14", "#198754", "#6f42c1", "#dc3545"
                ]
            }]
        },
        options: { responsive: true }
    });
}

function actualizarChartNovedadesMes(items) {
    const ctx = document.getElementById("chartNovedadesMes");

    if (chartNovedadesMes) chartNovedadesMes.destroy();

    chartNovedadesMes = new Chart(ctx, {
        type: "bar",
        data: {
            labels: items.map(i => i.mes),
            datasets: [{
                label: "Novedades",
                data: items.map(i => i.cantidad),
                backgroundColor: "#0d6efd"
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const max = items.reduce((a, b) => a.cantidad > b.cantidad ? a : b);
    $("#lblMesMaxNovedades").text(max.mes + " (" + max.cantidad + ")");
}

function actualizarChartTopEmpleados(items) {
    const ctx = document.getElementById("chartTopEmpleados");

    if (chartTopEmpleados) chartTopEmpleados.destroy();

    chartTopEmpleados = new Chart(ctx, {
        type: "bar",
        data: {
            labels: items.map(i => i.empleado),
            datasets: [{
                label: "Novedades",
                data: items.map(i => i.cantidad),
                backgroundColor: "#20c997"
            }]
        },
        options: {
            indexAxis: "y",
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    if (items.length > 0)
        $("#lblEmpleadoTop").text(items[0].empleado + " (" + items[0].cantidad + ")");
}

function actualizarKpiAreas(k) {
    $("#kpiAreaMasNovedades").text(k.areaMasNovedades.area);
    $("#kpiAreaMasNovedadesDetalle").text(k.areaMasNovedades.cantidad + " registros");

    $("#kpiAreaMenosNovedades").text(k.areaMenosNovedades.area);
    $("#kpiAreaMenosNovedadesDetalle").text(k.areaMenosNovedades.cantidad + " registros");
}

function actualizarTablaAreas(items) {
    const $tbody = $("#tblNovedadesArea tbody");
    $tbody.empty();

    items.forEach(n => {
        $tbody.append(`
            <tr>
                <td>${n.area}</td>
                <td>${n.cantidad}</td>
                <td>${n.porcentaje.toFixed(1)}%</td>
            </tr>
        `);
    });
}

function actualizarChartAreas(items) {
    const ctx = document.getElementById("chartNovedadesAreas");

    if (chartNovedadesAreas) chartNovedadesAreas.destroy();

    chartNovedadesAreas = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: items.map(i => i.area),
            datasets: [{
                data: items.map(i => i.cantidad),
                backgroundColor: [
                    "#0d6efd", "#20c997", "#ffc107",
                    "#fd7e14", "#198754", "#6f42c1"
                ]
            }]
        },
        options: { plugins: { legend: { position: "bottom" } } }
    });
}

function obtenerMockData() {
    return {
        kpis: {
            empleadosActivos: 124,
            empleadosActivosDetalle: "Sobre 137 totales",
            empleadosBaja: 13,
            empleadosBajaDetalle: "Últimos 12 meses",
            totalNovedades: 982,
            totalNovedadesDetalle: "Histórico",
            novedadesMesActual: 87,
            novedadesMesDetalle: "Mes en curso",
            novedadMasFrecuente: { descripcion: "Horas extras", cantidad: 312 },
            novedadMenosFrecuente: { descripcion: "Accidente - ART", cantidad: 7 }
        },

        novedadesPorTipo: [
            { tipo: "Horas extras", categoria: "Productividad", cantidad: 312, porcentaje: 31.8 },
            { tipo: "Licencias", categoria: "Ausentismo", cantidad: 241, porcentaje: 24.5 },
            { tipo: "Faltas injustificadas", categoria: "Ausentismo", cantidad: 73, porcentaje: 7.4 },
            { tipo: "Noche", categoria: "Adicional", cantidad: 128, porcentaje: 13.0 },
            { tipo: "UTI", categoria: "Servicio crítico", cantidad: 94, porcentaje: 9.6 },
            { tipo: "Accidente - ART", categoria: "Riesgo", cantidad: 7, porcentaje: 0.7 },
            { tipo: "Adic. Admin.", categoria: "Adicional", cantidad: 127, porcentaje: 12.9 }
        ],

        novedadesPorMes: [
            { mes: "Ene", cantidad: 70 },
            { mes: "Feb", cantidad: 61 },
            { mes: "Mar", cantidad: 74 },
            { mes: "Abr", cantidad: 80 },
            { mes: "May", cantidad: 91 },
            { mes: "Jun", cantidad: 88 },
            { mes: "Jul", cantidad: 97 },
            { mes: "Ago", cantidad: 101 },
            { mes: "Sep", cantidad: 89 },
            { mes: "Oct", cantidad: 93 },
            { mes: "Nov", cantidad: 82 },
            { mes: "Dic", cantidad: 76 }
        ],

        topEmpleados: [
            { empleado: "García, Ana", cantidad: 21 },
            { empleado: "Pérez, Juan", cantidad: 19 },
            { empleado: "López, Marta", cantidad: 17 },
            { empleado: "Rodríguez, Luis", cantidad: 15 },
            { empleado: "Fernández, Sofía", cantidad: 14 }
        ],

        areas: [
            { area: "Enfermería", cantidad: 310, porcentaje: 31.6 },
            { area: "Administración", cantidad: 189, porcentaje: 19.2 },
            { area: "Limpieza", cantidad: 205, porcentaje: 20.9 },
            { area: "Mantenimiento", cantidad: 122, porcentaje: 12.4 },
            { area: "Cocina", cantidad: 68, porcentaje: 6.9 },
            { area: "Sistemas", cantidad: 4, porcentaje: 5.0 }
        ],

        kpiAreas: {
            areaMasNovedades: { area: "Enfermería", cantidad: 310 },
            areaMenosNovedades: { area: "Sistemas", cantidad: 4 }
        }
    };
}
