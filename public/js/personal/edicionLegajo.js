$(document).on("submit", "#formLegajo", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $("#btnGuardarLegajo");

    armarCuilLegajo();

    // régimen: se guardan las horas (texto de la opción), igual que los datos migrados
    const datos = $form.serializeArray().map(function (campo) {
        if (campo.name === "regimen") {
            const texto = $("#selectorRegimenLegajo option:selected")
                .text()
                .trim();
            return { name: "regimen", value: /^\d+$/.test(texto) ? texto : "" };
        }
        return campo;
    });

    $btn.prop("disabled", true);

    $.ajax({
        url: "/rrhh/personal/guardarEdicionLegajo",
        type: "POST",
        dataType: "json",
        data: $.param(datos),
        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Legajo actualizado",
                timer: 1400,
                showConfirmButton: false,
            });
            verLegajo(legajoActual.id_personal); // recarga con los ids nuevos de contactos/familiares
            if (typeof tablaPersonal !== "undefined")
                tablaPersonal.ajax.reload(null, false);
        },
        error: function (xhr) {
            const r = xhr.responseJSON || {};
            let msg = "No se pudo guardar el legajo.";
            if (r.errors) msg = Object.values(r.errors)[0][0];
            else if (r.message) msg = r.message;
            Swal.fire("Error", msg, "error");
        },
        complete: function () {
            $btn.prop("disabled", false);
        },
    });
});
