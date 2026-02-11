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

            data.forEach((tipoEncuesta) => {
                select.append(`
                    <option value="${tipoEncuesta.id_encuesta}">
                        ${tipoEncuesta.nombre_encuesta}
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
            text: "Debe seleccionar un archivo"
        });
        return;
    }

    if (!tipo) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Debe seleccionar un tipo de encuesta"
        });
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);
    formData.append("idTipoEncuesta", tipo);
    formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

    console.log("Enviando tipo:", tipo);

    $.ajax({
        url: "/encuestas/procesar",
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
                text: "Preprocesamiento correcto"
            });
        },
        error: function (err) {

            console.log("STATUS:", err.status);
            console.log("RESPONSE:", err.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.responseJSON?.mensaje ?? "Error procesando archivo"
            });
        }
    });

});

function renderExcelPreview(headers, rows) {

    let table = $("#tablaExcel");
    table.empty();

    let thead = "<thead><tr>";
    headers.forEach((h) => {
        thead += `<th>${h}</th>`;
    });
    thead += "</tr></thead>";

    let tbody = "<tbody>";

    rows.forEach((r) => {
        tbody += "<tr>";
        r.forEach((c) => {
            tbody += `<td>${c ?? ""}</td>`;
        });
        tbody += "</tr>";
    });

    tbody += "</tbody>";

    table.append(thead + tbody);
}
