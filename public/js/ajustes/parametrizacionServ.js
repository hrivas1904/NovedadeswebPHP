let tablaServiciosAreas = null;

function cargarServiciosAreaSeleccionada(idArea, nombreArea) {
    $("#nombreAreaTurnos").text(nombreArea);
    $("#inputIdAreaServ").val(idArea);

    if ($.fn.DataTable.isDataTable("#tb_servicios_areas")) {
        $("#tb_servicios_areas")
            .DataTable()
            .ajax.url(`/rrhh/servicios-empleados/por-area/${idArea}`)
            .load();
        return;
    }

    tablaServiciosAreas = $("#tb_servicios_areas").DataTable({
        ajax: {
            url: `/rrhh/servicios-empleados/por-area/${idArea}`,
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_servicios", className: "text-start" },
            {
                data: "servicio",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="text" class="form-control inputServicio" data-campo="nombre" value="${data}">
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
                                class="form-check-input switchActivoServicio inputServicio"
                                type="checkbox"
                                role="switch"
                                data-id="${row.id_Servicios}"
                                data-campo="estado"
                                ${checked}>
                        </div>
                    `;
                },
            },
        ],

        language: {
            url: "/js/es-ES.json",
        },

        paging: false,
        searching: false,
        info: false,
        autoWidth: false,
        scrollX: false,
        scrollY: "55vh",
        scrollCollapse: true,
    });
}

$(document).on("submit", "#formNuevoServicio", function (e) {
    e.preventDefault();

    const idArea = $("#inputIdAreaServ").val();

    if (!idArea) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Primero debe seleccionar un área.",
            confirmButtonColor: "#1DAC8A",
        });

        return;
    }

    const formData = $(this).serialize();

    $.ajax({
        url: "/rrhh/servicios/crear",
        method: "POST",
        data: formData,

        success: function (response) {
            const codigo = Number(response.codigo);

            if (codigo === 1) {
                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa!",
                    text: response.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                $("#formNuevoServicio")[0].reset();
                $("#inputIdAreaServ").val(idArea);

                if ($.fn.DataTable.isDataTable("#tb_servicios_areas")) {
                    $("#tb_servicios_areas")
                        .DataTable()
                        .ajax.reload(null, false);
                }
            } else if (codigo === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: response.message,
                    confirmButtonColor: "#1DAC8A",
                });
            }
        },

        error: function (xhr) {
            let mensaje = "Ocurrió un error inesperado.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje,
                confirmButtonColor: "#1DAC8A",
                showCancelButton: false,
                showConfirmButton: false,
            });
        },
    });
});

$(document).on("change", "#tb_servicios_areas .inputServicio", function () {
    const input = $(this);

    const tabla = $("#tb_servicios_areas").DataTable();

    const fila = input.closest("tr");

    const datos = tabla.row(fila).data();

    if (!datos) return;

    const idServicio = datos.id_servicios;

    const campo = input.attr("data-campo");

    let valor;

    if (input.is(":checkbox")) {
        valor = input.is(":checked") ? 1 : 0;
    } else {
        valor = input.val();
    }

    console.log({
        idServicio,
        campo,
        valor,
    });

    actualizarCampoServicio(idServicio, campo, valor);
});

function actualizarCampoServicio(idServicio, campo, valor) {
    $.ajax({
        url: `/rrhh/servicios/${idServicio}/campo`,

        type: "PUT",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            campo: campo,
            valor: valor,
        },

        success: function (response) {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Servicio actualizado correctamente.",
                icon: "success",
                timer: 1200,
                showConfirmButton: false,
            });

            $("#tb_servicios_areas").DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error("ERROR ACTUALIZANDO SERVICIO:", xhr);

            console.error(xhr.responseText);

            let mensaje = "No se pudo actualizar el servicio.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                title: "Error",
                text: mensaje,
                icon: "error",
            });

            // Restauramos el valor real desde BD
            $("#tb_servicios_areas").DataTable().ajax.reload(null, false);
        },
    });
}
