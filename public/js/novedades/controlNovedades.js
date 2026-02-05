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

function cerrarModalLegajo() {
    const modal = $("#modalDetalleNovedad");
    modal.modal("hide");
}

function verDetalleNovedad(idRegistro) {
    $.ajax({
        url: `/novedades/verDetalleNovedad/${idRegistro}`,
        type: "GET",
        dataType: "json",
        data: {idRegistro:idRegistro},
        success: function (response) {
            if (response.success) {
                const d = response.data;

                $("#inputRegistro").val(d.id_nxe);
                $("#inputFechaReg").val(d.fecha_registro);
                $("#inputRegistrante").val(d.registrante);

                $("#inputLegajo").val(d.legajo);
                $("#inputColaborador").val(d.colaborador);
                $("#inputEstado").val(d.estado);
                $("#inputDni").val(d.dni);
                $("#inputArea").val(d.area);

                $("#inputFechaAplicacion").val(d.FECHA_APLICACION);
                $("#inputFechaDesde").val(d.FECHA_DESDE);
                $("#inputFechaHasta").val(d.FECHA_HASTA);

                if (d.tipo_valor === "Pesos") {
                    $("#inputValor").val(formatearPesos(d.DURACION));
                } else {
                    $("#inputValor").val(d.DURACION);
                }

                $("#inputCodigo").val(d.codigo_novedad);
                $("#inputNovedad").val(d.novedad);
                $("#inputDescripcion").val(d.DESCRIPCION);

                $("#inputTipoVacaciones").val(d.TIPO);
                $("#inputAnnioVacaciones").val(d.ANNIO);

                $("#inputNumAtencion").val(d.NUM_ATENCION);
                $("#inputPacienteAtencion").val(d.PACIENTE);
                $("#inputConcepto").val(d.CONCEPTO);
                $("#inputCuotas").val(d.CUOTAS);
                $("#inputImporteCuotas").val(d.importeCuotas);

                console.log("Abriendo detalle del registro ", idRegistro);
                const modal = $("#modalDetalleNovedad");
                modal.modal("show");

            } else {
                alert("Error: " + response.mensaje);
            }
        },
        error: function (error) {
            console.error("Error en la petición:", error);
            alert("No se pudo conectar con el servidor");
        },
    });
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
                    targets: [5, 6, 8, 9, 10, 11, 12, 16, 17, 18, 19],
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
                { data: "AREA", width: "%4" },
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
                { data: "CENTRO_COSTO" },
                { data: "DURACION" },
                { data: "VALOR2" },
                {
                    data: "FECHA_APLICACION",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                { data: "EMPRESA" },
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
                { data: "DURACION" },
                { data: "DESCRIPCION" },
                { data: "ANNIO" },
                {
                    data: "LEGAJO",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "TIPO" },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        // 'data' es el valor de la celda (REGISTRO)
                        // 'row' contiene todo el objeto del empleado/evento
                        return `
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <button type="button" 
                                    class="btn-sm btn-secundario btn-VerDetalleNovedad" 
                                    data-id="${row.registro}" 
                                    title="Detalle de novedad">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" 
                                    class="btn-sm btn-alerta btn-editar-evento" 
                                    data-id="${row.registro}" 
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
                        modifier: { order: "index" },
                        orthogonal: "export",
                        columns: function (idx) {
                            if (esLicenciaAnual()) {
                                return [12, 13, 14, 15, 17, 18, 19].includes(
                                    idx,
                                );
                            } else {
                                return [
                                    5, 6, 8, 9, 10, 11, 13, 14, 16,
                                ].includes(idx);
                            }
                        },
                    },

                    customizeData: function (data) {
                        if (esLicenciaAnual()) {
                            // ORDEN QUE VOS QUERÉS
                            const orden = [
                                "EMPRESA",
                                "DESDE",
                                "HASTA",
                                "CANT",
                                "AÑO",
                                "LEGAJO",
                                "TIPO",
                            ];

                            // Índices actuales
                            const idxMap = orden.map((h) =>
                                data.header.indexOf(h),
                            );

                            // Reordenar headers
                            data.header = orden;

                            // Reordenar filas
                            data.body = data.body.map((row) =>
                                idxMap.map((i) => row[i]),
                            );
                        }
                    },

                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets["sheet1.xml"];
                        var row = $("row:first", sheet).find("c");

                        let headers = esLicenciaAnual()
                            ? [
                                  "EMPRESA",
                                  "FECHADESDE",
                                  "FECHAHASTA",
                                  "CANTIDADDIAS",
                                  "AÑO",
                                  "LEGAJO",
                                  "TIPO",
                              ]
                            : [
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

                        row.each(function (i) {
                            $("is t", this).text(headers[i]);
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

        $(document).on("click", ".btn-VerDetalleNovedad", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const idRegistro = $(this).data("id");
            console.log("Legajo recibido: " + idRegistro);
            verDetalleNovedad(idRegistro);
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
