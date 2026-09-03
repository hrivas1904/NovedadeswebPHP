let tablaRegimen = $("#tbRegimenes");

function cargarTablaRegimenes() {
    tablaRegimen= new DataTable("#tbRegimenes", {
        ajax: {
            url: "/rrhh/parametros/listarRegimenesColab",
            type: "GET",
            dataSrc: "",
        },

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

        order: [[0, "asc"]],

        columns: [
            // ID
            {
                data: "id",
                className: "text-start",
            },

            // RÉGIMEN
            {
                data: "regimen",
                className: "text-start",

                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }

                    return `
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control inputEditarRegimen"
                            data-id="${row.id}"
                            value="${data}">
                    `;
                },
            },

            // HORAS DIARIAS
            {
                data: "horasDiarias",
                className: "text-start",

                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }

                    return `
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control inputEditarHorasRegimen"
                            data-id="${row.id}"
                            value="${data}">
                    `;
                },
            },

            // ACTIVO
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
                                class="form-check-input switchActivoRegimen"
                                type="checkbox"
                                role="switch"
                                data-id="${row.id}"
                                ${checked}>
                        </div>
                    `;
                },
            },
        ],
    });
};

$("#btnCrearRegimen").on("click", function () {
    const regimen = $("#inputRegimen").val();
    const horasDiarias = $("#inputHorasDiarias").val();

    if (!regimen || !horasDiarias) {
        Swal.fire({
            icon: "warning",
            title: "Datos incompletos",
            text: "Debe ingresar el régimen y las horas diarias.",
        });

        return;
    }

    $.ajax({
        url: "/rrhh/parametros/crearRegimen",
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            regimen: regimen,
            horasDiarias: horasDiarias,
        },

        success: function () {
            $("#inputRegimen").val("");
            $("#inputHorasDiarias").val("");

            tablaRegimen.DataTable().ajax.reload(null, false);

            Swal.fire({
                icon: "success",
                title: "Régimen creado",
                timer: 1200,
                showConfirmButton: false,
            });
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.message ?? "No se pudo crear el régimen.",
            });
        },
    });
});

$(document).on("change", ".switchActivoRegimen", function () {
    const switchInput = $(this);

    const id = switchInput.data("id");
    const activo = switchInput.is(":checked") ? 1 : 0;

    $.ajax({
        url: "/rrhh/parametros/activarRegimen",
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            id: id,
            activo: activo,
        },

        error: function (xhr) {
            console.error(xhr.responseText);
            switchInput.prop("checked", !activo);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo modificar el estado del régimen.",
            });
        },
    });
});

$(document).on("change", ".inputEditarRegimen", function () {
    const id = $(this).data("id");
    const regimen = $(this).val();

    $.ajax({
        url: "/rrhh/parametros/editarRegimen",
        type: "PUT",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            id: id,
            regimen: regimen,
        },

        success: function () {
            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Régimen actualizado correctamente.",
                timer: 1200,
                showConfirmButton: false,
            });

            tablaRegimen.DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo modificar el régimen.",
            });
        },
    });
});

$(document).on("change", ".inputEditarHorasRegimen", function () {
    const id = $(this).data("id");
    const horasDiarias = $(this).val();

    $.ajax({
        url: "/rrhh/parametros/editarHorasRegimen",
        type: "PUT",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            id: id,
            horasDiarias: horasDiarias,
        },

        success: function () {
            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Horas diarias de régimen actualizado correctamente.",
                timer: 1200,
                showConfirmButton: false,
            });

            tablaRegimen.DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudieron modificar las horas diarias.",
            });
        },
    });
});