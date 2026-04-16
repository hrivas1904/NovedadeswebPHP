let tablaControl;

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "55vh";
}

function formatearFechaArgentina(fecha) {
    if (!fecha) return "";
    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function formatearPesos(valor) {
    if (valor === null || valor === undefined || valor === "") return "";
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    }).format(valor);
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
            select.trigger("change");
        },
        error: function (xhr) {
            console.error("Error AJAX:", xhr.responseText);
            alert("Error cargando novedades");
        },
    });
}

function cerrarModalDetalleNovedad() {
    $("#formDetalleNovedad")[0].reset();
    $("#btnGuardarCambios").addClass("d-none");
    $("#btnHabilitarEdicion").removeClass("d-none");
    $("#modalDetalleNovedad").modal("hide");
}

$(document).ready(function () {
    cargarFiltroNovedad();
});

$("#btnLimpiarFiltros").on("click", function () {
    $("#filtroDesde").val(null);
    $("#filtroHasta").val(null);
    $("#idNovedad").val(null);
    $("#liquidada").val(0);
    tablaControl.ajax.reload();
});

$("#btnAplicarFiltros").on("click", function () {
    tablaControl.ajax.reload();
});

//carga dt novedades
$(document).ready(function () {
    tablaControl = $("#tb_control");
    if (tablaControl.length > 0) {
        tablaControl = new DataTable("#tb_control", {
            ajax: {
                url: "/novedades/listarNovedadesPorArea",
                type: "GET",
                data: function (d) {
                    d.idNovedad = $("#idNovedad").val();
                    d.desde = $("#filtroDesde").val();
                    d.hasta = $("#filtroHasta").val();
                    d.liquidada = $("#liquidada").val();
                },
            },
            order: [[0, "desc"]],
            columns: [
                { data: "REGISTRO" },
                {
                    data: "FECHA_REGISTRO",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                { data: "REGISTRANTE" },
                {
                    data: "CODIGO_NOVEDAD",
                    width: "3%",
                    className: "text-start",
                    title: "NOVEDAD",
                },
                { data: "NOVEDAD_NOMBRE" },
                {
                    data: "FECHA_DESDE",
                    className: "text-end",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },
                {
                    data: "FECHA_HASTA",
                    className: "text-end",
                    render: function (data) {
                        return formatearFechaArgentina(data);
                    },
                },                
                {
                    data: "DURACION",
                    render: function (data, type, row) {
                        tipoValorNov = row.TIPO_VALOR;

                        if (tipoValorNov === "Pesos") {
                            return formatearPesos(data);
                        } else {
                            return parseInt(data);
                        }
                        return data;
                    },
                },
                { data: "DESCRIPCION" },
                {
                    data: "REGISTRO",
                    orderable: false,
                    width: "10%",
                    render: function (data, type, row) {
                        if (USER_ROLE === "Colaborador/a") {
                            return `
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" 
                                        class="btn btn-secondary btn-VerDetalleNovedad" 
                                        data-id="${data}" 
                                        title="Detalle de novedad">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" 
                                        class="btn btn-secondary btn-VerDetalleNovedad" 
                                        data-id="${data}" 
                                        title="Detalle de novedad">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button type="button" 
                                        class="btn btn-danger btn-AnularNovedad" 
                                        data-id="${data}" 
                                        title="Anular novedad">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                },
            ],
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: getScrollY(),
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
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'B> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    className: "btn-export-excel dt-buttons",
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
                                    5, 6, 8, 9, 10, 11, 13, 14, 16, 20, 21,
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
                                "VALOR",
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

                        // =========================
                        // ALINEAR A LA DERECHA
                        // =========================
                        var styles = xlsx.xl["styles.xml"];
                        var cellXfs = $("cellXfs xf", styles);

                        // Crear estilo alineado a la derecha
                        cellXfs
                            .last()
                            .after(
                                '<xf xfId="0" applyAlignment="1"><alignment horizontal="right"/></xf>',
                            );

                        var rightAlignIndex = cellXfs.length;

                        // Columnas a alinear (post-export)
                        let columnasDerecha = esLicenciaAnual()
                            ? [1, 2, 3] // ajustá si querés
                            : [0, 3, 4, 5, 6, 7]; // ej: legajo + fechas + valores

                        $("row c", sheet).each(function () {
                            var cell = $(this);
                            var cellRef = cell.attr("r");
                            var colLetter = cellRef.replace(/[0-9]/g, "");

                            // Soporte AA, AB, etc
                            var colIndex = 0;
                            for (var i = 0; i < colLetter.length; i++) {
                                colIndex *= 26;
                                colIndex += colLetter.charCodeAt(i) - 64;
                            }
                            colIndex--;

                            if (columnasDerecha.includes(colIndex)) {
                                cell.attr("s", rightAlignIndex);
                            }
                        });

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
                                  "NOVEDAD",
                                  "CENTROCOSTO",
                                  "VALOR1",
                                  "VALOR2",
                                  "FECHAAPLICACION",
                                  "FECHADESDE",
                                  "FECHAHASTA",
                                  "DESCRIPCION",
                                  "COLABORADOR",
                                  "CONCEPTO_NOVEDAD",
                              ];

                        row.each(function (i) {
                            $("is t", this).text(headers[i]);
                        });
                    },
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf dt-buttons",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "print",
                    text: '<i class="fa-solid fa-print"></i> Imprimir',
                    title: "Registro de novedades",
                    orientation: "landscape",
                    pageSize: "A4",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer dt-buttons",
                },
            ],
        });

        $("#idNovedad").on("change", function () {
            tablaControl.ajax.reload();
        });

        $("#liquidada").on("change", function () {
            console.log($(this).val())
            tablaControl.ajax.reload();
        });

        $(document).on("click", ".btn-VerDetalleNovedad", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const idRegistro = $(this).data("id");
            console.log("Id registro recibido: " + idRegistro);
            verDetalleNovedad(idRegistro);
        });
    }
});

function verDetalleNovedad(idRegistro) {
    $.ajax({
        url: `/novedades/verDetalleRegistroNovedad/${idRegistro}`,
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const d = response.data;

                $("#inputRegistro").val(d.id_nxe);
                $("#inputFechaReg").val(
                    formatearFechaArgentina(d.fecha_registro),
                );
                $("#inputRegistrante").val(d.registrante);

                $("#inputLegajo").val(d.legajo);
                $("#inputColaborador").val(d.colaborador);
                $("#inputEstado").val(d.estado);
                $("#inputDni").val(d.dni);
                $("#inputArea").val(d.area);

                $("#inputFechaAplicacion").val(
                    formatearFechaArgentina(d.FECHA_APLICACION),
                );
                $("#inputFechaDesde").val(d.FECHA_DESDE);
                $("#inputFechaHasta").val(d.FECHA_HASTA);

                let valor = d.DURACION;
                let valorFormateado = 0;
                let tipo_valor = d.tipo_valor;
                $("#tipoValor").val(tipo_valor);

                if (tipo_valor === "Pesos") {
                    valorFormateado = formatearPesos(valor);
                    $("#inputValor").val(valorFormateado);
                    $("#inputValor").addClass("text-end");
                } else {
                    $("#inputValor").val(parseInt(valor));
                    $("#inputValor").addClass("text-start");
                }

                if (tipo_valor === "Horas") {
                    calcularHorasHabiles();
                }

                $("#inputCodigo").val(d.codigo_novedad);
                $("#inputNovedad").val(d.novedad);
                $("#inputDescripcion").val(d.DESCRIPCION);

                $("#inputTipoVacaciones").val(d.TIPO);
                $("#inputAnnioVacaciones").val(d.ANNIO);

                if (d.novedad === "Licencia anual") {
                    $("#divInfoVacaciones").removeClass("d-none");
                } else {
                    $("#divInfoVacaciones").addClass("d-none");
                }

                let importeCuotas = d.importeCuotas;
                let importeCuotasFormateado = formatearPesos(importeCuotas);

                $("#inputNumAtencion").val(d.NUM_ATENCION);
                $("#inputPacienteAtencion").val(d.PACIENTE);
                $("#inputConcepto").val(d.CONCEPTO);
                $("#inputCuotas").val(d.CUOTAS);
                $("#inputImporteCuotas")
                    .val(importeCuotasFormateado)
                    .addClass("text-end");

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