let satisfaccion;

$(document).ready(function () {
    let mybutton = $("#btn-back-to-top");

    $(window).on("scroll", function () {
        if ($(window).scrollTop() > 100) {
            mybutton.fadeIn();
        } else {
            mybutton.fadeOut();
        }
    });

    mybutton.on("click", function () {
        $("html, body").animate(
            {
                scrollTop: 0,
            },
            500,
        );
    });
});

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
                satisfaccion = res.kpi[0].satisfaccion ?? 0;

                if (satisfaccion >= 95) {
                }

                $("#kpiSatisfaccion").text(satisfaccion + "%");
            }

            if (res.kpiGuardiaGeneral && res.kpiGuardiaGeneral.length > 0) {
                satisfaccion = res.kpiGuardiaGeneral[0].satisfaccion ?? 0;

                if (satisfaccion >= 95) {
                }
                $("#kpiGuardiaGeneral").text(satisfaccion + "%");
            }

            if (
                res.kpiInternacionGeneral &&
                res.kpiInternacionGeneral.length > 0
            ) {
                satisfaccion = res.kpiInternacionGeneral[0].satisfaccion ?? 0;

                if (satisfaccion >= 95) {
                }
                $("#kpiInternacionGeneral").text(satisfaccion + "%");
            }

            if (
                res.kpiInternacionAmbulatoria &&
                res.kpiInternacionAmbulatoria.length > 0
            ) {
                satisfaccion =
                    res.kpiInternacionAmbulatoria[0].satisfaccion ?? 0;

                if (satisfaccion >= 95) {
                }
                $("#kpiInternacionAmbulatoria").text(satisfaccion + "%");
            }

            renderTablaGuardias(res.guardias);
            renderTablaAreas(res.areas);
            renderTablaExpectativasAmbulatoria(res.expectativasAmbulatoria);
            renderTablaUti(res.uti);
            renderTablaInternacion(res.internacion);
            renderTablaGuardiaPreguntas(res.guardia_adulto_preguntas);
            renderTablaGuardiaPediatricaPreguntas(res.guardia_pediatrica_preguntas,);
            renderTablaInternacionPreguntas(res.internacion_preguntas);
            renderTablaInternacionPreguntasGeneral(res.internacion_preguntas_general);
            renderTablaInternacionAmb(res.internacion_amb_preguntas);
        },
    });
}

//EXPECTATIAS
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
            { title: "Total", data: "total_respuestas" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 95
                            ? "#00b18d"
                            : data >= 90
                              ? "#ffc107"
                              : "#d64545";
                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid white;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${data}%
                        </span>
                    `;
                },
            },
        ],
        pageLength: 5,
        language:{
            url:"/js/es-ES.json",
        },
        footerCallback: function (row, data) {
            let totalGeneral = data.reduce((acc, item) => {
                return acc + (parseInt(item.total_respuestas) || 0);
            }, 0);
            $(this.api().table().footer()).html(`
                <tr>
                    <th colspan="6" class="text-end">
                        Total de respuestas: ${totalGeneral}
                    </th>
                </tr>
            `);
        },
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
            { title: "Total", data: "total_respuestas" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 95
                            ? "#00b18d"
                            : data >= 90
                              ? "#ffc107"
                              : "#d64545";
                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${data}%
                        </span>
                    `;
                },
            },
        ],
        pageLength: 5,
        language:{
            url:"/js/es-ES.json",
        },

        footerCallback: function (row, data) {
            let totalGeneral = data.reduce((acc, item) => {
                return acc + (parseInt(item.total_respuestas) || 0);
            }, 0);
            $(this.api().table().footer()).html(`
                <tr>
                    <th colspan="6" class="text-end">
                        Total de respuestas: ${totalGeneral}
                    </th>
                </tr>
            `);
        },
    });
}

function renderTablaExpectativasAmbulatoria(data) {
    if ($.fn.DataTable.isDataTable("#tablaExpAmb")) {
        $("#tablaExpAmb").DataTable().destroy();
    }

    $("#tablaExpAmb").DataTable({
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
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "Total", data: "total_respuestas" },
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 95
                            ? "#00b18d"
                            : data >= 90
                              ? "#ffc107"
                              : "#d64545";
                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${data}%
                        </span>
                    `;
                },
            },
        ],
        pageLength: 5,
        language:{
            url:"/js/es-ES.json",
        },
        footerCallback: function (row, data) {
            let totalGeneral = data.reduce((acc, item) => {
                return acc + (parseInt(item.total_respuestas) || 0);
            }, 0);
            $(this.api().table().footer()).html(`
                <tr>
                    <th colspan="6" class="text-end">
                        Total de respuestas: ${totalGeneral}
                    </th>
                </tr>
            `);
        },
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
            {
                title: "% Positivas",
                data: "porc_positivas",
                render: function (data) {
                    let color =
                        data >= 95
                            ? "#00b18d"
                            : data >= 90
                              ? "#ffc107"
                              : "#d64545";
                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${data}%
                        </span>
                    `;
                },
            },
        ],
        pageLength: 5,
        language:{
            url:"/js/es-ES.json",
        }
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
        language:{
            url:"/js/es-ES.json",
        },
    });
}

//RESULTADOS POR TIPO DE ENCUESTAS

function renderTablaGuardiaPreguntas(data) {
    if ($.fn.DataTable.isDataTable("#tablaGuardia")) {
        $("#tablaGuardia").DataTable().destroy();
    }

    $("#tablaGuardia").DataTable({
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
            { title: "Pregunta", data: "pregunta" },

            {
                title: "Positivas",
                data: "positivos",
                render: function (data) {
                    return `<span style="color:green;font-weight:bold">${data}</span>`;
                },
            },

            {
                title: "Negativas",
                data: "negativos",
                render: function (data) {
                    return `<span style="color:red;font-weight:bold">${data}</span>`;
                },
            },

            {
                title: "No Aplica",
                data: "no_aplica",
            },

            {
                title: "Total",
                data: "total_respuestas",
            },

            {
                title: "%Positivas",
                data: null,
                render: function (row) {
                    let total = row.total_respuestas;
                    let positivos = row.positivos;
                    let noAplica=row.no_aplica;

                    let porcentaje =
                        total > 0 ? ((positivos * 100) / (total-noAplica)).toFixed(1) : 0;

                    let color =
                        porcentaje >= 95
                            ? "#00b18d"
                            : porcentaje >= 90
                              ? "#ffc107"
                              : "#d64545";

                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${porcentaje}%
                        </span>
                    `;
                },
            },
        ],
        language:{
            url:"/js/es-ES.json",
        }
    });
}

function renderTablaGuardiaPediatricaPreguntas(data) {
    if ($.fn.DataTable.isDataTable("#tablaGuardiaPediatrica")) {
        $("#tablaGuardiaPediatrica").DataTable().destroy();
    }

    $("#tablaGuardiaPediatrica").DataTable({
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
            { title: "Pregunta", data: "pregunta" },

            {
                title: "Positivas",
                data: "positivos",
                render: function (data) {
                    return `<span style="color:green;font-weight:bold">${data}</span>`;
                },
            },

            {
                title: "Negativas",
                data: "negativos",
                render: function (data) {
                    return `<span style="color:red;font-weight:bold">${data}</span>`;
                },
            },

            {
                title: "No Aplica",
                data: "no_aplica",
            },

            {
                title: "Total",
                data: "total_respuestas",
            },

            {
                title: "%Positivas",
                data: null,
                render: function (row) {
                    let total = row.total_respuestas;
                    let positivos = row.positivos;
                    let noAplica=row.no_aplica;

                    let porcentaje =
                        total > 0 ? ((positivos * 100) / (total-noAplica)).toFixed(1) : 0;

                    let color =
                        porcentaje >= 95
                            ? "#00b18d"
                            : porcentaje >= 90
                              ? "#ffc107"
                              : "#d64545";

                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${porcentaje}%
                        </span>
                    `;
                },
            },
        ],
        language:{
            url:"/js/es-ES.json",
        }
    });
}

function renderTablaInternacionPreguntas(data) {
    if ($.fn.DataTable.isDataTable("#tablaInternacionPreguntas")) {
        $("#tablaInternacionPreguntas").DataTable().destroy();
    }

    $("#tablaInternacionPreguntas").DataTable({
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

            { title: "Pregunta", data: "pregunta" },

            {
                title: "Positivas",
                data: "positivos",
                render: (d) =>
                    `<span style="color:green;font-weight:bold">${d}</span>`,
            },

            {
                title: "Negativas",
                data: "negativos",
                render: (d) =>
                    `<span style="color:red;font-weight:bold">${d}</span>`,
            },

            {
                title: "No Aplica",
                data: "no_aplica",
            },

            {
                title: "Total",
                data: "total_respuestas",
            },

            {
                title: "% Positivas",
                data: null,
                render: function (row) {
                    let total = row.total_respuestas;
                    let positivos = row.positivos;
                    let noAplica=row.no_aplica;

                    let porcentaje =
                        total > 0 ? ((positivos * 100) / (total-noAplica)).toFixed(1) : 0;

                    let color =
                        porcentaje >= 95
                            ? "#00b18d"
                            : porcentaje >= 90
                              ? "#ffc107"
                              : "#d64545";

                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${porcentaje}%
                        </span>
                    `;
                },
            },
        ],

        order: [
            [0, "asc"],
            [6, "asc"],
        ],
        language:{
            url:"/js/es-ES.json",
        }
    });
}

function renderTablaInternacionPreguntasGeneral(data) {
    if ($.fn.DataTable.isDataTable("#tablaInternacionPreguntasGeneral")) {
        $("#tablaInternacionPreguntasGeneral").DataTable().destroy();
    }

    $("#tablaInternacionPreguntasGeneral").DataTable({
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
            { title: "Pregunta", data: "pregunta" },

            {
                title: "Positivas",
                data: "positivos",
                render: (d) =>
                    `<span style="color:green;font-weight:bold">${d}</span>`,
            },

            {
                title: "Negativas",
                data: "negativos",
                render: (d) =>
                    `<span style="color:red;font-weight:bold">${d}</span>`,
            },

            {
                title: "No Aplica",
                data: "no_aplica",
            },

            {
                title: "Total",
                data: "total_respuestas",
            },

            {
                title: "% Positivas",
                data: null,
                render: function (row) {
                    let total = row.total_respuestas;
                    let positivos = row.positivos;
                    let noAplica=row.no_aplica;

                    let porcentaje =
                        total > 0 ? ((positivos * 100) / (total-noAplica)).toFixed(1) : 0;
                    

                    let color =
                        porcentaje >= 95
                            ? "#00b18d"
                            : porcentaje >= 90
                              ? "#ffc107"
                              : "#d64545";

                    return `
                        <span class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${porcentaje}%
                        </span>
                    `;
                },
            },
        ],

        order: [
            [0, "asc"],
            [6, "asc"],
        ],
        language:{
            url:"/js/es-ES.json",
        }
    });
}

function renderTablaInternacionAmb(data) {

    if ($.fn.DataTable.isDataTable("#tablaInternacionAmbPregunta")) {
        $("#tablaInternacionAmbPregunta").DataTable().destroy();
        $("#tablaInternacionAmbPregunta tbody").empty();
    }

    $("#tablaInternacionAmbPregunta").DataTable({
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
            {
                title: "Pregunta",
                data: "pregunta"
            },

            {
                title: "Positivas",
                data: "positivos",
                render: (d) =>
                    `<span style="color:green;font-weight:bold">${d}</span>`
            },

            {
                title: "Negativas",
                data: "negativos",
                render: (d) =>
                    `<span style="color:red;font-weight:bold">${d}</span>`
            },

            {
                title: "No Aplica",
                data: "no_aplica"
            },

            {
                title: "Total",
                data: "total_respuestas"
            },

            {
                title: "% Positivas",
                data: null,
                render: function (data, type, row) {

                    let total = parseInt(row.total_respuestas) || 0;
                    let positivos = parseInt(row.positivos) || 0;
                    let noAplica = parseInt(row.no_aplica) || 0;

                    let base = total - noAplica;

                    let porcentaje =
                        base > 0
                            ? ((positivos * 100) / base).toFixed(1)
                            : 0;

                    let color =
                        porcentaje >= 95
                            ? "#00b18d"
                            : porcentaje >= 90
                                ? "#ffc107"
                                : "#d64545";

                    return `
                        <span
                            class="badge px-3 py-2"
                            style="
                                background:${color};
                                color:white;
                                border:1px solid;
                                font-weight:600;
                                font-size:1rem;
                                min-width:75px;
                            ">
                            ${porcentaje}%
                        </span>
                    `;
                }
            }
        ],

        order: [[0, "asc"]],

        language: {
            url: "/js/es-ES.json"
        }
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

$("#btnKpiPromedioGuardias").on("click", function () {
    $("html, body").animate(
        {
            scrollTop: $("#sectionResultadosGuardias").offset().top - 80,
        },
        500,
    );
});

$("#btnKpiPromedioInternacion").on("click", function () {
    $("html, body").animate(
        {
            scrollTop:
                $("#sectionResultadosInternacionEstadia").offset().top - 80,
        },
        500,
    );
});

$("#btnKpiPromedioAmbulatoria").on("click", function () {
    $("html, body").animate(
        {
            scrollTop:
                $("#sectionResultadosInternacionAmbulatoria").offset().top - 80,
        },
        500,
    );
});
