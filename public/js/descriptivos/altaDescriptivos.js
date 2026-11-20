$(document).ready(function () {
    obtenerAreasSelector($("#selectAreaDescriptivo"));
    obtenerServiciosSelector($("#selectServicioDescriptivo"));
    obtenerPuestosTrabajoSelector($("#selectPuestoDescriptivo"));
});

$(document).on("change", "#selectPuestoDescriptivo", function () {
    const $nombre = $("#inputNombrePuestoDescriptivo");
    if ($(this).val() && !$nombre.val().trim()) {
        $nombre.val($(this).find("option:selected").text().trim());
    }
});

$(document).on("submit", "#formAltaDescriptivo", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const esEdicion = !!$("#hiddenIdDescriptivoEdicion").val();

    $btn.prop("disabled", true);

    $.ajax({
        url: esEdicion ? "/rrhh/descriptivos/editarDescriptivo" : "/rrhh/descriptivos/altaDescriptivo",
        type: "POST",
        dataType: "json",
        data: $form.serialize(),
        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Descriptivo creado",
                text: res.message,
            });
            $("#modalAltaDescriptivo").modal("hide");
            $form[0].reset();
            $("#divCompetenciasGenericas, #divCompetenciasEspecificas").empty();
            tablaDescriptivos.ajax.reload(null, false);
            if (esEdicion) verDetalleDescriptivo(res.data.id_descriptivo);
        },
        error: function (xhr) {
            const r = xhr.responseJSON;
            let msg = "No se pudo guardar el descriptivo.";
            if (r && r.errors) msg = Object.values(r.errors)[0][0];
            else if (r && r.message) msg = r.message;
            Swal.fire("Error", msg, "error");
        },
        complete: function () {
            $btn.prop("disabled", false);
        },
    });
});
