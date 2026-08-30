let tablaCateg=null;

function cargarTablaCategorias() {
    tablaCateg = new DataTable("#tb_categ", {
        ajax: {
            url: "/rrhh/categorias-empleados/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_categ", className:"text-start" }, 
            { data: "nombre", className:"text-start",
                render:function(data){
                    return `
                        <input class="form-control inputNombreCateg"
                    `
                }
             }
        ],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "58vh",
        info: false,
        searching: false,
    });

    $("#tb_categ tbody").on("click", "tr", function () {
        const rowData = tablaCateg.row(this).data();
        verDetalleCateg(rowData.id_categ);
    });
};

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