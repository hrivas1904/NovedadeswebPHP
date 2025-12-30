$(document).ready(function () {
    if ($("#tb_configuracion").length > 0) {
        $("#tb_configuracion").DataTable({
            ajax: {
                url: "/novedades/data",
                type: "GET",
                error: function (e) {
                    console.error("Error al cargar datos:", e.responseText);
                },
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            order: [[2, "asc"]],
            info: true,
            paginate: {
                first: "",
                last: "",
                next: "",
                previous: "",
            },
            columns: [
                { data: "CODIGO_NOVEDAD" },
                { data: "NOVEDAD" },
                { data: "CATEG" },
                { data: "TIPO_VALOR"},
                {
                    data: "CODIGO_NOVEDAD",
                    render: function (data) {
                        return `
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${data}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                },
            ],
            dom:
                "<'d-flex justify-content-between align-items-center mb-3'lfB>" +
                "rt" +
                "<'d-flex justify-content-between'p>",
            buttons: [
                {
                    extend: "excelHtml5",
                    className: "btn-export-excel",
                    text: "Excel",
                },
                {
                    extend: "pdfHtml5",
                    className: "btn-export-pdf",
                    text: "PDF",
                },
                { extend: "print", className: "btn-printer", text: "Imprimir" },
            ],
            scrollX: true,
        });
    }
});
