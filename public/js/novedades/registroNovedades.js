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
        if (dia === 0) domingos++;
        if (dia === 6) sabados++;

        fecha.setDate(fecha.getDate() + 1);
    }

    let diasHabiles = diasTotales - domingos - sabados / 2;
    let horas = diasHabiles * 8;

    document.getElementById("inputHoras").value = parseFloat(horas);
}

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

$("#inputImporte").on("change", function () {
    let monto = 0;
    monto = $(this).val();
    $(this).val(formatearPesos(monto));
});

$("#inputImporte").on("focus", function () {
    $(this).val("");
});


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
            fechaFin.dispatchEvent(new Event('change'));
        }
    }

    fechaFin.addEventListener("change", () => {
        if (!fechaInicio.value || !fechaFin.value) return;

        const d1 = new Date(fechaInicio.value + "T00:00:00");
        const d2 = new Date(fechaFin.value + "T00:00:00");

        if (d2 < d1) {
            Swal.fire("Atención", "La fecha de fin no puede ser anterior a la fecha de inicio.", "warning");
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

$("#formCargaNovedad").on("submit", function (e) {
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
});

function abrirModalNovedad() {
    $("#modalRegNovedadColaborador").modal("show");
}

function cerrarModalNovedad() {
    $("#formCargaNovedad")[0].reset();
    $("#selectNovedad").val(null).trigger("change");
    $("#modalRegNovedadColaborador").modal("hide");
}