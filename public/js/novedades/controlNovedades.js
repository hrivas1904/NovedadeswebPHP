//sp para cargar selector areas
$(document).ready(function () {
    cargarAreas();
});

function cargarAreas() {
    $.ajax({
        url: "/areas/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-area");
            const areaFija = $("#areaFija").val(); // puede ser undefined

            select.empty();
            select.append('<option value="">Seleccione área</option>');

            data.forEach((area) => {
                select.append(`
                    <option value="${area.id_area}">
                        ${area.nombre}
                    </option>
                `);
            });

            // 👉 LÓGICA CORRECTA
            if (areaFija) {
                select.val(areaFija);
                select.prop("disabled", true);
            } else {
                select.prop("disabled", false);
            }
        },
        error: function () {
            console.error("Error cargando áreas");
        },
    });
}

//carga dt personal
$(document).ready(function () {
    tablaPersonal = $("#tb_control");

    if (tablaPersonal.length > 0) {
        let dt = new DataTable("#tb_control", {
            ajax: {
                url: "/novedades/listarNovedadesPorArea",
                type: "GET",
                data: function (d) {
                    d.area_id = $("#area").val();
                },
            },
            scrollX: true,
            paging: true,
            paginate: {
                first: "",
                last: "",
                next: "",
                previous: "",
            },
            columnDefs: [
                {
                    targets: [6, 12, 13, 14, 15],
                    visible: false,
                    searchable: false,
                },
            ],
            columns: [
                { data: "REGISTRO" },
                { data: "FECHA_REGISTRO" },
                { data: "AREA" },
                { data: "COLABORADOR" },
                {
                    data: "LEGAJO",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "CODIGO_NOVEDAD" },
                { data: "CATEGORIA" },
                { data: "NOVEDAD_NOMBRE" },
                { data: "FECHA_DESDE" },
                { data: "FECHA_HASTA" },
                { data: "FECHA_APLICACION" },
                { data: "DURACION" },
                { data: "VALOR2" },
                { data: "CENTRO_COSTO" },
                { data: "REGISTRANTE" },
                { data: "DESCRIPCION" },
            ],
            scrollX: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            paging: false,
            autoWidth: false,
            dom: "<'dt-top d-flex align-items-center justify-content-between'Bf>rt<'dt-bottom'p>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    className: "btn-export-excel",
                    title: "",
                    exportOptions: {
                        columns: [4, 5, 13, 11, 12, 10, 8, 9, 15],
                        format: {
                            header: function (data, columnIdx) {
                                const headersMap = {
                                    4: "LEGAJO",
                                    5: "NOVEDAD",
                                    13: "CENTROCOSTO",
                                    11: "VALOR1",
                                    12: "VALOR2",
                                    10: "FECHAAPLICACION",
                                    8: "FECHADESDE",
                                    9: "FECHAHASTA",
                                    15: "DESCRIPCION",
                                };
                                return headersMap.hasOwnProperty(columnIdx)
                                    ? headersMap[columnIdx]
                                    : data;
                            },
                        },
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets["sheet1.xml"];
                    },
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "print",
                    text: '<i class="fa-solid fa-print"></i> Imprimir',
                    title: "Productos en sucursal",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });

        $("#area").on("change", function () {
            dt.ajax.reload();
        });

        setTimeout(function () {
            if ($("#area").val() !== "" && $("#area").val() !== null) {
                dt.ajax.reload();
            }
        }, 500);

        $(document).on("click", ".btn-VerLegajo", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            const nombre = $(this).data("nombre");
            console.log("Legajo recibido: " + legajoColaborador);
            verLegajo(legajoColaborador, nombre);
        });

        $(document).on("click", ".btn-DarBaja", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            console.log("Id recibido: " + legajoColaborador);
            darDeBaja(legajoColaborador);
        });
    }

    
});
