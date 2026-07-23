function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "50vh";
}

$(document).ready(function () {
    cargarTipoEncuestas();
});

function cargarTipoEncuestas() {
    $.ajax({
        url: "/calidad/encuestas/tipos",
        method: "GET",
        success: function (data) {
            const select = $("#selectTipoEncuesta");
            select.empty();

            select.append(
                '<option value="">Seleccione tipo de encuesta</option>',
            );

            data.forEach((tipo) => {
                select.append(`
                    <option value="${tipo.id}">
                        ${tipo.text}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando tipos de encuestas");
        },
    });
}

$("#btnImportar").click(function () {
    let file = $("#excelFile")[0].files[0];
    let tipo = $("#selectTipoEncuesta").val();

    if (!file) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un archivo",
            customClass: {
                confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
        });
        return;
    }

    if (!tipo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un tipo de encuesta",
            customClass: {
                confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
        });
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);
    formData.append("idTipoEncuesta", tipo);
    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    console.log("Enviando tipo:", tipo);

    Swal.fire({
        title: "Procesando archivo...",
        text: "Leyendo Excel",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "/calidad/encuestas/importar",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function (res) {
            console.log("PREPROCESAMIENTO OK:", res);

            renderExcelPreview(res.headers, res.rows);

            Swal.fire({
                icon: "success",
                title: "Archivo procesado",
                text: "Preprocesamiento correcto",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },

        error: function (err) {
            console.log("STATUS:", err.status);
            console.log("RESPONSE:", err.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.responseJSON?.mensaje ?? "Error procesando archivo",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });
});

function renderExcelPreview(headers, rows) {
    if ($.fn.DataTable.isDataTable("#tablaExcel")) {
        $("#tablaExcel").DataTable().destroy();
    }

    let columns = headers.map((h) => ({
        title: h,
    }));

    $("#tablaExcel").DataTable({
        data: rows,
        columns: columns,
        pageLength: 10,
        destroy: true,
        scrollX: true,
        autoWidth: true,
        paging: false,
        searching: false,
        scrollCollapse: true,
        scrollY: getScrollY(),
        language: {
            url: "/js/es-ES.json",
        },
    });
}

$("#btnAnalizar").click(function (e) {
    e.preventDefault();
    e.stopPropagation();

    let file = $("#excelFile")[0].files[0];
    let tipo = $("#selectTipoEncuesta").val();

    if (!file) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un archivo",
            customClass: {
                confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
        });
        return;
    }

    if (!tipo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un tipo de encuesta",
            customClass: {
                confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
        });
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);
    formData.append("idTipoEncuesta", tipo);
    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    Swal.fire({
        title: "Procesando...",
        text: "Guardando datos en el sistema",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: "/calidad/encuestas/procesar",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            console.log("PROCESADO OK:", res);

            Swal.fire({
                icon: "success",
                title: "Datos procesados",
                text: `Se insertaron ${res.registrosInsertados} registros`,
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            }).then(() => {
                window.location.href = "/calidad/dashboardCalidad";
            });
        },
        error: function (err) {
            console.log("ERROR COMPLETO:", err);
            console.log("RESPONSE:", err.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.responseJSON?.detalle ?? "Error procesando archivo",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });
});

function renderResultados(data) {
    if ($.fn.DataTable.isDataTable("#tablaResultados")) {
        $("#tablaResultados").DataTable().destroy();
    }

    $("#tablaResultados").DataTable({
        data: data,
        columns: [
            { title: "Pregunta", data: "texto" },
            { title: "Positivas", data: "positivas" },
            { title: "Negativas", data: "negativas" },
            { title: "No Aplica", data: "no_aplica" },
            { title: "% Positivas", data: "porc_positivas" },
        ],
    });
}

function cargarResultados(idImportacion) {
    $.ajax({
        url: `/calidad/encuestas/resultados/${idImportacion}`,
        method: "GET",
        success: function (data) {
            console.log("RESULTADOS:", data);
            renderResultados(data.resultados);
        },
    });
}
