$("#togglePrioridad").on("click", function () {
    $("#listaPrioridades").toggleClass("d-none");
});

$("#toggleAutorizacion").on("click", function () {
    $("#listaAutorizacion").toggleClass("d-none");
});

$("#toggleEstado").on("click", function () {
    $("#listaEstados").toggleClass("d-none");
});

function getAreasSeleccionadas() {
    return $(".check-Prioridades:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getNovedadesSeleccionadas() {
    return $(".check-Autorizacion:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getFinnegansSeleccionadas() {
    return $(".check-Estados:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}