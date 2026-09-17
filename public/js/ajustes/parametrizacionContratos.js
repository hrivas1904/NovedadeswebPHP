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
                render: function (data) {
                    return `
                        <input class="form-control inputContratos" data-campo="nombreContrato" value="${data}">
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

$("#formNuevaTipoContrato").on("submit", function (e) {
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

            $("#tbContratos").DataTable.ajax.reload();
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
