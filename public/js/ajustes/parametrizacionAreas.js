let tablaArea = null;

function cargarTablaAreas() {
    tablaArea = $("#tb_areas").DataTable({
        ajax: {
            url: "/rrhh/areas/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_area", className: "text-start" }, 
            { data: "nombre", className: "text-start",
                render: function(data){
                    return `
                        <input type="text" class="form-control inputNombreTabla" value="${data}">
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
        scrollY: "55vh",
        info: false,
        searching: false,
        scrollCollapse: true,
    });

    $("#tb_areas tbody").off("click", "tr").on("click", "tr", function () {
        const rowData = tablaArea.row(this).data();
        if (!rowData) return;
        cargarServiciosAreaSeleccionada(rowData.id_area, rowData.nombre);
        cargarTurnosAreaSeleccionada(rowData.id_area, rowData.nombre);
        cargarFuncionesAreaSeleccionada(rowData.id_area, rowData.nombre);
    });
};


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