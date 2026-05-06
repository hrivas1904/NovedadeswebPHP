let nombre;
let dni;
let fechaNacimiento;

function cargarHijoColab(legajo) {
    $.ajax({
        url: "/personal/listarFamiliares",
        type: "GET",
        data: { legajo: legajo },

        success: function (res) {
            if (res.success) {
                const familiares = res.data;
                $("#divHijosEdit").empty().removeAttr("hidden");

                if (familiares && familiares.length > 0) {
                    familiares.forEach((f) => {
                        const htmlFamiliar = `
                            <div class="col-lg-4 col-12">
                                <div class="card p-3 cardHijos">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-8 col-12">
                                            <label class="form-label">Nombre</label>
                                            <input class="form-control inputNombre" value="${f.nombre}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">DNI</label>
                                            <input class="form-control inputDni" value="${f.dni}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">Fecha nacimiento</label>
                                            <input type="date" class="form-control inputNacimiento" value="${f.fechaNacimiento}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">Parentesco</label>
                                            <input class="form-control" value="${f.parentesco}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12 justify-content-end d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary btn-edit-hijo">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-eliminar-hijo">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#divHijosEdit").append(htmlFamiliar);
                    });
                }
            } else {
                Swal.fire({
                    title: "Error",
                    icon: "error",
                    text: res.message,
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pueden cargar los hijos del colaborador",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });

    $(document).on("click", ".btn-edit-hijo", function () {
        let card=$(this).closest(".cardHijos");
        card.find(".inputNombre, inputDni, inputFechaNacimiento").prop('disable',false);
    });
}
