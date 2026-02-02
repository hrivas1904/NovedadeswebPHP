function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

//sp para cargar selector areas
$(document).ready(function () {
    cargarAreas();
    cargarFiltroNovedad();
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

function cargarFiltroNovedad() {
    const select = $(".js-select-novedadFiltro");

    select.prop("disabled", true);

    $.ajax({
        url: "/novedades/lista",
        type: "GET",
        dataType: "json",

        success: function (data) {
            console.log("Novedades:", data);

            select.empty();

            select.append(
                $("<option>", {
                    value: "",
                    text: "Seleccione novedad",
                }),
            );

            data.forEach((novedad) => {
                select.append(
                    $("<option>", {
                        value: novedad.ID_NOVEDAD,
                        text: novedad.NOMBRE,
                    }),
                );
            });

            select.prop("disabled", false);

            // Si usás Select2
            select.trigger("change");
        },

        error: function (xhr) {
            console.error("Error AJAX:", xhr.responseText);

            alert("Error cargando novedades");
        },
    });
}
function esLicenciaAnual() {
    return $("#idNovedad").val() == "3";
}

//carga dt novedades
$(document).ready(function () {
    tablaPersonal = $("#tb_control");

    if (tablaPersonal.length > 0) {
        let dt = new DataTable("#tb_control", {
            ajax: {
                url: "/novedades/listarNovedadesPorArea",
                type: "GET",
                data: function (d) {
                    if (USER_ROLE !== "Administrador/a") {
                        d.area_id = USER_AREA_ID;
                    } else {
                        d.area_id = $("#area").val();
                    }
                    d.idNovedad = $("#idNovedad").val();
                },
            },
            columnDefs: [
                {
                    targets: [0, 6, 5, 10, 12, 13, 15, 16, 17],
                    visible: false,
                    searchable: false,
                },
            ],
            order: [[0, "desc"]],
            columns: [
                { data: "REGISTRO" },
                {
                    data: "FECHA_REGISTRO",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                { data: "AREA" },
                { data: "REGISTRANTE" },
                { data: "COLABORADOR" },
                {
                    data: "LEGAJO",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                {
                    data: "CODIGO_NOVEDAD",
                    width: "3%",
                    className: "text-start",
                },
                { data: "NOVEDAD_NOMBRE" },
                {
                    data: "FECHA_DESDE",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                {
                    data: "FECHA_HASTA",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                {
                    data: "FECHA_APLICACION",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                { data: "DURACION" },
                { data: "VALOR2" },
                { data: "CENTRO_COSTO" },
                { data: "DESCRIPCION" },
                { data: "EMPRESA" },
                { data: "ANNIO" },
                { data: "TIPO" },
                {
                    data: "REGISTRO",
                    render: function (data, type, row) {
                        // 'data' es el valor de la celda (REGISTRO)
                        // 'row' contiene todo el objeto del empleado/evento
                        return `
                            <div class="d-flex align-items-center justify-content-between">
                                <button type="button" 
                                    class="btn btn-sm btn-alerta btn-editar-evento" 
                                    data-id="${row.id}" 
                                    title="Editar Registro">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        `;
                    },
                },
            ],
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: "65vh",
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
            autoWidth: false,
            dom: "<'d-top d-flex align-items-center gap-2'B<'d-flex ms-auto'f>><'my-2'rt><'d-bottom d-flex align-items-center justify-content-center'i>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    className: "btn-export-excel",
                    title: "",

                    exportOptions: {
                        columns: function (idx, data, node) {
                            if (esLicenciaAnual()) {
                                return [15, 8, 9, 11, 16, 5, 17].includes(idx);
                            } else {
                                return [
                                    5, 6, 13, 11, 12, 10, 8, 9, 14,
                                ].includes(idx);
                            }
                        },
                    },

                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets["sheet1.xml"];
                        var row = $("row:first c", sheet);

                        let headers;

                        if (esLicenciaAnual()) {
                            headers = [
                                "EMPRESA",
                                "FECHADESDE",
                                "FECHAHASTA",
                                "CANTIDADDIAS",
                                "AÑO",
                                "LEGAJO",
                                "TIPO",
                            ];
                        } else {
                            headers = [
                                "LEGAJO",
                                "CODIGO",
                                "CENTROCOSTO",
                                "VALOR1",
                                "VALOR2",
                                "FECHAAPLICACION",
                                "FECHADESDE",
                                "FECHAHASTA",
                                "DESCRIPCION",
                            ];
                        }

                        row.each(function (index) {
                            if (headers[index]) {
                                $("is t", this).text(headers[index]);
                            }
                        });
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
                    title: "Registro de novedades",
                    orientation: "landscape",
                    pageSize: "A4",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });

        $("#area").on("change", function () {
            dt.ajax.reload();
        });

        $("#idNovedad").on("change", function () {
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
