let tablaControl;

function getScrollY() {
    return window.innerWidth < 768 ? "28vh" : "60vh";
}

flatpickr("#filtroDesde, #filtroHasta", {
    locale: "es",
    altInput: true,
    altFormat: "d/m/Y",
    dateFormat: "Y-m-d",
    onChange: function () {
        tablaControl.ajax.reload();
    },
});

function calcularHorasHabiles() {
    const fechaDesde = document.getElementById("inputFechaDesde").value;
    const fechaHasta = document.getElementById("inputFechaHasta").value;

    if (!fechaDesde || !fechaHasta) return;

    const inicio = new Date(fechaDesde + "T00:00:00");
    const fin = new Date(fechaHasta + "T00:00:00");

    if (fin < inicio) {
        document.getElementById("inputHoras").value = "";
        return;
    }

    let diasHabiles = 0;
    let fecha = new Date(inicio);

    while (fecha <= fin) {
        const diaSemana = fecha.getDay();

        // Cuenta lunes a viernes como día hábil
        if (diaSemana !== 0 && diaSemana !== 6) {
            diasHabiles++;
        }

        fecha.setDate(fecha.getDate() + 1);
    }

    const horas = diasHabiles * 8;
    document.getElementById("inputValor").value = horas;
}

function cerrarModalDetalleNovedad() {
    $("#formDetalleNovedad")[0].reset();
    $("#btnGuardarCambios").addClass("d-none");
    $("#btnHabilitarEdicion").removeClass("d-none");
    $("#modalDetalleNovedad").modal("hide");
}

//carga dt novedades
$(document).ready(function () {
    tablaControl = $("#tb_control");
    if (tablaControl.length > 0) {
        tablaControl = new DataTable("#tb_control", {
            ajax: {
                url: "/novedades/historicoNovedades",
                type: "GET",
                data: function (d) {
                    d.area_id = getAreasSeleccionadas().join(",") || null;
                    d.idNovedad = getNovedadesSeleccionadas().join(",") || null;
                    d.paraFinnegans = getFinnegansSeleccionadas().join(",") || null;
                    d.desde = $("#filtroDesde").val();
                    d.hasta = $("#filtroHasta").val();
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
                { data: "AREA" },
                { data: "REGISTRANTE" },
                { data: "COLABORADOR" },
                {
                    data: "CODIGO_NOVEDAD",
                    width: "3%",
                    className: "text-start",
                    title: "NOVEDAD",
                    visible: false,
                },
                { data: "NOVEDAD_NOMBRE", className: "text-wrap" },
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
                { data: "DESCRIPCION", width: "auto", className: "text-wrap" },                
                { data: null,
                    orderable: false,
                    className: "text-center",
                    render: function(data, type, row){
                        if (USER_ROLE==='Administrador/a'){
                            return(
                                `<button class="btn btn-danger btn-eliminar" data-id="${row.REGISTRO}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>`
                            );
                        };
                        return "";
                    },                    
                },
            ],

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
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'> \
                    <'ms-md-auto mt-2 mt-md-0'> \
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

        $(document).on("change",".check-area, .check-nov, .check-Finnegans",function () {
              tablaControl.ajax.reload();
            },
        );

        $("#btn-limpiar-filtros").on("click", function () {
            $(".check-area, .check-nov, .check-Finnegans").prop(
                "checked",
                false,
            );
            document.querySelector("#filtroDesde")._flatpickr.clear();
            document.querySelector("#filtroHasta")._flatpickr.clear();
            $("#toggleAreas span").text("Áreas");
            $("#toggleNov span").text("Novedades");
            $("#toggleFinnegans span").text("Finnegans");
            tablaControl.search("").draw();
            tablaControl.ajax.reload();
        });

        $("#searchRegistro").on("keyup", function () {
            let valor = $(this).val();
            tablaControl.search(valor).draw();
        });

        $("#btnClearSearch").on("click", function () {
            $("#searchRegistro").val("");
            tablaControl.search("").draw();
        });

        setTimeout(function () {
            if ($("#area").val() !== "" && $("#area").val() !== null) {
                tablaControl.ajax.reload();
            }
        }, 500);

        $(document).on("click", "#tb_control tbody tr", function (event) {
            event.preventDefault();
            event.stopPropagation();
            if ($(event.target).closest(".btn-eliminar").length) return;
            const tabla = $("#tb_control").DataTable();
            const data = tabla.row(this).data();
            if (!data) return;
            const idRegistro = data.REGISTRO;
            console.log("Id registro recibido:", idRegistro);
            verDetalleNovedad(idRegistro);
        });

        $(document).on("click", ".btn-eliminar", function (evento){
            event.preventDefault();
            event.stopPropagation();
            const idRegistro=$(this).data("id");
            console.log("Id recibido: "+idRegistro)
            anularNovedad(idRegistro);
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

                $("#inputFechaAplicacion").val(d.FECHA_APLICACION);
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
                $("#inputNovedad").val(d.novedad).trigger('change');
                $("#inputIdNovedad").val(d.novedad);
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

$(document).on("click", "#btnExportExcelHist", function () {
    let params = new URLSearchParams({
        area_id: getAreasSeleccionadas().join(",") || "",
        idNovedad: getNovedadesSeleccionadas().join(",") || "",
        paraFinnegans: getFinnegansSeleccionadas().join(",") || "",
        desde: $("#filtroDesde").val(),
        hasta: $("#filtroHasta").val(),
    });

    window.location.href = "/novedades/exportarExcelHist?" + params.toString();
});
