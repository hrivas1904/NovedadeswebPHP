let tablaMedicos = null;

$(document).ready(function () {
    tablaMedicos = new DataTable("#tbMedicos", {
        ajax: {
            url: "/rrhh/medicos/obtenerMedicos",
            type: "GET",
            dataSrc: "",
        },
        columns: [
            { data: "id", className: "text-start", visible: false },
            { data: "medico", className: "text-start" },
            { data: "matricula", className: "text-start" },
            { data: "dni", className: "text-start" },
            { data: "especialidad", className: "text-start" },
            { data: "telefono", className: "text-start" },
        ],
        language: {
            url: "/js/es-ES.json",
        },
        paging: false,
        scrollX: false,
        scrollY: "58vh",
        scrollCollapse: true,
        autoWidth: true,
        dom: "tir",
    });

    $("#tbMedicos tbody").on("click", "tr", function () {
        alert("Hiciste click");
    });
});

function verDetalleMedico(idMedico) {
    $.ajax({
        url: "",
        dataSrc: "",
        data: function (d) {},
    });
}
