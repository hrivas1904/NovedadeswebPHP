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
    obtenerPuestosTrabajoActivos($("#selectorPuestoTrabajoPersonal"));
    obtenerCategoriasActivas($("#selectorCategoriaPersonal"));
    obtenerRegimenesActivos(
        $("#selectorRegimenPersonal"),
        $("#inputHorasDiariasPersonal"),
    );
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

$(".inputDniPersonal").on("change", function(){
    const dni=$(this).val();
    $(".midCuilPersonal").val(dni);
})

function armarCuil() {
    const cuil =
        $(".preCuilPersonal").val() +
        $(".midCuilPersonal").val() +
        $(".posCuilPersonal").val();
    $(".inputCuilPersonal").val(cuil);
}

$(document).on("submit", "#formAltaColaborador", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');

    armarCuil();
    $btn.prop("disabled", true);

    $.ajax({
        url: "/rrhh/personal/alta",
        method: "POST",
        data: $form.serialize(),
        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Alta registrada",
                text: res.data.legajo
                    ? "Legajo asignado: " + res.data.legajo
                    : res.message,
            });
            $("#modalAltaColaborador").modal("hide");
            $form[0].reset();
            $("#selectLocalidad").val(null).trigger("change");
            $("#divContactosEmergencia, #divFamiliares").empty();
            $("#divDependencia, #divExterno").addClass("d-none");
            // acá recargás la tabla de personal si corresponde
        },
        error: function (xhr) {
            const r = xhr.responseJSON;
            let msg = "No se pudo guardar el alta.";
            if (r && r.errors) msg = Object.values(r.errors)[0][0];
            else if (r && r.message) msg = r.message;
            Swal.fire("Error", msg, "error");
        },
        complete: function () {
            $btn.prop("disabled", false);
        },
    });
});
