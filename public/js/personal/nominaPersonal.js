$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        new DataTable("#tb_personal", {
            scrollX: true, // Mejor para 15 columnas
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

function previewImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewFoto').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

//sp para cargar selectores
$(document).ready(function () {
    cargarAreas();
});

function cargarAreas() {
    $.ajax({
        url: '/areas/lista',
        type: 'GET',
        success: function (data) {

            let select = $('#area');
            select.empty();
            select.append('<option value="">Seleccione área</option>');

            data.forEach(area => {
                select.append(
                    `<option value="${area.id_area}">
                        ${area.nombre}
                    </option>`
                );
            });
        },
        error: function (err) {
            console.error('Error cargando áreas', err);
        }
    });
}

