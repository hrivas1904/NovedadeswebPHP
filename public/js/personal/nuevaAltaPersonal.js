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
    console.log("Tipo de vínculo seleccionado:", valor);
    const esDependencia = valor === "Dependencia";
    const esExterno = valor === "Externo";
    $("#divDependencia").toggleClass("d-none", !esDependencia);
    $("#divExterno").toggleClass("d-none", !esExterno);
    if (esDependencia) {
        obtenerObrasSociales(
            $("#selectorObrasSociales"),
            $("#modalAltaColaborador"),
            $("#inputCodigoObraSocial"),
        );
    }
});
