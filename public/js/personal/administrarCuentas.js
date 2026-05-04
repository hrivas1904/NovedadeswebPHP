$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function listarCuentasBancarias(legajo) {
    $.ajax({
        url: "/personal/cuentasBancarias",
        type: "GET",
        data: { legajo: legajo },
        success: function (respuesta) {
            if (respuesta.success) {
                let html = "";

                respuesta.data.forEach((r) => {
                    html += `
                        <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                            <label class="req-card w-100">
                                <div class="row">
                                    <div class="col-lg-8 col-12">

                                        <div class="req-check">
                                            <input type="radio"
                                                name="cuenta_${r.legajo}"
                                                value="${r.id}"
                                                ${r.prioridad == 1 ? "checked" : ""}>
                                            <span class="req-title">
                                                <strong>Banco: </strong>${r.banco}
                                                ${r.prioridad == 1 ? '<span class="badge bg-success ms-2">Predeterminada</span>' : ""}
                                            </span>
                                        </div>

                                        <div class="mt-2">
                                            <label class="form-label mb-0">CBU</label>
                                            <input type="text"
                                                class="form-control form-control-sm input-cbu"
                                                value="${r.cbu ?? ""}"
                                                disabled>
                                        </div>

                                        <div class="mt-2">
                                            <label class="form-label mb-0">Cuenta N°</label>
                                            <input type="text"
                                                class="form-control form-control-sm input-cuenta"
                                                value="${r.numero_cuenta ?? ""}"
                                                disabled>
                                        </div>

                                    </div>

                                    <div class="col-lg-4 col-12 text-end">
                                        <button type="button" class="btn btn-primary btn-editar-cuenta" title="Editar cuenta">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button"  class="btn btn-primary btn-guardar-cuenta d-none" title="Guardar">
                                            <i class="fa-regular fa-floppy-disk"></i>
                                        </button>
                                        <button type="button"  class="btn btn-danger btn-eliminar-cuenta" title="Eliminar cuenta">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>                                
                            </label>
                        </div>
                    `;
                });

                $("#contenedorCuentasBancarias").html(html);
            } else {
                Swal.fire("Error", respuesta.message, "error");
            }
        },
        error: function (xhr, status, error) {
            Swal.fire("Error", error, "error");
        },
    });

    $(document).on("click", ".btn-editar-cuenta", function () {
        let card = $(this).closest(".req-card");

        // habilitar inputs
        card.find(".input-cbu, .input-cuenta").prop("disabled", false);

        // mostrar botón guardar
        card.find(".btn-guardar-cuenta").removeClass("d-none");

        // ocultar botón editar
        $(this).addClass("d-none");
    });

    $(document).on("click", ".btn-guardar-cuenta", function () {
        let card = $(this).closest(".req-card");

        let cbu = card.find(".input-cbu").val();
        let cuenta = card.find(".input-cuenta").val();

        let id = card.find("input[type=radio]").val();

        $.ajax({
            url: "/cuentas/actualizar",
            type: "POST",
            data: {
                id: id,
                cbu: cbu,
                numero_cuenta: cuenta,
            },
            success: function (res) {
                if (res.success == 1) {
                    Swal.fire({
                        title: "Operación exitosa",
                        text: res.mensaje,
                        icon: "success",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    });

                    card.find(".input-cbu, .input-cuenta").prop(
                        "disabled",
                        true,
                    );

                    card.find(".btn-editar-cuenta").removeClass("d-none");
                    card.find(".btn-guardar-cuenta").addClass("d-none");
                } else {
                    Swal.fire({
                        title: "Error",
                        text: res.mensaje,
                        icon: "error",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        },
                    });
                }
            },
        });
    });

    $(document).on("click", ".btn-eliminar-cuenta", function () {
        let card = $(this).closest(".req-card");
        let id = card.find("input[type=radio]").val();

        Swal.fire({
            title: "¿Eliminar cuenta?",
            text: "Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            confirmButtonColor: "#00b18d",
            cancelButtonText: "Cancelar",
            cancelButtonColor: "#004a7c",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/cuentas/eliminar",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    data: { id: id },

                    success: function (res) {
                        if (res.success == 1) {
                            Swal.fire("OK", res.mensaje, "success");

                            card.closest(".col-lg-3").remove();
                        } else {
                            Swal.fire("Atención", res.mensaje, "warning");
                        }
                    },
                });
            }
        });
    });
}

$("#btnMostraDivNuevaCuenta").on("click", function () {
    const div = $("#divCrearCuentaBancariaColab");
    div.removeClass("d-none");
});

$("#btnOcultarDivCuentaNueva").on("click", function () {
    const div = $("#divCrearCuentaBancariaColab");
    div.addClass("d-none");
    $("#selectBanco").val("");
    $("#inputNumeroCuenta").val("");
    $("#nputCbu").val("");
});

$(document).on("click", "#btnGuardarCuenta", function () {
    let banco = $("#selectBanco").val();
    let numeroCuenta = $("#inputNumeroCuenta").val();
    let cbu = $("#inputCbu").val();
    let legajo = $("#inputLegajo").val();

    if (!banco || !numeroCuenta || !cbu) {
        Swal.fire("Atención", "Completá todos los campos", "warning");
        return;
    }

    if (cbu.length !== 22) {
        Swal.fire("Atención", "El CBU debe tener 22 dígitos", "warning");
        return;
    }

    $.ajax({
        url: "/cuentas/crear",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            legajo: legajo,
            banco: banco,
            numero_cuenta: numeroCuenta,
            cbu: cbu,
        },

        success: function (res) {
            if (res.success == 1) {
                Swal.fire({
                    title: "Operación exitosa",
                    text: res.mensaje,
                    icon: "success",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });

                $("#divCrearCuentaBancariaColab").find("input, select").val("");

                $("#divCrearCuentaBancariaColab").addClass("d-none");

                cargarCuentasBancarias(legajo);
            } else {
                Swal.fire({
                    title: "Atencion",
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
                text: "No se pudo guardar la cuenta",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
    });
});

$(document).on("change", "input[type=radio][name^='cuenta_']", function () {
    let id = $(this).val();
    let legajo = $("#inputLegajo").val();

    $.ajax({
        url: "/cuentas/priorizar",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            id: id,
            legajo: legajo,
        },
        success: function (res) {
            if (res.success == 1) {
                Swal.fire({
                    icon: "success",
                    title: "Cuenta predeterminada actualizada",
                    timer: 1200,
                    showConfirmButton: false,
                });
                listarCuentasBancarias(legajo);
            } else {
                Swal.fire("Atención", res.mensaje, "warning");
            }
        },
    });
});
