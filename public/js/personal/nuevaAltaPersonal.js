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

$(document).on("change", "#selectorTipoVinculo", function () {
    const tipo = $(this).val();
    const esDep = tipo === "DEPENDENCIA";
    const esExt = tipo !== "" && !esDep;

    $("#divDependencia")
        .toggleClass("d-none", !esDep)
        .find("input, select")
        .prop("disabled", !esDep);
    $("#divExternos")
        .toggleClass("d-none", !esExt)
        .find("input, select")
        .prop("disabled", !esExt);
});
