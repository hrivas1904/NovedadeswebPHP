$(document).ready(function(){
    cargarAreasServicios();
})

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
            { data: "nombre" },
            { data: "id_area", visible: false },
            { data: "nombre" },
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
                    `<option value="${area.id_area}">${area.nombre}</option>`
                );
            });
            select.select2({
                placeholder: "-- Seleccioná un área --",
                allowClear: true,
            });
        },
    });
}