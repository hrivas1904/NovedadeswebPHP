$(document).ready(function () {
    cargarAreas();
    cargarFiltroCateg();
    cargarFiltroConvenio();
});

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "60vh";
}

$("#btnExportExcel").on("click", function () {
    let filtros = {
        area_id: $("#filtroArea").val() || null,
        categ_id: $("#filtroCategoria").val() || null,
        p_convenio: $("#filtroConvenio").val() || null,
        p_regimen: $("#filtroRegimen").val() || null,
        p_uti: $("#filtroUti").val() || null,
        p_noche: $("#filtroNoche").val() || null,
    };

    Object.keys(filtros).forEach((key) => {
        if (!filtros[key]) filtros[key] = null;
    });

    let query = $.param(filtros);

    window.open("/personal/exportarListaColabDatatable?" + query, "_blank");
});

$("#btnExportExcelBaja").on("click", function () {
    let filtros = {
        area_id: $("#filtroArea").val() || null,
        categ_id: $("#filtroCategoria").val() || null,
        p_convenio: $("#filtroConvenio").val() || null,
        p_regimen: $("#filtroRegimen").val() || null,
        p_uti: $("#filtroUti").val() || null,
        p_noche: $("#filtroNoche").val() || null,
    };

    Object.keys(filtros).forEach((key) => {
        if (!filtros[key]) filtros[key] = null;
    });

    let query = $.param(filtros);

    window.open("/personal/exportarListaColabBajaDatatable?" + query, "_blank");
});

//CALCULAR EDAD
function calcularEdad(fecha) {
    if (!fecha) return "";

    const nacimiento = new Date(fecha);
    const hoy = new Date();

    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const m = hoy.getMonth() - nacimiento.getMonth();

    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }

    return edad;
}

//CALCULAR ANTINGÜEDAD
function calcularAntiguedad(fecha) {
    if (!fecha) return "";

    const ingreso = new Date(fecha);
    const hoy = new Date();

    let años = hoy.getFullYear() - ingreso.getFullYear();
    let meses = hoy.getMonth() - ingreso.getMonth();

    // Ajuste si todavía no cumplió el mes
    if (hoy.getDate() < ingreso.getDate()) {
        meses--;
    }

    // Ajuste si los meses quedan negativos
    if (meses < 0) {
        años--;
        meses += 12;
    }

    let texto = "";

    if (años > 0) {
        texto += `${años} año${años > 1 ? "s" : ""}`;
    }

    if (meses > 0) {
        texto += (texto ? " " : "") + `${meses} mes${meses > 1 ? "es" : ""}`;
    }

    return texto || "0 meses";
}

//FORMATEAR FECHA ARG
function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

//FILTROS TIPO ECOMMERCE
function cargarAreas() {
    $.ajax({
        url: "/areas/lista",
        method: "GET",
        success: function (data) {
            const container = $("#listaAreas");
            const areaFija = $("#areaFija").val();

            container.empty();

            data.forEach((area) => {
                const checked = areaFija == area.id_area ? "checked" : "";
                const disabled = areaFija ? "disabled" : "";

                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-area" 
                               value="${area.id_area}" 
                               ${checked} ${disabled}>
                        ${area.nombre}
                    </label>
                `);
            });
        },
    });
}

function cargarFiltroCateg() {
    $.ajax({
        url: "/categorias-empleados/lista",
        method: "GET",
        success: function (data) {
            const container = $("#listaCateg");

            container.empty();

            data.forEach((categoria) => {
                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-categ" 
                               value="${categoria.id_categ}">
                        ${categoria.nombre}
                    </label>
                `);
            });
        },
        error: function () {
            console.error("Error cargando categorías");
        },
    });
}

function cargarFiltroConvenio() {
    $.ajax({
        url: "/convenios/lista",
        method: "GET",
        success: function (data) {
            const container = $("#listaConvenios");

            container.empty();

            data.forEach((convenio) => {
                if (!convenio.convenio || convenio.convenio.trim() === "") {
                    return;
                }
                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-convenio" 
                               value="${convenio.convenio}">
                        ${convenio.convenio}
                    </label>
                `);
            });
        },
        error: function () {
            console.error("Error cargando convenios");
        },
    });
}

function getAreasSeleccionadas() {
    return $(".check-area:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getCategoriasSeleccionadas() {
    return $(".check-categ:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getConveniosSeleccionados() {
    return $(".check-convenio:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

$("#toggleAreas").on("click", function () {
    $("#listaAreas").toggleClass("d-none");
});

$("#toggleCateg").on("click", function () {
    $("#listaCateg").toggleClass("d-none");
});

$("#toggleConvenios").on("click", function () {
    $("#listaConvenios").toggleClass("d-none");
});

//REQUERIMIENTOS ALIMENTARIOS
function cargarRequerimientosAlimentarios() {
    $.ajax({
        url: "/requerimientos-alimentarios",
        type: "GET",
        success: function (data) {
            let html = "";

            data.forEach((r) => {
                html += `
                    <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                        <label class="req-card w-100">
                            <div class="req-check">
                                <input type="checkbox"
                                       name="req_alimenticios[]"
                                       value="${r.id}">
                                <span class="req-title">
                                    ${r.nombre}
                                </span>
                            </div>

                            <small class="req-desc">
                                ${r.descripcion ?? ""}
                            </small>
                        </label>
                    </div>
                `;
            });

            $("#contenedorRequerimientos").html(html);
        },
        error: function () {
            console.error("Error cargando requerimientos alimentarios");
        },
    });
}

$(document).on("change", "input[name='req_alimenticios[]']", function () {
    const idOtro = 12; // ID "Otro"
    const seleccionados = $("input[name='req_alimenticios[]']:checked")
        .map(function () {
            return parseInt($(this).val());
        })
        .get();

    if (seleccionados.includes(idOtro)) {
        $("#rowObservacionReq").slideDown(150);
    } else {
        $("#rowObservacionReq").slideUp(150);
        $("#inputReqOtro").val("");
    }
});

function cargarReqSeleccionados(legajo) {
    $.ajax({
        url: `/personal/${legajo}/req-alimenticios`,
        type: "GET",
        success: function (data) {
            // limpiar todos
            $("input[name='req_alimenticios[]']").prop("checked", false);

            $("#rowObservacionReq").hide();
            $("#inputReqOtro").val("");

            data.forEach((r) => {
                $(
                    `input[name='req_alimenticios[]'][value='${r.idRequerimiento}']`,
                ).prop("checked", true);

                // si es "otro"
                if (r.idRequerimiento == 12) {
                    $("#rowObservacionReq").show();
                    $("#inputReqOtro").val(r.observ);
                }
            });
        },
    });
}

//HISTORIAL DE NOVEDADES
function inicializarORefrescarHistorial() {
    if (tablaHistorialNovedades) {
        tablaHistorialNovedades.ajax
            .url(`/novedades/historial/${legajoActivo}`)
            .load(null, false);
        return;
    }

    tablaHistorialNovedades = $("#tb_historialNovedades").DataTable({
        ajax: {
            url: `/novedades/historial/${legajoActivo}`,
            type: "GET",
            dataSrc: "data",
        },
        order: [[0, "desc"]],
        columns: [
            { data: "REGISTRO" },
            {
                data: "FECHA_REGISTRO",
                width: "7%",
                render: function (data) {
                    return formatearFechaArgentina(data);
                },
            },
            { 
                data: "NOVEDAD_NOMBRE",
                width: "35%"
            },
            {
                data: "FECHA_DESDE",
                width:"3%",
                render: function (data) {
                    return formatearFechaArgentina(data);
                },
            },
            {
                data: "FECHA_HASTA",
                width:"3%",
                render: function (data) {
                    return formatearFechaArgentina(data);
                },
            },
            {
                data: "DURACION",
                width:"7%",
                className: "text-start",
                render: (d) => d ?? "-",
            },
            {
                data:"DESCRIPCION",
                width: "40%",
                className: "text-wrap"
            },
            {
                data: "REGISTRO",
                width:"5%",
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function (data) {
                    return `
                        <button type="button" class="btn btn-primary btn-subir-archivo" data-registro="${data}">
                            <i class="fa-solid fa-upload"></i> Subir
                        </button>
                        <button type="button"
                            class="btn btn-secondary btn-ver-archivo"
                            data-registro="${data}">
                            <i class="fa-solid fa-eye"></i> Ver
                        </button>
                    `;
                },
            },
        ],
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: "40vh",
        searching: true,
        ordering: true,
        language: {
            url: "/js/es-ES.json",
        },
        dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'B> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="fa-solid fa-file-excel"></i> Excel',
                className: "btn-export-excel",
                exportOptions: { columns: ":visible" },
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                className: "btn-export-pdf",
                exportOptions: { columns: ":visible" },
            },
        ],
    });
}

//VER LEGAJO COLABORADOR
function abrirModalLegajo() {
    $("#modalLegajoColaborador").modal("show");
}

function cerrarModalLegajo() {
    $("#formEditColaborador")[0].reset();
    $("#selectObraSocialEdit").val(null).trigger("change");
    $("#selectLocalidadEdit").val(null).trigger("change");
    $("#modalLegajoColaborador").modal("hide");
}
