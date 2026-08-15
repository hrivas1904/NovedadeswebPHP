let fechaCrono = new Date();

const meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre"
];

$("#btnOcultarAcciones").on("click", function () {
    $("#divAcciones").toggleClass("d-none");
    const oculto = $("#divAcciones").hasClass("d-none");
    $("#iconUpAcciones")
        .toggleClass("fa-angles-up", !oculto)
        .toggleClass("fa-angles-down", oculto);
    $("#textoAcciones").text(oculto ? "Mostrar acciones" : "Ocultar acciones");
});

$("#btnOcultarIndicadores").on("click", function () {
    $("#divIndicadores").toggleClass("d-none");
    const oculto = $("#divIndicadores").hasClass("d-none");
    $("#iconUpIndicadores")
        .toggleClass("fa-angles-up", !oculto)
        .toggleClass("fa-angles-down", oculto);
    $("#textoIndicadores").text(oculto ? "Mostrar indicadores" : "Ocultar indicadores");
});

function cargarMesesCrono() {
    const fechaActual = new Date();
    const anioActual = fechaActual.getFullYear();
    const mesActual = fechaActual.getMonth();

    const $selector = $("#selectorMesCrono");
    $selector.empty();

    for (let anio = anioActual; anio <= anioActual + 1; anio++) {
        meses.forEach((nombreMes, indiceMes) => {
            const mes = String(indiceMes + 1).padStart(2, "0");
            const valor = `${anio}-${mes}`;
            $selector.append(`
                <option value="${valor}">
                    ${nombreMes} ${anio}
                </option>
            `);
        });
    }

    const valorActual =
        `${anioActual}-${String(mesActual + 1).padStart(2, "0")}`;
    $selector.val(valorActual);
}

$("#btnMesAnterior").on("click", function () {
    const $selector = $("#selectorMesCrono");
    const indiceActual = $selector.prop("selectedIndex");
    if (indiceActual > 0) {
        $selector.prop("selectedIndex", indiceActual - 1).trigger("change");
    }
});

$("#btnMesSiguiente").on("click", function () {
    const $selector = $("#selectorMesCrono");
    const indiceActual = $selector.prop("selectedIndex");
    const cantidadOpciones = $selector.find("option").length;
    if (indiceActual < cantidadOpciones - 1) {
        $selector.prop("selectedIndex", indiceActual + 1).trigger("change");
    }
});

function cargarAreas() {
    $.ajax({
        url: "/rrhh/areas/lista",
        type: "GET",
        dataType: "json",
        success: function (res) {
            const $selector = $("#selectorArea");
            $selector.empty();
            $selector.append('<option value="">Seleccione área</option>');
            res.forEach(area => {
                $selector.append(`
                    <option value="${area.id_area}">
                        ${area.nombre}
                    </option>
                `);
            });
        },
        error: function (xhr) {
            console.error("Error al cargar las áreas:", xhr.responseText);
        }
    });
}

function cargarServiciosPorArea(idArea) {
    const $selector = $("#selectorServicio");
    $selector.empty();
    $selector.append('<option value="">Seleccione servicio</option>');

    if (!idArea) {
        $selector.prop("disabled", true);
        return;
    }
    $selector.prop("disabled", true);
    $.ajax({
        url: `/rrhh/servicios-empleados/por-area/${idArea}`,
        type: "GET",
        dataType: "json",

        success: function (res) {
            $selector.empty();
            $selector.append('<option value="">Seleccione servicio</option>');

            res.forEach(servicio => {
                $selector.append(`
                    <option value="${servicio.id_servicios}">
                        ${servicio.servicio}
                    </option>
                `);
            });
            $selector.prop("disabled", false);
        },

        error: function (xhr) {
            console.error(
                "Error al cargar los servicios:",
                xhr.responseText
            );

            $selector
                .empty()
                .append('<option value="">Error al cargar servicios</option>')
                .prop("disabled", true);
        }
    });
}

$("#selectorArea").on("change", function () {
    const idArea = $(this).val();
    cargarServiciosPorArea(idArea);
});

$(document).ready(function () {
    cargarMesesCrono();
    cargarAreas();
});