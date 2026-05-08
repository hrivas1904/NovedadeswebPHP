let card;
let cbu;
let cuenta;
let id;
let banco;
let numeroCuenta;

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
                                                readonly>
                                            <input type="text"
                                                class="form-control form-control-sm input-legajo d-none"
                                                value="${r.legajo ?? ""}">
                                        </div>

                                        <div class="mt-2">
                                            <label class="form-label mb-0">Cuenta N°</label>
                                            <input type="text"
                                                class="form-control form-control-sm input-cuenta"
                                                value="${r.numero_cuenta ?? ""}"
                                                readonly>
                                        </div>

                                    </div>

                                    <div class="col-lg-4 col-12 text-end">
                                        <button type="button" class="btn btn-sm btn-primary btn-editar-cuenta" title="Editar cuenta">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button"  class="btn btn-sm btn-primary btn-guardar-cuenta d-none" title="Guardar">
                                            <i class="fa-regular fa-floppy-disk"></i>
                                        </button>
                                        <button type="button"  class="btn btn-sm btn-danger btn-eliminar-cuenta" title="Eliminar cuenta">
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
}

$(document).on("click", ".btn-editar-cuenta", function () {
    card = $(this).closest(".req-card");
    card.find(".input-cbu, .input-cuenta").prop("readonly", false);
    card.find(".btn-guardar-cuenta").removeClass("d-none");
    $(this).addClass("d-none");
});

$(document).on("click", ".btn-guardar-cuenta", function () {
    card = $(this).closest(".req-card");
    cbu = card.find(".input-cbu").val();
    cuenta = card.find(".input-cuenta").val();
    id = card.find("input[type=radio]").val();
    legajo=card.find(".input-legajo").val();

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
                card.find(".input-cbu, .input-cuenta").prop("readonly",true,);
                card.find(".btn-editar-cuenta").removeClass("d-none");
                card.find(".btn-guardar-cuenta").addClass("d-none");

                listarCuentasBancarias(legajo)

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
    card = $(this).closest(".req-card");
    id = card.find("input[type=radio]").val();
    legajo=card.find(".input-legajo").val();

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
                        Swal.fire({
                            title: "Operación exitosa",
                            text: res.mensaje,
                            icon: "success",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });
                        listarCuentasBancarias(legajo)
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
            });
        }
    });
});

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
    banco = $("#selectBanco").val();
    numeroCuenta = $("#inputNumeroCuenta").val();
    cbu = $("#inputCbu").val();
    legajo = $("#inputLegajo").val();

    if (!banco || !cbu) {
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "Debe completar Banco y CBU.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    }

    if (cbu.length !== 22) {
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "El CBU debe tener 22 dígitos.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
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

                listarCuentasBancarias(legajo);
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
    id = $(this).val();
    legajo = $("#inputLegajo").val();

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
