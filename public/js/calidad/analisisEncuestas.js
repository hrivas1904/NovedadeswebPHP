$(document).ready(function () {
    cargarTipoEncuestas();
});

function cargarTipoEncuestas() {
    $.ajax({
        url: "/encuestas/tipos",
        method: "GET",
        success: function (data) {

            const select = $("#selectTipoEncuesta");
            select.empty();

            select.append('<option value="">Seleccione tipo de encuesta</option>');

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
        }
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
        });
        return;
    }

    if (!tipo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un tipo de encuesta",
        });
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);
    formData.append("idTipoEncuesta", tipo);
    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    console.log("Enviando tipo:", tipo);

    $.ajax({
        url: "/encuestas/importar",
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
            });
        },
        error: function (err) {
            console.log("STATUS:", err.status);
            console.log("RESPONSE:", err.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.responseJSON?.mensaje ?? "Error procesando archivo",
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
    });
}

$("#btnAnalizar").click(function () {
    let file = $("#excelFile")[0].files[0];
    let tipo = $("#selectTipoEncuesta").val();

    if (!file) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un archivo",
        });
        return;
    }

    if (!tipo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un tipo de encuesta",
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
        url: "/encuestas/procesar",
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
            });

            // 🔥 IMPORTANTE: guardar idImportacion
            window.idImportacionActual = res.idImportacion;

            // 👉 siguiente paso: traer resultados
            cargarResultados(res.idImportacion);
        },
        error: function (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    err.responseJSON?.mensaje ??
                    "Error procesando archivo",
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
            { title: "% Positivas", data: "porc_positivas" }
        ]
    });
}

function cargarResultados(idImportacion) {
    $.ajax({
        url: `/encuestas/resultados/${idImportacion}`,
        method: "GET",
        success: function (data) {
            console.log("RESULTADOS:", data);
            renderResultados(data.resultados);
        },
    });
}