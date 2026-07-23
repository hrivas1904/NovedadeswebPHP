
$(document).ready(function () {
    cargarAreas();
    cargarNovedades();
});

$("#toggleAreas").on("click", function () {
    $("#listaAreas").toggleClass("d-none");
});

$("#toggleNov").on("click", function () {
    $("#listaNov").toggleClass("d-none");
});

$("#toggleFinnegans").on("click", function () {
    $("#listaFinnegas").toggleClass("d-none");
});

function cargarAreas() {
    $.ajax({
        url: "/rrhh/areas/lista",
        method: "GET",
        dataType:'json',
        success: function (data) {
            const container = $("#listaAreas");

            container.empty();

            data.forEach((areas) => {

                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-area" 
                               value="${areas.id_area}">
                        ${areas.nombre}
                    </label>
                `);
            });
        },
    });
}

function cargarNovedades() {
    $.ajax({
        url: "/rrhh/novedades/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            const container = $("#listaNov");

            container.empty();

            data.forEach((novedad) => {

                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-nov" 
                               value="${novedad.ID_NOVEDAD}">
                        ${novedad.NOMBRE}
                    </label>
                `);
            });
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

function getNovedadesSeleccionadas() {
    return $(".check-nov:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getFinnegansSeleccionadas() {
    return $(".check-Finnegans:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}