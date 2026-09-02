let tablaCateg = null;

function cargarTablaCategorias() {
    tablaCateg = new DataTable("#tb_categ", {
        ajax: {
            url: "/rrhh/categorias-empleados/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_categ", className: "text-start" },
            {
                data: "nombre",
                className: "text-start",
                render: function (data) {
                    return `
                        <input class="form-control inputCategoria" data-campo="nombre" value="${data}">
                    `;
                },
            },
            {
                data: "estado",
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }
                    const checked = Number(data) === 1 ? "checked" : "";
                    return `
                        <div class="form-check form-switch d-flex justify-content-center m-0">
                            <input
                                class="form-check-input switchActivoServicio inputCategoria"
                                type="checkbox"
                                role="switch"
                                data-campo="estado"
                                data-id="${row.id_categ}"
                                ${checked}>
                        </div>
                    `;
                },
            },
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
}

$(document)
    .off("submit", "#formNuevaCategoria")
    .on("submit", "#formNuevaCategoria", function (e) {
        e.preventDefault();

        console.log("SUBMIT CATEGORÍA CAPTURADO");

        const formData = $(this).serialize();

        console.log("DATOS:", formData);

        $.ajax({
            url: "/rrhh/categorias/crear",
            method: "POST",
            data: formData,

            success: function (response) {
                const codigo = Number(response.codigo);

                if (codigo === 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: response.message,
                        confirmButtonColor: "#1DAC8A",
                    });

                    $("#formNuevaCategoria")[0].reset();

                    if ($.fn.DataTable.isDataTable("#tb_categ")) {
                        $("#tb_categ").DataTable().ajax.reload(null, false);
                    }
                } else if (codigo === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Atención",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1200,
                    });
                }
            },

            error: function (xhr) {
                console.error("ERROR AL CREAR CATEGORÍA:", xhr.responseText);

                let mensaje = "Ocurrió un error inesperado.";

                if (xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: false,
                    timer: 1200,
                });
            },
        });
    });

$(document).on("change", "#tb_categ .inputCategoria", function () {
    const input = $(this);

    const tabla = $("#tb_categ").DataTable();

    const fila = input.closest("tr");

    const datos = tabla.row(fila).data();

    if (!datos) return;

    const idCategoria = datos.id_categ;

    const campo = input.attr("data-campo");

    let valor;

    if (input.is(":checkbox")) {
        valor = input.is(":checked") ? 1 : 0;
    } else {
        valor = input.val();
    }

    console.log({
        idCategoria,
        campo,
        valor,
    });

    actualizarCampoCategoria(idCategoria, campo, valor);
});

function actualizarCampoCategoria(idCategoria, campo, valor) {
    $.ajax({
        url: `/rrhh/categorias/${idCategoria}/campo`,

        type: "PUT",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            campo: campo,
            valor: valor,
        },

        success: function (response) {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Categoría actualizada correctamente.",
                timer: 1200,
                showConfirmButton: false,
            });

            $("#tb_categ").DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error("ERROR ACTUALIZANDO CATEGORÍA:", xhr.responseText);

            let mensaje = "No se pudo actualizar la categoría.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                title: "Error",
                text: mensaje,
                icon: "error",
                timer: 1200,
                showConfirmButton: false,
            });

            // Restauramos el dato real desde BD
            $("#tb_categ").DataTable().ajax.reload(null, false);
        },
    });
}
