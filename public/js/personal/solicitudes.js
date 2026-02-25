function formatearPesos(valor) {
    if (valor === null || valor === undefined || valor === "") return "";

    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    }).format(valor);
}

function parsearMonto(valor) {
    if (!valor) return 0;
    let limpio = valor.toString();
    limpio = limpio.replace(/\$/g, "").trim();
    limpio = limpio.replace(/\./g, "");
    limpio = limpio.replace(/,/g, ".");

    const resultado = parseFloat(limpio);
    return isNaN(resultado) ? 0 : resultado;
}

$("#inputMonto").on("blur", function () {
    const valorInput = $(this).val();
    const montoFormateado = formatearPesos(valorInput);
    $(this).val(montoFormateado);
});

$("#inputMonto").on("focus", function () {
    $(this).val("");
});

function abrirSolicitud() {
    const legajo = USER_LEGAJO;

    Swal.showLoading();

    $.ajax({
        url: `/personal/info/${legajo}`,
        type: "GET",
        success: function (data) {
            Swal.close();
            const $modal = $("#modalRegSolicitud");

            $modal.find("input[name='colaborador']").val(data.COLABORADOR);
            $modal.find("input[name='servicio']").val(data.SERVICIO);
            $modal.modal("show");
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo obtener la información del colaborador",
            });
        },
    });
}

$("#btnAbrirModalSolicitud").on("click", function () {
    abrirSolicitud();
});

function cerrarModalSolicitud() {
    const modalSolicitud = $("#modalRegSolicitud");
    const formulario = $("#formCargaSolicitud");
    formulario[0].reset();
    modalSolicitud.modal("hide");
}
