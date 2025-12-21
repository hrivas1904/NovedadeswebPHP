$(document).ready(function () {
    if ($("#tb_configuracion").length > 0) {
        new DataTable("#tb_configuracion", {
            scrollX: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            paging: false,
            scrollX: true,
            autoWidth: true,
            fixedColumns: true,
            paging: false,
            /*scrollY: 'var(--tabla-altura)',*/
            dom: "<'dt-top d-flex align-items-center justify-content-between'Bf>rt<'dt-bottom'p>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: "Exportar Excel",
                    filename: "Productos en sucursal",
                    title: "",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-export-excel",
                },
                {
                    extend: "pdfHtml5",
                    text: "Exportar PDF",
                    filename: "Productos en sucursal",
                    title: "Productos en sucursal",
                    pageSize: "A4",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-export-pdf",
                },
                {
                    extend: "print",
                    text: "Imprimir",
                    title: "Productos en sucursal",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ]
        });
    }
});
