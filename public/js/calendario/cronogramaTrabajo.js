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

function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "65vh";
}

function obtenerNombreDia(fechaString) {
    if (!fechaString) return "Sin fecha";
    const soloFecha = fechaString.split(' ')[0]; 
    const partes = soloFecha.split('-');

    const anio = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10) - 1;
    const dia = parseInt(partes[2], 10);

    const fechaObj = new Date(anio, mes, dia);
    const nombreDia = fechaObj.toLocaleDateString('es-ES', { weekday: 'long' });
    return nombreDia.charAt(0).toUpperCase() + nombreDia.slice(1);
}

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
    $.get(RUTAS_CRONO_GRILLA.periodosVisibles, function (resp) {
        const periodos = resp.data.sort((a, b) => a.periodo.localeCompare(b.periodo));
        const $selector = $("#selectorMesCrono");
        $selector.empty();

        periodos.forEach(p => {
            const [anio, mes] = p.periodo.split('-');
            const nombreMes = meses[parseInt(mes, 10) - 1];
            $selector.append(`<option value="${p.periodo}">${nombreMes} ${anio}</option>`);
        });

        if (periodos.length) {
            $selector.val(periodos[periodos.length - 1].periodo);
        }
    });
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
    $.get(RUTAS_CRONO_GRILLA.areas, function (res) {
        const $selector = $("#selectorArea");
        $selector.empty();
        $selector.append('<option value="">Seleccione área</option>');
        res.data.forEach(area => {
            $selector.append(`<option value="${area.ID_AREA}">${area.NOMBRE}</option>`);
        });
    }).fail(function (xhr) {
        console.error("Error al cargar las áreas:", xhr.responseText);
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