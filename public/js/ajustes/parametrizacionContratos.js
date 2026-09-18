let tablaContratos = null;

function cargarTablaContratos() {
    $("#tbContratos").DataTable({
        ajax: {
            url: "/rrhh/contratos/obtenerTiposContratos",
            type: "GET",
            dataSrc: "",
        },
        columns: [
            { data: "id", className: "text-start" },
            {
                data: "nombre",
                render: function (data, type, row) {
                    return `
                        <input class="form-control inputContratos" data-id="${row.id}" data-campo="nombreContrato" value="${data}">
                    `;
                },
            },
            {
                data: "estado",
                className: "text-center",
                render: function (data, type, row) {
                    const checked = data == 1 ? "checked" : "";

                    return `
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input 
                                class="form-check-input inputContratos"
                                type="checkbox"
                                role="switch"
                                data-campo="estadoContrato"
                                data-id="${row.id}"
                                ${checked}
                            >
                        </div>
                    `;
                },
            },
        ],
        language: {
            url: "/js/es-ES.json",
        },

        paging: false,
        scrollX: false,
        scrollY: "22vh",
        scrollCollapse: true,
        info: false,
        searching: false,
        autoWidth: false,
    });
}

$(document).on("submit", "#formNuevaTipoContrato", function (e) {
    e.preventDefault();

    if (!$("#nombreTipoContrato").val().trim()) {
        Swal.fire({
            title: "Atención!",
            text: "El campo nombre es obligatorio.",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
        });
        return;
    }

    $.ajax({
        url: "/rrhh/contratos/registrarNuevoTipoContrato",
        type: "POST",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $("#formNuevaTipoContrato")[0].reset();

            Swal.fire({
                title: "Operación exitosa!",
                text: "Tipo de contrato registrado correctamente.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
            });

            $("#tbContratos").DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
            console.error("ERROR AJAX:", xhr);
            console.error("STATUS:", xhr.status);
            console.error("RESPUESTA:", xhr.responseText);

            Swal.fire({
                title: "Error",
                text:
                    xhr.responseJSON?.detalle ??
                    xhr.responseJSON?.error ??
                    "Error al registrar el tipo de contrato.",
                icon: "error",
            });
        },
    });
});

$(document).on(
    "change",
    '.inputContratos[data-campo="nombreContrato"]',
    function () {

        const id = $(this).data("id");
        const nombre = $(this).val().trim();

        if (!nombre) {
            Swal.fire({
                title: "¡Atención!",
                text: "El nombre del tipo de contrato no puede estar vacío.",
                icon: "warning",
                timer: 2000,
                showConfirmButton: false,
            });

            return;
        }

        $.ajax({
            url: "/rrhh/contratos/editarNombreTipoContrato",
            type: "PUT",

            data: {
                idTipo: id,
                nombre: nombre,
            },

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            success: function (response) {
                Swal.fire({
                    title: "Actualizado",
                    text: "Nombre actualizado correctamente.",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false,
                });
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                Swal.fire({
                    title: "Error",
                    text:
                        xhr.responseJSON?.detalle ??
                        "No se pudo actualizar el nombre.",
                    icon: "error",
                });
            },
        });
    }
);

$(document).on(
    "change",
    '.inputContratos[data-campo="estadoContrato"]',
    function () {

        const id = $(this).data("id");
        const estado = $(this).is(":checked") ? 1 : 0;

        $.ajax({
            url: "/rrhh/contratos/editarEstadoTipoContrato",
            type: "PUT",

            data: {
                idTipo: id,
                estado: estado,
            },

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            success: function (response) {
                Swal.fire({
                    title: "Actualizado",
                    text: "Estado actualizado correctamente.",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false,
                });
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                Swal.fire({
                    title: "Error",
                    text:
                        xhr.responseJSON?.detalle ??
                        "No se pudo actualizar el estado.",
                    icon: "error",
                });
            },
        });
    }
);