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

function renderExcelPreview(headers, rows){

    let table = $("#tablaExcel");
    table.empty();

    // HEAD
    let thead = "<thead><tr>";
    headers.forEach(h=>{
        thead += `<th>${h}</th>`;
    });
    thead += "</tr></thead>";

    // BODY
    let tbody = "<tbody>";

    rows.forEach(r=>{
        tbody += "<tr>";
        r.forEach(c=>{
            tbody += `<td>${c ?? ''}</td>`;
        });
        tbody += "</tr>";
    });

    tbody += "</tbody>";

    table.append(thead + tbody);
}

//importar excel
$("#btnImportar").click(function () {

    let file = $("#excelFile")[0].files[0];

    if (!file) {
        alert("Seleccione un Excel");
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);
    formData.append("_token", $('meta[name="csrf-token"]').attr('content'));


    $.ajax({
        url: "/encuestas/importar",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {

            console.log("EXCEL IMPORTADO:", res);
            renderExcelPreview(res.headers, res.rows);
            alert("Excel leído correctamente");
        },
        error: function (err) {
            console.error(err);
            alert("Error leyendo excel");
        }
    });

});


