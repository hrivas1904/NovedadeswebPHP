$("#btnAgregarContacto").on("click", function () {
    agregarFormNuevoContacto($("#divContactosEmergencia"));
});

$("#btnAgregarFamiliar").on("click", function () {
    agregarFormNuevoFamiliar($("#divFamiliares"));
});

$(document).ready(function () {
    obtenerLocalidadesGeoRest(
        $("#selectLocalidad"),
        $("#modalAltaColaborador"),
    );
    agregarFormNuevoContacto($("#divContactosEmergencia"));
    agregarFormNuevoFamiliar($("#divFamiliares"));
    obtenerTiposContratosSelector($("#selectorTipoContratoExt"));
    obtenerTiposContratosSelector($("#selectorTipoContratoDep"));
    obtenerServiciosSelector($("#selectorServicioExterno"));
});

$(document).on("change", "#selectorTipoVinculo", function () {
    const valor = $(this).val();

    const esDependencia = valor === "DEPENDENCIA";
    const esExterno = valor === "EXTERNO";

    // Relación de dependencia
    $("#divDependencia")
        .toggleClass("d-none", !esDependencia)
        .find("input, select, textarea")
        .prop("disabled", !esDependencia);

    // Personal externo
    $("#divExterno")
        .toggleClass("d-none", !esExterno)
        .find("input, select, textarea")
        .prop("disabled", !esExterno);

    // Cargar obras sociales
    if (esDependencia) {
        obtenerObrasSociales(
            $("#selectorObrasSociales"),
            $("#modalAltaColaborador"),
            $("#inputCodigoObraSocial"),
        );
    }
});
