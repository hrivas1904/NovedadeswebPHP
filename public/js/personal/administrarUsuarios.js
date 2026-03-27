$(document).ready(function () {
    cargarAreas();
});

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "60vh";
}

function cargarAreas() {
    $.ajax({
        url: "/areas/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-area");

            select.empty();
            select.append('<option value="">Seleccione área</option>');

            data.forEach((area) => {
                select.append(`
                    <option value="${area.id_area}">
                        ${area.nombre}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando áreas");
        },
    });
}

$("#btnLimpiarFiltros").on("click", function () {
    $("#filtroArea").val("").trigger("change");
    $("#filtroEstado").val("").trigger("change");

    tabla.ajax.reload();
});

$(document).ready(function () {
    let tabla = $("#tb_usuarios").DataTable({
        processing: true,
        ajax: {
            url: "/usuarios/listar",
            data: function (d) {
                d.area = $("#filtroArea").val() || null;
                d.estado = $("#filtroEstado").val() || null;
            },
        },
        autoWidth: false,
        scrollX: false,
        paging: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        responsive: true,
        columns: [
            { data: "legajo" },
            { data: "colaborador" },
            { data: "area" },
            { data: "username" },
            { data: "fecha_alta" },
            { data: "fecha_baja" },
            {
                data: "estado",
                render: function (data) {
                    if (data === "ACTIVO") {
                        return `<span class="badge bg-success">ACTIVO</span>`;
                    } else {
                        return `<span class="badge bg-danger">INACTIVO</span>`;
                    }
                },
            },
            {
                data: null,
                orderable: false,
                className:"text-center",
                render: function (data) {
                    if (
                        data.estado === "DE BAJA" ||
                        data.estado === "INACTIVO"
                    ) {
                        return "";
                    }
                    return `
            <button class="btn btn-sm btn-primary btnEditar" data-id="${data.legajo}">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.legajo}">
                <i class="fa fa-user-slash"></i>
            </button>
        `;
                },
            },
        ],
        dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2' \
                    <'d-flex flex-column flex-sm-row gap-2'B> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="fa-solid fa-file-excel"></i> Excel',
                className: "btn-export-excel dt-buttons",
                exportOptions: { columns: ":visible" },
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                className: "btn-export-pdf dt-buttons",
                exportOptions: { columns: ":visible" },
            },
            {
                extend: "print",
                text: '<i class="fa-solid fa-print"></i> Imprimir',
                title: "Nómina de personal dt-buttons",
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                className: "btn-printer",
            },
        ],
    });

    $("#filtroArea, #filtroEstado").on("change", function () {
        tabla.ajax.reload();
    });
});

// limpiar form al abrir
$("#modalNuevoUsuario").on("shown.bs.modal", function () {
    $("#formNuevoUsuario")[0].reset();
});

// guardar usuario
$("#btnGuardarUsuario").on("click", function () {
    let data = {
        name: $("#name").val(),
        legajo: $("#legajo").val(),
        username: $("#username").val(),
        password: $("#password").val(),
        rol: $("#rol").val(),
        area: $("#area").val(),
    };

    // validación
    if (
        !data.name ||
        !data.legajo ||
        !data.password ||
        !data.rol ||
        !data.area
    ) {
        Swal.fire("Error", "Completá los campos obligatorios", "warning");
        return;
    }

    $.ajax({
        url: "/usuarios/crear",
        type: "POST",
        data: data,
        success: function (resp) {
            if (!resp.ok) {
                Swal.fire("Error", resp.mensaje, "warning");
                return;
            }

            Swal.fire("OK", resp.mensaje, "success");

            $("#modalNuevoUsuario").modal("hide");

            $("#formNuevoUsuario")[0].reset(); // 🔥 importante

            $("#tb_usuarios").DataTable().ajax.reload();
        },
        error: function (xhr) {
            let msg = "Error en la operación";

            if (xhr.responseJSON?.mensaje) {
                msg = xhr.responseJSON.mensaje;
            }

            Swal.fire("Error", msg, "error");
        },
    });
});

$(document).on("click", ".btnEditar", function () {
    let legajo = $(this).data("id");

    $.get(`/usuarios/obtener/${legajo}`, function (data) {
        $("#edit_name").val(data.name);
        $("#edit_legajo").val(data.legajo);
        $("#edit_username").val(data.username);
        $("#edit_password").val("");
        $("#edit_rol").val(data.rol);
        $("#edit_area").val(data.area_id).trigger("change");
        $("#modalEditarUsuario").modal("show");
    });
});

$("#btnActualizarUsuario").on("click", function () {
    let data = {
        name: $("#edit_name").val(),
        legajo: $("#edit_legajo").val(),
        username: $("#edit_username").val(),
        password: $("#edit_password").val(),
        rol: $("#edit_rol").val(),
        area: $("#edit_area").val(),
    };

    if (!data.name || !data.legajo || !data.rol || !data.area) {
        Swal.fire("Error", "Completá los campos obligatorios", "warning");
        return;
    }

    $.ajax({
        url: "/usuarios/actualizar",
        type: "POST",
        data: data,
        success: function (resp) {
            if (!resp.ok) {
                Swal.fire("Error", resp.mensaje, "warning");
                return;
            }

            Swal.fire("OK", resp.mensaje, "success");

            $("#modalEditarUsuario").modal("hide");

            $("#tb_usuarios").DataTable().ajax.reload();
        },
        error: function () {
            Swal.fire("Error", "Error al actualizar", "error");
        },
    });
});

$(document).on("click", ".btnEliminar", function () {
    let legajo = $(this).data("id");

    Swal.fire({
        title: "¿Dar de baja usuario?",
        text: "El usuario será deshabilitado",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, dar de baja",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/usuarios/baja",
                type: "POST",
                data: { legajo: legajo },
                success: function (resp) {
                    if (!resp.ok) {
                        Swal.fire("Error", resp.mensaje, "warning");
                        return;
                    }

                    Swal.fire("OK", resp.mensaje, "success");

                    $("#tb_usuarios").DataTable().ajax.reload();
                },
                error: function () {
                    Swal.fire("Error", "No se pudo dar de baja", "error");
                },
            });
        }
    });
});
