let tablaRegimen = $("#tbRegimenes");

$(document).ready(function () {
    cargarAreasServicios();
});

function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "50vh";
}

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

$(document).ready(function () {
    tablaRegimen.DataTable({
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
        scrollY: "230px",
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
});

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
