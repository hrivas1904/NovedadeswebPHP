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

function calcularHorasHabilesMasiva() {
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

/*
document
    .getElementById("fechaHastaNovedad")
    .addEventListener("change", function () {
        const fechaDesde = document.getElementById("fechaDesdeNovedad").value;
        const fechaHasta = this.value;

        if (!fechaDesde || !fechaHasta) return;

        const inicio = new Date(fechaDesde + "T00:00:00");
        const fin = new Date(fechaHasta + "T00:00:00");

        if (fin < inicio) {
            Swal.fire(
                "Fecha inválida",
                "La fecha hasta no puede ser anterior a la fecha desde",
                "warning",
            );

            this.value = "";
            document.getElementById("inputDias").value = "";
            return;
        }

        calcularDias();
    });*/

//calcular fecha hasta
$("#inputDias").on("change", function () {
    let fechaDesdeStr = $("#fechaDesdeNovedad").val();
    let dias = parseInt($(this).val());

    if (!fechaDesdeStr || isNaN(dias)) return;

    let fechaDesde = new Date(fechaDesdeStr);
    fechaDesde.setDate(fechaDesde.getDate() + dias);

    let yyyy = fechaDesde.getFullYear();
    let mm = String(fechaDesde.getMonth() + 1).padStart(2, "0");
    let dd = String(fechaDesde.getDate()).padStart(2, "0");

    $("#fechaHastaNovedad").val(`${yyyy}-${mm}-${dd}`);
});

//calcula fecha aplicación como último día del mes
function setFechaAplicacionUltimoDiaMes() {
    const hoy = new Date();
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    const fechaFormateada = ultimoDia.toISOString().split("T")[0];
    document.querySelector('input[name="fechaAplicacion"]').value =
        fechaFormateada;
}

/*document
    .getElementById("fechaDesdeNovedad")
    .addEventListener("change", function () {
        calcularDias();
        calcularHorasHabilesMasiva();
    });

document
    .getElementById("fechaHastaNovedad")
    .addEventListener("change", function () {
        calcularDias();
        calcularHorasHabilesMasiva();
    });*/

$("#inputImporte").on("change", function () {
    let monto = 0;
    monto = $(this).val();
    $(this).val(formatearPesos(monto));
});

$("#inputImporte").on("focus", function () {
    $(this).val("");
});

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
            $("input[name='horasDiarias']").val(data.HORAS_DIARIAS);
            $("input[name='convenio']").val(data.CONVENIO);
            $("input[name='titulo']").val(data.TITULO);
            $("input[name='afiliado']").val(data.AFILIADO);

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

document.addEventListener("DOMContentLoaded", () => {
    const fechaInicio = document.getElementById("fechaDesdeNovedad");
    const fechaFin = document.getElementById("fechaHastaNovedad");
    let novedadSeleccionada = null;

    $("#selectNovedad").on("select2:select", function (e) {
        novedadSeleccionada = e.params.data;
        calcularFechaFin();
    });

    fechaInicio.addEventListener("change", calcularFechaFin);

    function calcularFechaFin() {
        if (!fechaInicio.value || !novedadSeleccionada) return;

        const limiteLicencia = parseInt(novedadSeleccionada.limite) || 0;

        let fecha = new Date(fechaInicio.value + "T00:00:00");

        if (isNaN(fecha.getTime())) {
            console.error("Fecha de inicio inválida");
            return;
        }

        fecha.setDate(fecha.getDate() + limiteLicencia);

        const yyyy = fecha.getFullYear();
        const mm = String(fecha.getMonth() + 1).padStart(2, "0");
        const dd = String(fecha.getDate()).padStart(2, "0");

        if (!isNaN(yyyy) && !isNaN(parseInt(mm)) && !isNaN(parseInt(dd))) {
            fechaFin.value = `${yyyy}-${mm}-${dd}`;
            fechaFin.dispatchEvent(new Event("change"));
        }
    }

    fechaFin.addEventListener("change", () => {
        if (!fechaInicio.value || !fechaFin.value) return;

        const d1 = new Date(fechaInicio.value + "T00:00:00");
        const d2 = new Date(fechaFin.value + "T00:00:00");

        if (d2 < d1) {
            Swal.fire(
                "Atención",
                "La fecha de fin no puede ser anterior a la fecha de inicio.",
                "warning",
            );
            fechaFin.value = "";
        }
    });
});

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

/*$("#formCargaNovedad").on("submit", function (e) {
    e.preventDefault();

    const dias = parseFloat(document.getElementById("inputDias").value) || 0;
    const horas = parseFloat(document.getElementById("inputHoras").value) || 0;
    const pesosRaw = document.getElementById("inputImporte").value.trim();

    let valorFinal = null;

    if (pesosRaw !== "") {
        valorFinal = parsearMonto(pesosRaw);
    } else if (horas > 0) {
        valorFinal = horas;
    } else if (dias > 0) {
        valorFinal = dias;
    }

    $("#cantidadFinal").val(valorFinal);

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
                $("#selectNovedad").val(null).trigger("change");
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
});*/

$("#formCargaNovedad").on("submit", function (e) {
    e.preventDefault();

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

$(document).ready(function () {
    $.ajax({
        url: "/novedades/selector",
        type: "GET",
        dataType: "json",
        success: function (data) {
            novedadesSelect = data;
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

    row.find(".codigoFinnegans").val(data.codigo || "");

    // guardar tipo de valor en la fila
    row.data("tipo_valor", data.tipo_valor);
});

$(document).on(
    "change",
    "input[name='fechaDesde[]'], input[name='fechaHasta[]']",
    function () {
        const row = $(this).closest("tr");
        calcularValorFila(row);
    },
);

function calcularValorFila(row) {
    const tipo = row.data("tipo_valor");

    const fechaDesde = row.find("input[name='fechaDesde[]']").val();
    const fechaHasta = row.find("input[name='fechaHasta[]']").val();

    if (!fechaDesde || !fechaHasta) return;

    const inicio = new Date(fechaDesde + "T00:00:00");
    const fin = new Date(fechaHasta + "T00:00:00");

    if (fin < inicio) return;

    const inputValor = row.find("input[name='valor[]']");

    if (tipo === "HORAS") {
        let diasHabiles = 0;
        let fecha = new Date(inicio);

        while (fecha <= fin) {
            const diaSemana = fecha.getDay();

            if (diaSemana !== 0 && diaSemana !== 6) {
                diasHabiles++;
            }

            fecha.setDate(fecha.getDate() + 1);
        }

        inputValor.val(diasHabiles * 8);
    } else if (tipo === "DIAS") {
        const diff = fin - inicio;
        const dias = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;

        inputValor.val(dias);
    }
}

$(document).on("blur", "input[name='valor[]']", function () {
    const row = $(this).closest("tr");
    const tipo = row.data("tipo_valor");

    if (tipo === "PESOS") {
        let valor = parsearMonto($(this).val());

        $(this).val(formatearPesos(valor));
    }
});

$(document).on("select2:select", ".selectNovedadRow", function (e) {
    const row = $(this).closest("tr");

    const codigo = e.params.data.codigo;
    const nombre = e.params.data.text;

    row.find(".codigoFinnegans").val(codigo);

    let extras = "";

    if (nombre === "Licencia anual") {
        extras = `
        <select class="form-control tipoVacaciones">
            <option value="2">Gozadas</option>
            <option value="3">Gozadas pagadas</option>
            <option value="4">Pagadas</option>
            <option value="5">Vencidas</option>
        </select>

        <input type="number" class="form-control annioVacaciones">
        `;
    }

    if (nombre === "Atención sanatorial") {
        extras = `
        <input type="number" class="form-control numAtencion" placeholder="N° atención">
        <input type="text" class="form-control paciente" placeholder="Paciente">
        <input type="text" class="form-control concepto">
        <input type="number" class="form-control cuotas">
        `;
    }

    row.find(".extras").html(extras);
});

$(document).on("click", ".btnEliminarRow", function () {
    $(this).closest("tr").remove();
});
