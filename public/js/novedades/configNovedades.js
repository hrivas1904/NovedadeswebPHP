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
                lengthMenu: "_MENU_",
                paginate: {
                    first: "<<",
                    previous: "<",
                    next: ">",
                    last: ">>",
                },
            },
            order: [[2, "asc"]],
            info: true,
            columns: [
                { data: "CODIGO_NOVEDAD" },
                { data: "NOVEDAD" },
                { data: "CATEG" },
                { data: "TIPO_VALOR"},
                {
                    data: "CODIGO_NOVEDAD",
                    render: function (data) {
                        return `
                            <button type="button" class="btn-alerta btn-edit" data-id="${data}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                },
            ],
            dom: "<'d-top d-flex align-items-center gap-2'lB<'d-flex ms-auto'f>><'my-2'rt><'d-bottom d-flex align-items-center justify-content-between'ip>",
            buttons: [
                {
                    extend: "excelHtml5",
                    className: "btn-export-excel",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel', 
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf",
                },
                { extend: "print", 
                  className: "btn-printer",
                  text: '<i class="fa-solid fa-print"></i> Imprimir' 
                },
            ],
            scrollX: true,
        });
    }
});
