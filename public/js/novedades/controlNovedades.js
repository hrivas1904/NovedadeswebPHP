let tablaPersonal = null;

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

function setFechaAplicacionUltimoDiaMes() {
    const hoy = new Date();

    // Último día del mes actual
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

    // Formato YYYY-MM-DD
    const fechaFormateada = ultimoDia.toISOString().split("T")[0];

    // Setear en el input
    document.querySelector('input[name="fechaAplicacion"]').value =
        fechaFormateada;
}

//sp para cargar selector areas
$(document).ready(function () {
    cargarAreas();
    cargarFiltroNovedad();
});

$("#btnLimpiarFiltros").on("click", function () {
    $(".js-select-area").val("");
    $(".js-select-novedadFiltro").val("");
    $(".js-select-novedadFinnegans").val("");
    $("#tb_control").DataTable().ajax.reload();
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
                $("#inputFechaDesde").val(
                    formatearFechaArgentina(d.FECHA_DESDE),
                );
                $("#inputFechaHasta").val(
                    formatearFechaArgentina(d.FECHA_HASTA),
                );

                let valor = d.DURACION;
                let valorFormateado = 0;
                let tipo_valor = d.tipo_valor;

                if (tipo_valor === "Pesos") {
                    valorFormateado = formatearPesos(valor);
                    $("#inputValor").val(valorFormateado);
                    $("#inputValor").addClass("text-end");
                } else {
                    $("#inputValor").val(parseInt(valor));
                    $("#inputValor").addClass("text-start");
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

                if (d.novedad === "Atención sanatorial") {
                    $("#divInfoAtencionMedica").removeClass("d-none");
                } else {
                    $("#divInfoAtencionMedica").addClass("d-none");
                }

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

$("#btnLimpiarFiltros").on("click", function () {
    $("#filtroDesde").val(null);
    $("#filtroHasta").val(null);
    $("#paraFinnegans").val(null);
    $("#idNovedad").val(null);
    $("#area").val(null);
    tablaPersonal.ajax.reload();
});

$("#btnAplicarFiltros").on("click", function () {
    tablaPersonal.ajax.reload();
});

//carga dt novedades
$(document).ready(function () {
    tablaPersonal = $("#tb_control");
    if (tablaPersonal.length > 0) {
        tablaPersonal = new DataTable("#tb_control", {
            ajax: {
                url: "/novedades/listarNovedadesPorArea",
                type: "GET",
                data: function (d) {
                    // Lógica de área según rol
                    if (USER_ROLE !== "Administrador/a") {
                        d.area_id = USER_AREA_ID;
                    } else {
                        d.area_id = $("#area").val();
                    }

                    // Filtros adicionales
                    d.idNovedad = $("#idNovedad").val();
                    d.paraFinnegans = $("#paraFinnegans").val();

                    // Enviamos las fechas al controlador
                    d.desde = $("#filtroDesde").val();
                    d.hasta = $("#filtroHasta").val();
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
                { data: "ANNIO" },
                {
                    data: "LEGAJO",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "TIPO" },
                {
                    data: "REGISTRO",
                    orderable: false,
                    width: "10%",
                    render: function (data, type, row) {
                        if (USER_ROLE === "Colaborador/a") {
                            return `
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" 
                                        class="btn-sm btn-secundario btn-VerDetalleNovedad" 
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
                                        class="btn-sm btn-secundario btn-VerDetalleNovedad" 
                                        data-id="${data}" 
                                        title="Detalle de novedad">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button type="button" 
                                        class="btn-sm btn-alerta btn-EditarNovedad" 
                                        data-id="${data}" 
                                        title="Editar novedad">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <button type="button" 
                                        class="btn-sm btn-peligro btn-AnularNovedad" 
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
            scrollY: "58vh",
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

        $("#paraFinnegans").on("change", function () {
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
            console.log("Id registro recibido: " + idRegistro);
            verDetalleNovedad(idRegistro);
        });
    }
});

$(document).ready(function () {
    const $select = $("#selectNovedad");

    $.ajax({
        url: "/novedades/selector",
        type: "GET",
        dataType: "json",
        success: function (data) {
            // Inicializar Select2 con los datos directamente
            $select.select2({
                data: data,
                placeholder: "Seleccione una novedad",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#modalCargaMasivaNovedad"),
            });
        },
        error: function (err) {
            console.error("Error al cargar novedades:", err);
        },
    });

    // Event handler para capturar el código
    $select.on("select2:select", function (e) {
        const data = e.params.data;
        console.log("Seleccionado:", data);

        // Buscar el código en los datos originales
        const novedad = data;
        $("#codigoFinnegans").val(novedad.codigo || "");
        $("#idNovedad").val(data.id);
    });
});

function resetearNovedades() {
    $("#selectNovedad")
        .empty()
        .append('<option value="">Seleccionar novedad</option>')
        .prop("disabled", true);

    $("#codigoFinnegans").val("");
    $("#idNovedad").val("");
}

function formatearPesos(valor) {
    if (valor === null || valor === undefined || valor === "") return "";

    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    }).format(valor);
}

function parsearMonto(valor) {
    if (!valor) return 0;
    let limpio = valor.toString();
    limpio = limpio.replace(/\$/g, "").trim();
    limpio = limpio.replace(/\./g, "");
    limpio = limpio.replace(/,/g, ".");

    const resultado = parseFloat(limpio);
    return isNaN(resultado) ? 0 : resultado;
}

$("#inputImporte").on("change", function () {
    let monto = 0;
    monto = $(this).val();
    $(this).val(formatearPesos(monto));
});

$("#inputImporte").on("focus", function () {
    $(this).val("");
});

document.addEventListener("DOMContentLoaded", () => {
    const fechaDesdeInput = document.querySelector('[name="fechaDesde"]');
    const fechaHastaInput = document.querySelector('[name="fechaHasta"]');

    fechaDesdeInput.addEventListener("blur", calcularDuracion);
    fechaHastaInput.addEventListener("blur", calcularDuracion);
});

function calcularDuracion() {
    const fechaDesdeInput = document.querySelector('[name="fechaDesde"]');
    const fechaHastaInput = document.querySelector('[name="fechaHasta"]');
    const duracionInput = document.querySelector('[name="duracion"]');

    const desdeVal = fechaDesdeInput.value;
    const hastaVal = fechaHastaInput.value;

    // No validar si aún no están completas
    if (desdeVal.length !== 10 || hastaVal.length !== 10) {
        duracionInput.value = "";
        return;
    }

    const fechaDesde = new Date(desdeVal + "T00:00:00");
    const fechaHasta = new Date(hastaVal + "T00:00:00");

    if (isNaN(fechaDesde) || isNaN(fechaHasta)) {
        duracionInput.value = "";
        return;
    }

    if (fechaHasta < fechaDesde) {
        alert("La fecha hasta no puede ser anterior a la fecha desde");
        fechaHastaInput.value = "";
        duracionInput.value = "";
        return;
    }

    const diffTime = fechaHasta - fechaDesde;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

    duracionInput.value = diffDays;
}

document.addEventListener("DOMContentLoaded", () => {
    let novedadSeleccionada = null;

    $("#selectNovedad").on("select2:select", function (e) {
        novedadSeleccionada = e.params.data;
        const valorNovedad = novedadSeleccionada.valor.trim();

        if (valorNovedad === "Días") {
            document.getElementById("divPeriodoDias").hidden = false;
            document.getElementById("divCantidadHoras").hidden = true;
            document.getElementById("divCantidadPesos").hidden = true;
        } else if (valorNovedad === "Horas") {
            document.getElementById("divPeriodoDias").hidden = true;
            document.getElementById("divCantidadHoras").hidden = false;
            document.getElementById("divCantidadPesos").hidden = true;
        } else if (valorNovedad === "Pesos") {
            document.getElementById("divPeriodoDias").hidden = true;
            document.getElementById("divCantidadHoras").hidden = true;
            document.getElementById("divCantidadPesos").hidden = false;
        }
    });
});

function calcularHorasHabiles() {
    const fechaDesde = document.getElementById("fechaDesdeNovedad").value;
    const fechaHasta = document.getElementById("fechaHastaNovedad").value;

    if (!fechaDesde || !fechaHasta) return;

    const [y1, m1, d1] = fechaDesde.split("-");
    const [y2, m2, d2] = fechaHasta.split("-");

    let inicio = new Date(y1, m1 - 1, d1);
    let fin = new Date(y2, m2 - 1, d2);

    let diasTotales = 0;
    let sabados = 0;
    let domingos = 0;

    let fecha = new Date(inicio);

    while (fecha <= fin) {
        diasTotales++;

        let dia = fecha.getDay();
        // 0 domingo - 6 sábado

        if (dia === 0) domingos++;
        if (dia === 6) sabados++;

        fecha.setDate(fecha.getDate() + 1);
    }

    let diasHabiles = diasTotales - domingos - sabados / 2;
    let horas = diasHabiles * 8;

    document.getElementById("inputHoras").value = parseFloat(horas);
}

document
    .getElementById("fechaDesdeNovedad")
    .addEventListener("change", calcularHorasHabiles);

document
    .getElementById("fechaHastaNovedad")
    .addEventListener("change", calcularHorasHabiles);

function cerrarModalCargaMasiva(){
    const modal = $("#modalCargaMasivaNovedad");
    $("#selectLocalidad").val(null).trigger("change");
    $("#formCargaMasiva")[0].reset();
    modal.modal('hide');
}

$("#btnCargaMasiva").on("click",function(){
    const modal = $("#modalCargaMasivaNovedad");
    modal.modal('show');
})