function cargarLinksImportacion() {
    $.ajax({
        url: "/administracion/links/obtenerLinksImportacion",
        type: "GET",
        dataType: "JSON",
        dataSrc: "data",
        data: { p_estado: estado },
        success: function (res) {
            let html = null;
            $.each(res, function (index, item) {
                html += `

                `;
            });
        },
    });
}

$(document).ready(function () {
    cargarLinksImportacion();
});
