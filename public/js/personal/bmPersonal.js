//DAR BAJA COLABORADOR
function darDeBaja(legajoColaborador, nombre) {
    $("#modalDarBajaColaborador").modal("show");

    $("#tituloBajaColab").html(`
        <i class="fa-solid fa-id-card me-2"></i>
        Dar de baja a <strong>${nombre}</strong>
    `);

    $("#inputLegajoBaja").val(legajoColaborador);
}

$(document).on("submit", "#formBajaColaborador", function (e) {
    e.preventDefault();

    const legajo = $("#inputLegajoBaja").val();

    Swal.fire({
        title: "¿Confirmar baja?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, dar de baja",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-secondary",
        },
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `/rrhh/personal/baja/${legajo}`,
            type: "POST",
            data: {
                fechaBaja: $("#fechaBajaColab").val(),
                motivo: $("#selectMotivoBajaColab").val(),
                observaciones: $("input[name='observacionesBaja']").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: "Operación exitosa",
                        text: res.mensaje,
                        icon: "success",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    });
                    $("#modalDarBajaColaborador").modal("hide");
                    tablaPersonal.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        title: "Atención",
                        text: res.mensaje,
                        icon: "warning",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Error",
                    text: "No se pudo dar de baja",
                    icon: "error",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });
            },
        });
    });
});

//EDICIÓN PERSONAL
