$("#toggleAreas").on("click", function () {
    $("#listaAreas").toggleClass("d-none");
});

$("#toggleCateg").on("click", function () {
    $("#listaCateg").toggleClass("d-none");
});

$("#toggleConvenios").on("click", function () {
    $("#listaConvenios").toggleClass("d-none");
});

function cargarAreas() {
    $.ajax({
        url: "/areas/lista",
        method: "GET",
        success: function (data) {
            const container = $("#listaAreas");

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

$(document).ready(function () {
    cargarAreas();
});