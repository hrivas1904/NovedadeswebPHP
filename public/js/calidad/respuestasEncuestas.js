function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "50vh";
}

$(document).ready(function () {
    cargarTipoEncuestas();
});

function cargarTipoEncuestas() {
    $.ajax({
        url: "/encuestas/tipos",
        method: "GET",
        success: function (data) {
            const select = $("#selectTipoEncuesta");
            select.empty();

            select.append(
                '<option value="">Seleccione tipo de encuesta</option>',
            );

            data.forEach((tipo) => {
                select.append(`
                    <option value="${tipo.id}">
                        ${tipo.text}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando tipos de encuestas");
        },
    });
}