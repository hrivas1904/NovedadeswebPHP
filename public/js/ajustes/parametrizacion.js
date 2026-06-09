$(document).ready(function () {
    cargarAreasServicios();
});

$(document).ready(function () {
    new DataTable("#tb_areas", {
        ajax: {
            url: "/areas/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_area" }, { data: "nombre" }],

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
});

$(document).ready(function () {
    new DataTable("#tb_categ", {
        ajax: {
            url: "/categorias-empleados/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_categ" }, { data: "nombre" }],

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
});

$(document).ready(function () {
    new DataTable("#tb_servicios", {
        ajax: {
            url: "/servicios/listar",
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
});

function cargarAreasServicios() {
    $.ajax({
        url: "/areas/lista",
        type: "GET",
        success: function (data) {
            const select = $("#selectAreaServicios");
            select.empty();
            select.append('<option value="">-- Seleccioná un área --</option>');
            data.forEach(function (area) {
                select.append(
                    `<option value="${area.id_area}">${area.nombre}</option>`,
                );
            });
            select.select2({
                placeholder: "-- Seleccioná un área --",
                allowClear: true,
            });
        },
    });
}

$("#formNuevaArea").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: "/areas/crear",
        method: "POST",
        data: formData,

        beforeSend: function () {
            $("#btnCrearArea").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Guardando...
                `);
        },

        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Éxito",
                text: response.message,
            });

            $("#formNuevaArea")[0].reset();

            // Recargar listado
            cargarAreas();
        },

        error: function (xhr) {
            let mensaje = "Ocurrió un error.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje,
            });
        },

        complete: function () {
            $("#btnCrearArea").prop("disabled", false).html(`
                    <i class="fa-solid fa-plus me-1"></i>
                    Crear nueva área
                `);
        },
    });
});
