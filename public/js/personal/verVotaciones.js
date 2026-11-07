let tablaVotaciones = null;
let tablaTop3 = null;

$(document).ready(function () {
    tablaVotaciones = new DataTable("#tbResultadosVotaciones", {
        ajax: {
            url: "/rrhh/obtenerRespuestas",
            type: "GET",
            dataType: "json",
            dataSrc: "data",
        },
        language: {
            url: "/js/es-ES.json",
        },
        columns: [
            { data: "id" },
            { data: "fecha_votacion" },
            { data: "votante" },
            { data: "grupo1_candidato" },
            { data: "grupo1_situacion" },
            { data: "grupo2_candidato" },
            { data: "grupo2_situacion" },
            { data: "grupo3_candidato" },
            { data: "grupo3_situacion" },
        ],
        scrollX: false,
        scrollY: "60vh",
        scrollCollapse: true,
        paging: false,
        buttons: [
            {
                extend: "excelHtml5",
                title: "Resultados - Reconocimiento entre Pares 2026",
                filename: "Resultados_Reconocimiento_Pares_2026",
                exportOptions: {
                    columns: ":visible",
                    modifier: {
                        search: "applied",
                        order: "applied",
                    },
                },
            },
        ],
    });
});

$(document).on("click", "#btnExportarResultados", function () {
    tablaVotaciones.button(".buttons-excel").trigger();
});

function verTop3xGrupo(){
    tablaTop3 = new DataTable("#tbTop3", {
        ajax: {
            url: "/rrhh/mejores3xGrupo",
            type: "GET",
            dataType: "json",
            dataSrc: "data",
        },
        language: {
            url: "/js/es-ES.json",
        },
        columns: [
            { data: "id_grupo" },
            { data: "grupo" },
            { data: "legajo" },
            { data: "colaborador" },
            { data: "cantidad_votos" },
            { data: "posicion" },
        ],
        scrollX: false,
        scrollY: "60vh",
        scrollCollapse: true,
        paging: false,
        dom: "tr",
        buttons: [
            {   
                extend: "excelHtml5",
                title: "Resultados - Reconocimiento entre Pares 2026",
                filename: "Resultados_Reconocimiento_Pares_2026",
                exportOptions: {
                    columns: ":visible",
                    modifier: {
                        search: "applied",
                        order: "applied",
                    },
                },
            },
        ],
    });

    $("#modalTop3xGrupo").modal("show");
}