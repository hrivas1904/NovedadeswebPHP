$(".inputDniPersonal").on("change", function () {
    const regexDNI = /^[0-9]{8}[A-Za-z]$/;
    const valorDni = $(this).val();

    if (regexDNI.test(valorDni)) {
        Swal.fire({
            icon: "warning",
            title: "Atención!",
            text: "El DNI debe contener sólo letras y debe tener una longitud de 8 dígitos",
            showConfirmButton: false,
            timer: 1500,
        });
    }

    if (!valorDni) {
        Swal.fire({
            icon: "warning",
            title: "Atención!",
            text: "El DNI es obligatorio",
            showConfirmButton: false,
            timer: 1200,
        });
    }

    $(".midCuilPersonal").val(valorDni);
});

$("#btnAgregarContacto").on("click", function () {
    agregarFormNuevoContacto($("#divContactosEmergencia"));
});

$("#btnAgregarFamiliar").on("click", function () {
    agregarFormNuevoFamiliar($("#divFamiliares"));
});

$(document).ready(function(){
    obtenerLocalidadesGeoRest(
        $("#selectLocalidad"),
        $("#modalAltaColaborador"),
    );
    agregarFormNuevoContacto($("#divContactosEmergencia"));
    agregarFormNuevoFamiliar($("#divFamiliares"));
    obtenerTiposContratosSelector($("#selectorTipoContratoExt"));
    obtenerTiposContratosSelector($("#selectorTipoContratoDep"));
    obtenerServiciosSelector($("#selectorServicioExterno"));
    obtenerObrasSociales($("#selectorObrasSociales"));
});
