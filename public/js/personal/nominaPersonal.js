$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        new DataTable("#tb_personal", {
            scrollX: true, // Mejor para 15 columnas
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            lengthMenu: [5, 10, 25, 50],
            dom: "Blfrtip", // Agregada la 'l' para que funcione tu lengthMenu
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fas fa-file-excel"></i> Excel', // Requiere FontAwesome
                    className: "btn-export-excel",
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf",
                },
                {
                    extend: "print",
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: "btn-printer",
                },
            ],
        });
    }
});
