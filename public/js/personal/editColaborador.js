$(document).ready(function () {
    cargarSelectAreasEdit();
});

$(document).ready(function () {
    const $select = $("#selectObraSocialEdit");

    $.ajax({
        url: "/obra-social/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.select2({
                data: data,
                placeholder: "Buscar obra social...",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#modalLegajoColaborador"),
            });
        },
        error: function (err) {
            console.error("Error cargando obras sociales:", err);
        },
    });
});

$("#selectObraSocialEdit").on("select2:select", function (e) {
    const data = e.params.data;
    $("#inputCodigoOS").val(data.codigo ?? "");
});

$("#selectObraSocialEdit").on("select2:clear", function () {
    $("#inputCodigoOS").val(data.codigo ?? "");
});

function cargarSelectAreasEdit() {
    const $select = $("#inputArea");

    $select.empty().append('<option value="">Cargando...</option>');

    $.ajax({
        url: "/areas/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.empty().append('<option value="">Seleccione área</option>');

            data.forEach((area) => {
                $select.append(`
                    <option value="${area.id_area}">
                        ${area.nombre}
                    </option>
                `);
            });
        },
        error: function (err) {
            console.error("Error cargando áreas:", err);
            $select.html('<option value="">Error al cargar</option>');
        },
    });
}

function cargarServiciosSimple(selected = null) {
    const idArea = $("#inputArea").val();
    const $select = $("#inputServicio");

    $select.empty().append('<option value="">Cargando...</option>');

    if (!idArea) {
        $select.html('<option value="">Seleccione servicio</option>');
        return;
    }

    $.ajax({
        url: `/servicios-empleados/por-area/${idArea}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select
                .empty()
                .append('<option value="">Seleccione servicio</option>');

            data.forEach((servicio) => {
                $select.append(`
                    <option value="${servicio.id_servicios}">
                        ${servicio.servicio}
                    </option>
                `);
            });

            // 🔥 marcar seleccionado
            if (selected) {
                $select.val(selected).trigger("change");
            }
        },
        error: function () {
            $select.html("<option>Error al cargar</option>");
        },
    });
}

function cargarRolesSimple(selected = null) {
    const idCategoria = $("#inputCategoria").val();
    const $select = $("#inputRol");

    $select.empty().append('<option value="">Cargando...</option>');

    if (!idCategoria) {
        $select.html('<option value="">Seleccione rol</option>');
        return;
    }

    $.ajax({
        url: `/roles-empleados/por-categoria/${idCategoria}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.empty().append('<option value="">Seleccione rol</option>');

            data.forEach((rol) => {
                $select.append(`
                    <option value="${rol.id_rol}">
                        ${rol.nombre}
                    </option>
                `);
            });

            // 🔥 marcar seleccionado
            if (selected) {
                $select.val(selected).trigger("change");
            }
        },
        error: function () {
            $select.html("<option>Error al cargar</option>");
        },
    });
}

$("#formEditColaborador").on("submit", function (e) {
    e.preventDefault();
    const legajo = $("#inputLegajo").val();
    const data = $(this).serialize();
    console.log("DATA:", data);
    $.ajax({
        url: `/personal/${legajo}/actualizar`,
        type: "POST",
        data: data,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            if (resp.success) {
                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    text: resp.mensaje,
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });
                $("#modalLegajoColaborador").modal("hide");
                $("#tb_personal").DataTable().ajax.reload(null, false);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: resp.mensaje,
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo actualizar el legajo",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
    });
});
