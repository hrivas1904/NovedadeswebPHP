$(document).ready(function () {
    cargarAreas();
    cargarFiltroCateg();
    cargarFiltroConvenio();
});

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "60vh";
}

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