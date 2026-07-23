$(document).ready(function () {
    cargarAreasServicios();
});

//AREAS
$(document).ready(function () {
    const tablaArea = new DataTable("#tb_areas", {
        ajax: {
            url: "/rrhh/areas/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_area" }, { data: "nombre" }],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });

    $("#tb_areas tbody").on("click", "tr", function () {
        const rowData = tablaArea.row(this).data();
        verDetalleArea(rowData.id_area);
    });
});

function verDetalleArea(idArea) {
    const modal = $("#modalEdicionArea");

    $.ajax({
        url: "/rrhh/parametrizacion/verArea",
        type: "GET",
        data: {
            idArea: idArea,
        },
        success: function (res) {
            $("#idAreaEdit").val(res.data[0].id_area);
            $("#nombreAreaEdit").val(res.data[0].nombre);
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo cargar la información del área.",
                icon: "error",
                customClass: {
                    confirmColorButton: "btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });

    modal.modal("show");
}

function cerrarModalAreaEdit() {
    const modal = $("#modalEdicionArea");
    const form = $("#formEditArea");
    form[0].reset();
    modal.modal("hide");
}

$("#formNuevaArea").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: "/rrhh/areas/crear",
        method: "POST",
        data: formData,

        success: function (response) {
            if (response.codigo === 1) {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });

                $("#formNuevaArea")[0].reset();
                $("#tb_areas").DataTable().ajax.reload();
            } else if (response.codigo === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado.",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
});

$("#btnEditarArea").on("click", function () {
    const idArea = $("#idAreaEdit").val();
    const nombre = $("#nombreAreaEdit").val();

    $.ajax({
        url: "/rrhh/parametrizacion/editarArea",
        type: "POST",
        data: {
            idArea: idArea,
            nombre: nombre,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: "Éxito",
                    text: "Área actualizada correctamente.",
                    icon: "success",
                    buttonsStyling: false,
                    customClass: { confirmButton: "btn btn-primary" },
                }).then(() => {
                    $("#modalEdicionArea").modal("hide");
                    $("#tb_areas").DataTable().ajax.reload();
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo actualizar el área.",
                icon: "error",
                buttonsStyling: false,
                customClass: { confirmButton: "btn btn-primary" },
            });
        },
    });
});

$("#btnEliminarArea").click(function () {
    let idArea = $("#idAreaEdit").val();

    Swal.fire({
        title: "¿Eliminar área?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn btn-primary me-auto",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/areas/" + idArea,
                type: "POST",
                headers: {
                    "X-HTTP-Method-Override": "DELETE",
                    "X-Requested-With": "XMLHttpRequest",
                },
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    _method: "DELETE",
                },
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            title: "Operación exitosa",
                            text: data.mensaje,
                            icon: "success",
                            customClass: {
                                confirmButton: "btn btn-primary",
                                cancelButton: "btn btn-secondary",
                            },
                            buttonsStyling: false,
                        });
                        $("#modalEdicionArea").modal("hide");
                        $("#tb_areas").DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.mensaje,
                            icon: "error",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 401 || xhr.status === 419) {
                        Swal.fire(
                            "Sesión expirada",
                            "Recargá la página",
                            "warning",
                        ).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Error del servidore",
                            icon: "error",
                            customClass: {
                                confirmButtonColor: "btn-primary",
                                cancelButtonColor: "btn-secondary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
            });
        }
    });
});

//CATEGORÍAS
$(document).ready(function () {
    const tablaCateg = new DataTable("#tb_categ", {
        ajax: {
            url: "/rrhh/categorias-empleados/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_categ" }, { data: "nombre" }],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });

    $("#tb_categ tbody").on("click", "tr", function () {
        const rowData = tablaCateg.row(this).data();
        verDetalleCateg(rowData.id_categ);
    });
});

function verDetalleCateg(idCateg) {
    const modal = $("#modalEdicionCateg");

    $.ajax({
        url: "/rrhh/parametrizacion/verCategoria",
        type: "GET",
        data: {
            idCateg: idCateg,
        },
        success: function (res) {
            $("#idCategEdit").val(res.data[0].id_categ);
            $("#nombreCategEdit").val(res.data[0].nombre);
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo cargar la información de la categoría.",
                icon: "error",
                customClass: {
                    confirmColorButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });

    modal.modal("show");
}

function cerrarModalCategEdit() {
    const modal = $("#modalEdicionCateg");
    const form = $("#formEditCateg");
    form[0].reset();
    modal.modal("hide");
}

$("#formNuevaCategoria").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: "/rrhh/categorias/crear",
        method: "POST",
        data: formData,

        success: function (response) {
            if (response.codigo === 1) {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });

                $("#formNuevaCategoria")[0].reset();
                $("#tb_categ").DataTable().ajax.reload();
            } else if (response.codigo === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado.",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
});

$("#btnEditarCateg").on("click", function () {
    const idCateg = $("#idCategEdit").val();
    const nombre = $("#nombreCategEdit").val();

    $.ajax({
        url: "/rrhh/parametrizacion/editarCateg",
        type: "POST",
        data: {
            idCateg: idCateg,
            nombre: nombre,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: "Éxito",
                    text: "Categoría actualizada correctamente.",
                    icon: "success",
                    buttonsStyling: false,
                    customClass: { confirmButton: "btn btn-primary" },
                }).then(() => {
                    $("#modalEdicionCateg").modal("hide");
                    $("#tb_categ").DataTable().ajax.reload();
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo actualizar la categoría.",
                icon: "error",
                buttonsStyling: false,
                customClass: { confirmButton: "btn btn-primary" },
            });
        },
    });
});

$("#btnEliminarCateg").click(function () {
    let idCateg = $("#idCategEdit").val();

    Swal.fire({
        title: "¿Eliminar categoría?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#1DAC8A",
        cancelButtonColor: "#00558C",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/categ/" + idCateg,
                type: "POST",
                headers: {
                    "X-HTTP-Method-Override": "DELETE",
                    "X-Requested-With": "XMLHttpRequest",
                },
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    _method: "DELETE",
                },
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            title: "Operación exitosa",
                            text: data.mensaje,
                            icon: "success",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            buttonsStyling: false,
                        });
                        $("#modalEdicionCateg").modal("hide");
                        $("#tb_categ").DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.mensaje,
                            icon: "error",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 401 || xhr.status === 419) {
                        Swal.fire(
                            "Sesión expirada",
                            "Recargá la página",
                            "warning",
                        ).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Error del servidore",
                            icon: "error",
                            customClass: {
                                confirmButtonColor: "btn-primary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
            });
        }
    });
});

//SERVICIOS
$(document).ready(function () {
    const tablaServicio = new DataTable("#tb_servicios", {
        ajax: {
            url: "/rrhh/servicios/listar",
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_servicios" },
            { data: "servicio" },
            { data: "id_area", visible: false },
            { data: "area" },
        ],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });

    $("#tb_servicios tbody").on("click", "tr", function () {
        const rowData = tablaServicio.row(this).data();
        verDetalleServicio(rowData.id_servicios);
    });
});

function verDetalleServicio(idServ) {
    const modal = $("#modalEdicionServicio");

    $.ajax({
        url: "/rrhh/parametrizacion/verServicio",
        type: "GET",
        data: {
            idServ: idServ,
        },
        success: function (res) {
            $("#idServEdit").val(res.data[0].id_servicios);
            $("#nombreServEdit").val(res.data[0].servicio);
            $("#areaServEdit").val(res.data[0].id_area).trigger("change");
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo cargar la información del servicio.",
                icon: "error",
                customClass: {
                    confirmColorButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });

    modal.modal("show");
}

function cargarAreasServicios() {
    $.ajax({
        url: "/rrhh/areas/lista",
        type: "GET",
        success: function (data) {
            const selects = [$("#selectAreaServicios"), $("#areaServEdit")];

            selects.forEach(function (select) {
                if (select.length === 0) return;

                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2("destroy");
                }

                select.empty();

                select.append(
                    '<option value="">-- Seleccione un área --</option>',
                );

                data.forEach(function (area) {
                    select.append(`
                        <option value="${area.id_area}">
                            ${area.nombre}
                        </option>
                    `);
                });

                const config = {
                    placeholder: "-- Seleccione un área --",
                    allowClear: true,
                };

                if (select.attr("id") === "areaServEdit") {
                    config.dropdownParent = $("#modalEdicionServicio");
                }

                select.select2(config);
            });
        },
    });
}

function cerrarModalServEdit() {
    const modal = $("#modalEdicionServicio");
    const form = $("#formEditServicio");
    form[0].reset();
    modal.modal("hide");
}

$("#formNuevoServicio").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: "/rrhh/servicios/crear",
        method: "POST",
        data: formData,

        success: function (response) {
            if (response.codigo === 1) {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });

                $("#formNuevoServicio")[0].reset();
                $("#selectAreaServicios").val(null).trigger("change");
                $("#tb_servicios").DataTable().ajax.reload();

            } else if (response.codigo === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado.",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
});

$("#btnEditarServ").on("click", function () {
    const idServ = $("#idServEdit").val();
    const nombre = $("#nombreServEdit").val();
    const idArea = $("#areaServEdit").val();

    $.ajax({
        url: "/rrhh/parametrizacion/editarServicio",
        type: "POST",
        data: {
            idServ: idServ,
            nombre: nombre,
            idArea: idArea,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: "Éxito",
                    text: "Servicio actualizado correctamente.",
                    icon: "success",
                    buttonsStyling: false,
                    customClass: { confirmButton: "btn btn-primary" },
                }).then(() => {
                    $("#modalEdicionServicio").modal("hide");
                    $("#tb_servicios").DataTable().ajax.reload();
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo actualizar el servicio.",
                icon: "error",
                buttonsStyling: false,
                customClass: { confirmButton: "btn btn-primary" },
            });
        },
    });
});

$("#btnEliminarServ").click(function () {
    let idServ = $("#idServEdit").val();

    Swal.fire({
        title: "¿Eliminar servicio?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#1DAC8A",
        cancelButtonColor: "#00558C",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/servicio/" + idServ,
                type: "POST",
                headers: {
                    "X-HTTP-Method-Override": "DELETE",
                    "X-Requested-With": "XMLHttpRequest",
                },
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    _method: "DELETE",
                },
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            title: "Operación exitosa",
                            text: data.mensaje,
                            icon: "success",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            buttonsStyling: false,
                        });
                        $("#modalEdicionServicio").modal("hide");
                        $("#tb_servicios").DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.mensaje,
                            icon: "error",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 401 || xhr.status === 419) {
                        Swal.fire(
                            "Sesión expirada",
                            "Recargá la página",
                            "warning",
                        ).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Error del servidore",
                            icon: "error",
                            customClass: {
                                confirmButtonColor: "btn-primary",
                            },
                            buttonsStyling: false,
                        });
                    }
                },
            });
        }
    });
});
