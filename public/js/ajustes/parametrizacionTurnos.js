let tablaTurnosAreas = null;

function cargarTurnosAreaSeleccionada(idArea, nombreArea) {
    $("#inputIdAreaTurno").val(idArea); 

    if ($.fn.DataTable.isDataTable("#tb_turnos_areas")) {
        tablaTurnosAreas.ajax.url(`/rrhh/turnos/por-area/${idArea}`).load();
        return;
    }

    tablaTurnosAreas = $("#tb_turnos_areas").DataTable({
        ajax: {
            url: `/rrhh/turnos/por-area/${idArea}`,
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id", className: "text-start" },
            {
                data: "nombre",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="text" class="form-control inputTurno" value="${data}" data-campo="nombre">
                    `;
                },
            },
            {
                data: "hora_inicio",
                className: "text-start",
                render: function (data) {
                    const hora = data? data.substring(0, 5): "";
                    return `                        
                        <input type="time" class="form-control inputHoraInicio" value="${data}" data-campo="hora_inicio">
                    `;
                },
            },
            {
                data: "hora_fin",
                className: "text-start",
                render: function (data) {
                    const hora = data? data.substring(0, 5): "";
                    return `                        
                        <input type="time" class="form-control inputHoraFin" value="${data}" data-campo="hora_inicio">
                    `;
                },
            },
            {
                data: "tolerancia_ingreso",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="text" class="form-control inputTolerancia" value="${data ?? 0}" data-campo="tolerancia_ingreso">
                    `;
                },
            },
            {
                data: "horas_reales",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="number" class="form-control inputHorasRaeles" value="${data ?? 0}" data-campo="horas_reales" min=0>
                    `;
                },
            },
            {
                data: "horas_computadas",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="number" class="form-control inputHorasComputadas" value="${data ?? 0}" min=0 data-campo="horas_computadas">
                    `;
                },
            },
            {
                data: "activo",
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
                                class="form-check-input inputTurno switchActivoTurno"
                                type="checkbox"
                                role="switch"
                                data-campo="activo"
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

$(document).on("submit", "#formNuevaTurno", function(e){

    e.preventDefault();

    console.log("Submit turno capturado");

    const idArea = $("#inputIdAreaTurno").val();

    console.log("ID AREA:", idArea);

    if (!idArea) {

        Swal.fire({
            title: "Atención",
            text: "Primero debe seleccionar un área.",
            icon: "warning"
        });

        return;
    }


    $.ajax({

        url: "/rrhh/turnos/crear",
        type: "POST",

        data: $(this).serialize(),

        success: function(response){

            console.log("RESPUESTA:", response);

            $("#formNuevaTurno")[0].reset();

            // El reset borra también el hidden
            $("#inputIdAreaTurno").val(idArea);

            $("#tb_turnos_areas").DataTable().ajax.reload(null, false);

            Swal.fire({
                title: "Operación exitosa!",
                text: "Turno creado correctamente.",
                icon: "success",
                timer: 1200,
                showConfirmButton: false
            });

        },

        error: function(xhr){

            console.error("ERROR AL CREAR TURNO:", xhr);
            console.error("RESPUESTA:", xhr.responseText);

            let mensaje = "No se pudo crear el turno.";

            if (xhr.status === 422 && xhr.responseJSON?.errors) {

                const errores = Object.values(xhr.responseJSON.errors);

                mensaje = errores[0][0];
            }

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                title: "Error",
                text: mensaje,
                icon: "error"
            });

        }

    });

});

$("#tb_turnos_areas").on("change", ".inputTurno", function () {
    const input = $(this);

    const fila = input.closest("tr");

    const datosTurno = tablaTurnosAreas.row(fila).data();

    const idTurno = datosTurno.id;

    const campo = input.data("campo");

    let valor;

    if (input.is(":checkbox")) {
        valor = input.is(":checked") ? 1 : 0;
    } else {
        valor = input.val();
    }

    $.ajax({
        url: `/rrhh/turnos/${idTurno}/campo`,

        type: "PUT",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),

            campo: campo,

            valor: valor,
        },

        success: function (response) {
            // Actualizamos también el objeto interno
            // de DataTables.
            datosTurno[campo] = valor;

            Swal.fire({
                title: "Operación exitosa!",
                text: "Turno actualizado correctamente.",
                timer: 1200,
                showConfirmButton: false,
            });
        },

        error: function (xhr) {
            // Volvemos a consultar la fila para
            // restaurar el valor original.
            tablaTurnosAreas.ajax.reload(null, false);

            let mensaje = "No se pudo actualizar el turno.";

            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errores = Object.values(xhr.responseJSON.errors);

                mensaje = errores[0][0];
            }

            Swal.fire({
                title: "Error",
                text: mensaje,
                icon: "error",
            });
        },
    });
});
