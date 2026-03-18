let novedades = [];
let novedadesSelect = [];

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

function parsearMonto(valor) {
    if (!valor) return 0;
    let limpio = valor.toString();
    limpio = limpio.replace(/\$/g, "").trim();
    limpio = limpio.replace(/\./g, "");
    limpio = limpio.replace(/,/g, ".");

    const resultado = parseFloat(limpio);
    return isNaN(resultado) ? 0 : resultado;
}

function calcularHorasHabilesUTI() {
    const fechaDesde = document.getElementById("fechaDesdeNovedad").value;
    const fechaHasta = document.getElementById("fechaHastaNovedad").value;

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

        if (diaSemana !== 0 && diaSemana !== 6) {
            diasHabiles++;
        }

        fecha.setDate(fecha.getDate() + 1);
    }

    const horas = diasHabiles * 8;
    document.getElementById("inputHoras").value = horas;
}

function calcularDias() {
    const fechaDesde = document.getElementById("fechaDesdeNovedad").value;
    const fechaHasta = document.getElementById("fechaHastaNovedad").value;

    if (!fechaDesde || !fechaHasta) return;

    const inicio = new Date(fechaDesde + "T00:00:00");
    const fin = new Date(fechaHasta + "T00:00:00");

    const diff = fin - inicio;

    if (diff < 0) return;

    const dias = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;

    document.getElementById("inputDias").value = dias;
}

//calcula fecha aplicación como último día del mes
function setFechaAplicacionUltimoDiaMes() {
    const hoy = new Date();
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    const fechaFormateada = ultimoDia.toISOString().split("T")[0];
    document.querySelector('input[name="fechaAplicacion"]').value =
        fechaFormateada;
}

$(document).on("change", "#chkTodos", function () {
    const checked = $(this).prop("checked");

    $(".chk-colaborador").prop("checked", checked);
});

$(document).on("change", ".chk-colaborador", function () {
    const total = $(".chk-colaborador").length;
    const seleccionados = $(".chk-colaborador:checked").length;

    $("#chkTodos").prop("checked", total === seleccionados);
});

//abrir modal con datos del colaborador para cargar novedad
function registrarNovedad(legajoColaborador) {
    console.log("Cargar novedad:", legajoColaborador);
    setFechaAplicacionUltimoDiaMes();
    const modal = $("#modalRegNovedadColaborador");

    $.ajax({
        url: `/personal/info/${legajoColaborador}`,
        type: "GET",
        success: function (data) {
            $("#tituloRegNovedad").html(`
                <i class="fa-solid fa-id-card me-2"></i>
                Registrar novedad de sueldo a <strong>${data.COLABORADOR}</strong>
            `);

            $("input[name='legajo']").val(data.LEGAJO);
            $("input[name='colaborador']").val(data.COLABORADOR);
            $("input[name='servicio']").val(data.SERVICIO);
            $("input[name='antiguedad']").val(
                calcularAntiguedad(data.FECHA_INGRESO),
            );
            $("input[name='regimen']").val(data.REGIMEN);
            $("input[name='uti']").val(data.UTI);
            $("input[name='convenio']").val(data.CONVENIO);
            $("input[name='titulo']").val(data.TITULO);
            $("input[name='noche']").val(data.NOCHE);

            modal.modal("show");
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo obtener la información del colaborador",
            });
        },
    });

    $(document).on("select2:select", "#selectNovedad", function (e) {
        const codigoVacaciones = e.params.data.codigo;
        const nombreNovedad = e.params.data.text;
        console.log("Código directo:", codigoVacaciones);
        if (nombreNovedad === "Licencia anual") {
            $(
                "#divSelectTipoVacaciones, #divSelectAnnioVacaciones",
            ).removeClass("d-none");

            console.log("mostrando div selector");
        } else {
            $("#divSelectTipoVacaciones, #divSelectAnnioVacaciones").addClass(
                "d-none",
            );
        }

        if (nombreNovedad === "Atención sanatorial") {
            $(
                "#divFechaAtencion, #divNumeroAtencion, #divIngresarPacienteAtencion, #divConceptoAtencion, #divMontoAtencion, #divCantidadCuotas",
            ).removeClass("d-none");

            console.log("mostrando div selector");
        } else {
            $(
                "#divFechaAtencion, #divNumeroAtencion, #divIngresarPacienteAtencion, #divConceptoAtencion, #divMontoAtencion, #divCantidadCuotas",
            ).addClass("d-none");
        }
    });
}

//selector de novedades de sueldo
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
                dropdownParent: $("#modalRegNovedadColaborador"),
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

$("#formCargaNovedad").on("submit", function (e) {
    e.preventDefault();

    // 🔥 LIMPIAR VALORES PESOS ANTES DE SERIALIZAR
    $("#tablaNovedades tbody tr").each(function () {
        let row = $(this);

        let tipo = row.find("input[name='tipo_valor[]']").val();
        let inputValor = row.find("input[name='valor[]']");
        let valor = inputValor.val();

        if (!tipo || !valor) return;

        tipo = tipo.toString().trim().toUpperCase();

        if (tipo === "PESOS") {
            let limpio = valor
                .replace(/\$/g, "") // quitar $
                .replace(/\./g, "") // quitar miles
                .replace(",", ".") // decimal
                .trim();

            console.log("valor original:", valor);
            console.log("valor limpio:", limpio);

            inputValor.val(limpio);
        }
    });

    // 🚀 TU AJAX (igual que antes)
    $.ajax({
        url: "/novedades/registrar",
        type: "POST",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    text: response.mensaje,
                });

                $("#formCargaNovedad")[0].reset();
                $("#tablaNovedades tbody").empty();
                $("#modalRegNovedadColaborador").modal("hide");

                if (tablaHistorialNovedades) {
                    tablaHistorialNovedades.ajax.reload(null, false);
                }

                if (typeof tablaPersonal !== "undefined") {
                    tablaPersonal.ajax.reload(null, false);
                }
            } else {
                Swal.fire("Atención", response.mensaje, "warning");
            }
        },

        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire("Error", "No se pudo registrar la novedad", "error");
        },
    });
});

function abrirModalNovedad() {
    $("#modalRegNovedadColaborador").modal("show");
}

function cerrarModalNovedad() {
    $("#formCargaNovedad")[0].reset();
    $("#selectNovedad").val(null).trigger("change");
    $("#tablaNovedades tbody").empty();
    $("#modalRegNovedadColaborador").modal("hide");
}

function anularNovedad(idRegistro) {
    Swal.fire({
        title: "¿Anular novedad?",
        text: "La novedad quedará desactivada",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, anular",
        confirmButtonColor: "#004a7c",
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#00b18d",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/novedades/anular",
                type: "POST",
                data: {
                    idRegistro: idRegistro,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },

                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Correcto",
                            text: response.mensaje,
                        });

                        if (tablaControl) {
                            tablaControl.ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire("Atención", response.mensaje, "warning");
                    }
                },

                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire("Error", "No se pudo anular la novedad", "error");
                },
            });
        }
    });
}

//CARGA MASIVA DE NOVEDADES
$(document).ready(function () {
    if ($("#tbSeleccionColabs").length > 0) {
        tablaPersonal = $("#tbSeleccionColabs").DataTable({
            ajax: {
                url: "/personal/listar",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    if (
                        USER_ROLE !== "Administrador/a" &&
                        USER_ROLE !== "Coordinador/a L2"
                    ) {
                        d.area_id = USER_AREA_ID;
                    } else {
                        d.area_id = $("#filtroArea").val() || null;
                    }
                    d.p_uti = $("#selectUti").val() || null;
                    d.p_noche = $("#selectNoche").val() || null;
                },
            },
            autoWidth: true,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: "50vh",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            columns: [
                {
                    data: "LEGAJO",
                    width: "4%",
                    className: "text-start",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "COLABORADOR", width: "auto", className: "text-start" },
                { data: "DNI", width: "5%", className: "text-start" },
                { data: "AREA", width: "6%", className: "text-start" },
                {
                    data: null,
                    className: "text-center",
                    width: "auto",
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input chk-colaborador"
                                    type="checkbox"
                                    value="${row.LEGAJO}">
                            </div>
                        `;
                    },
                },
            ],
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1' \
                    <'d-flex flex-column flex-sm-row gap-2'> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
        });

        $(
            "#filtroArea, #filtroCategoria, #filtroRegimen, #filtroConvenio, #filtroEstado, #selectUti, #selectNoche",
        ).on("change", function () {
            tablaPersonal.ajax.reload();
        });

        $("#btn-limpiar-filtros").on("click", function () {
            $("#filtroArea").val(null);
            $("#filtroCategoria").val(null);
            $("#filtroRegimen").val(null);
            $("#filtroConvenio").val(null);
            $("#filtroEstado").val(null);
            tablaPersonal.search("").draw();
            tablaPersonal.ajax.reload();
        });

        setTimeout(function () {
            if ($("#area").val() !== "" && $("#area").val() !== null) {
                tablaPersonal.ajax.reload();
            }
        }, 500);
    }
});

$(document).ready(function () {
    tablaDetalle = $("#tbDetalleNovedadMasiva").DataTable({
        paging: false,
        searching: false,
        info: false,
        scrollY: "20vh",
        scrollCollapse: true,
        ordering: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
    });
});

$("#btnSeleccionarColab").on("click", function () {
    let seleccionados = [];
    $(".chk-colaborador:checked").each(function () {
        let fila = $(this).closest("tr");
        let data = tablaPersonal.row(fila).data();
        seleccionados.push(data);
    });

    seleccionados.forEach(function (colab) {
        tablaDetalle.row
            .add([
                colab.LEGAJO.toString().padStart(5, "0"),
                colab.COLABORADOR,
                colab.DNI,
                colab.AREA,
                `<button class="btn btn-sm btn-danger btnQuitar">
                    <i class="fa-solid fa-trash"></i>
                </button>`,
            ])
            .draw(false);
    });
    $(".chk-colaborador").prop("checked", false);
    $("#modalSeleccionColab").modal("hide");
    $("#modalCargaMasivaNovedad").modal("show");
});

$(document).on("click", ".btnQuitar", function () {
    tablaDetalle.row($(this).parents("tr")).remove().draw();
});

function obtenerLegajosSeleccionados() {
    let legajos = [];

    tablaDetalle.rows().every(function () {
        let data = this.data();

        legajos.push(data[0]); // columna LEGAJO
    });

    return legajos;
}

$("#btnRegistrarMasivo").on("click", function () {
    // 1. Obtener valores de duración
    const dias = parseFloat($("#inputDias").val()) || 0;
    const horas = parseFloat($("#inputHoras").val()) || 0;
    const pesosRaw = $("#inputImporte").val().trim();

    let valorFinal = 0;
    if (pesosRaw !== "") {
        valorFinal = parsearMonto(pesosRaw);
    } else if (horas > 0) {
        valorFinal = horas;
    } else if (dias > 0) {
        valorFinal = dias;
    }

    // 2. Obtener legajos de la tabla
    let legajos = [];
    tablaDetalle.rows().every(function () {
        let data = this.data();
        legajos.push(parseInt(data[0], 10));
    });

    if (legajos.length === 0) {
        Swal.fire(
            "Atención",
            "Debe seleccionar al menos un colaborador",
            "warning",
        );
        return;
    }

    // 3. Confirmación y Envío
    Swal.fire({
        title: "¿Registrar novedades?",
        text: `Se aplicará a ${legajos.length} colaboradores`,
        icon: "question",
        showCancelButton: true,
        cancelButtonColor: "#004a7c",
        confirmButtonText: "Sí, registrar",
        confirmButtonColor: "#00b18d",
    }).then((result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: "Registrando novedades...",
            text: "Procesando colaboradores seleccionados",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        let data = {
            _token: $('input[name="_token"]').val(),
            idNovedad: $("#selectNovedad").val(),
            fechaDesde: $("#fechaDesdeNovedad").val(),
            fechaHasta: $("#fechaHastaNovedad").val(),
            fechaAplicacion: $("input[name='fechaAplicacion']").val(),
            cantidadFinal: valorFinal,
            descripcion: $("input[name='descripcion']").val(),
            annio: $("input[name='annio']").val(),
            tipoVacaciones: $("#selectTipoVacaciones").val(),
            numAtencion: $("#numAtencion").val(),
            pacienteAtencion: $("#paciente").val(),
            conceptoAtencion: $("#conceptoAtencion").val(),
            cantidadCuotas: $("#cantidadCuotas").val(),
            legajos: legajos, // Array directo
        };

        $.ajax({
            url: "/novedades/registrar-masivo",
            type: "POST",
            data: data,
            success: function (response) {
                if (response.success) {
                    let icono =
                        response.procesados < response.total
                            ? "warning"
                            : "success";

                    Swal.fire({
                        icon: icono,
                        title:
                            icono === "success"
                                ? "Operación exitosa"
                                : "Proceso con observaciones",
                        text: response.mensaje,
                        confirmButtonText: "OK",
                        confirmButtonColor: "#00b18d",
                    }).then(() => {
                        tablaDetalle.clear().draw();
                        $("#formCargaMasiva")[0].reset();
                        $("#modalCargaMasivaNovedad").modal("hide");
                        tablaControl.ajax.reload();
                    });
                } else {
                    Swal.fire("Error total", response.mensaje, "error");
                }
            },
            error: function () {
                Swal.fire(
                    "Error",
                    "No se pudo conectar con el servidor",
                    "error",
                );
            },
        });
    });
});

function cerrarModalCargaMasiva() {
    const modal = $("#modalCargaMasivaNovedad");
    $("#formCargaMasiva")[0].reset();
    tablaDetalle.clear().draw();
    $("#selectNovedad").val(null).trigger("change");
    modal.modal("hide");
}

$("#btnCargaMasiva").on("click", function () {
    const modal = $("#modalCargaMasivaNovedad");
    modal.modal("show");
    setFechaAplicacionUltimoDiaMes();
});

function cerrarModalSeleccionColab() {
    $("#modalSeleccionColab").modal("hide");
    $("#modalCargaMasivaNovedad").modal("show");
}

$("#btnAbrirSeleccionColabs").on("click", function () {
    $("#modalCargaMasivaNovedad").modal("hide");
    $("#modalSeleccionColab").modal("show");
});

$("#btnHabilitarEdicion").on("click", function () {
    $("#formDetalleNovedad .editable")
        .prop("readonly", false)
        .prop("disabled", false);

    $("#btnGuardarCambios").removeClass("d-none");
    $(this).addClass("d-none");
});




$("#btnGuardarCambios").on("click", function () {
    const data = {
        idRegistro: $("#inputRegistro").val(),
        fechaDesde: $("#inputFechaDesde").val(),
        fechaHasta: $("#inputFechaHasta").val(),
        duracion: $("#inputValor").val(),
        descripcion: $("#inputDescripcion").val(),
        nAtencion: $("#inputNumAtencion").val(),
        paciente: $("#inputPacienteAtencion").val(),
        concepto: $("#inputConcepto").val(),
        cuotas: $("#inputCuotas").val(),
        annio: $("#inputAnnioVacaciones").val(),
    };

    $.ajax({
        url: "/novedades/actualizar",
        type: "POST",
        data: data,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    text: response.mensaje,
                });

                $("#formDetalleNovedad .editable")
                    .prop("readonly", true)
                    .prop("disabled", true);

                $("#btnGuardarCambios").addClass("d-none");
                $("#btnHabilitarEdicion").removeClass("d-none");
                cerrarModalDetalleNovedad();
                tablaControl.ajax.reload(null, false);
            } else {
                Swal.fire("Atención", response.mensaje, "warning");
            }
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire("Error", "No se pudo actualizar la novedad", "error");
        },
    });
});

//NUEVO FORMATO DE CARGA
function calcularDias(fechaDesde, fechaHasta) {
    if (!fechaDesde || !fechaHasta) return "";

    const fDesde = new Date(fechaDesde);
    const fHasta = new Date(fechaHasta);

    if (fHasta < fDesde) return "";

    const diffTime = fHasta - fDesde;
    const diffDias = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

    return diffDias;
}

function calcularValorFila(row) {
    const fechaDesde = row.find("input[name='fechaDesde[]']").val();
    const fechaHasta = row.find("input[name='fechaHasta[]']").val();
    let tipo = row.find("input[name='tipo_valor[]']").val();

    console.log("tipo raw:", tipo);

    if (tipo) {
        tipo = tipo.toString().trim().toUpperCase();
    }

    console.log("tipo normalizado:", tipo);

    const inputValor = row.find("input[name='valor[]']");

    // Reset básico
    inputValor.css("text-align", "left");

    if (!fechaDesde || !fechaHasta || !tipo) {
        console.warn("Faltan datos para calcular");
        return;
    }

    const dias = calcularDias(fechaDesde, fechaHasta);

    let valor = "";

    if (tipo === "DÍAS") {
        valor = dias;
        inputValor.val(valor);
    } else if (tipo === "HORAS") {
        valor = dias * 8;
        inputValor.val(valor);
    } else if (tipo === "PESOS") {
        // 🔥 modo moneda
        inputValor.css("text-align", "right");
        inputValor.val(""); // ingreso manual
    } else {
        console.warn("Tipo no reconocido:", tipo);
    }

    console.log("valor calculado:", valor);
}

$(document).ready(function () {
    $.ajax({
        url: "/novedades/selector",
        type: "GET",
        dataType: "json",
        success: function (data) {
            novedadesSelect = data;
            console.log("novedadesSelect:", data);
        },
        error: function (err) {
            console.error("Error al cargar novedades:", err);
        },
    });
});

function inicializarSelect2Fila() {
    let select = $("#tablaNovedades tbody tr:last .selectNovedadRow");

    select.select2({
        data: novedadesSelect,
        placeholder: "Seleccione una novedad",
        allowClear: true,
        width: "100%",
        dropdownParent: $("#modalRegNovedadColaborador"),
    });
}

// Agregar fila
$(document).on("click", "#btnAgregarNovedad", function () {
    let fila = `
    <tr>
        <td>
            <select class="form-select selectNovedadRow" name="idNovedad[]">
                <option value="">Seleccionar</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control codigoFinnegans" readonly>
        </td>
        <td>
            <input type="date" class="form-control" name="fechaDesde[]">
        </td>
        <td>
            <input type="date" class="form-control" name="fechaHasta[]">
        </td>
        <td>
            <input type="text" class="form-control" name="valor[]">
        </td>
        <td>
            <input type="text" class="form-control" name="descripcion[]">
        </td>
        <td>
            <input type="text" class="form-control d-none" name="tipo_valor[]">
        </td>
        <td>
            <button type="button" class="btn btn-danger btnEliminarRow">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    </tr>
    `;

    $("#tablaNovedades tbody").append(fila);
    inicializarSelect2Fila();
});

$(document).on("select2:select", ".selectNovedadRow", function (e) {
    const row = $(this).closest("tr");
    const data = e.params.data;
    console.log("data:", data);
    row.find(".codigoFinnegans").val(data.codigo || "");
    let tipo = data.tipo_valor || data.valor;
    row.find("input[name='tipo_valor[]']").val(tipo);
    console.log("tipo detectado:", tipo);
    calcularValorFila(row);
});

$(document).on(
    "change",
    "input[name='fechaDesde[]'], input[name='fechaHasta[]']",
    function () {
        let row = $(this).closest("tr");
        calcularValorFila(row);
    },
);

$(document).on("focus", "input[name='valor[]']", function () {
    const row = $(this).closest("tr");
    let tipo = row.find("input[name='tipo_valor[]']").val();

    if (tipo) {
        tipo = tipo.toString().trim().toUpperCase();
    }

    if (tipo === "PESOS") {
        $(this).val(""); // limpia para edición
    }
});

$(document).on("blur", "input[name='valor[]']", function () {
    const row = $(this).closest("tr");
    let tipo = row.find("input[name='tipo_valor[]']").val();

    if (tipo) {
        tipo = tipo.toString().trim().toUpperCase();
    }

    if (tipo === "PESOS") {
        let valor = $(this).val();

        // Limpiar posibles símbolos antes de parsear
        valor = valor.replace(/[^\d.-]/g, "");

        if (valor !== "") {
            $(this).val(formatearPesos(parseFloat(valor)));
        }
    }
});

$(document).on("click", ".btnEliminarRow", function () {
    $(this).closest("tr").remove();
});
