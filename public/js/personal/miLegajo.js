function calcularEdad(fecha) {
    if (!fecha) return "";

    const nacimiento = new Date(fecha);
    const hoy = new Date();

    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const m = hoy.getMonth() - nacimiento.getMonth();

    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }

    return edad;
}

function calcularAntiguedad(fecha) {
    if (!fecha) return "";

    const ingreso = new Date(fecha);
    const hoy = new Date();

    let años = hoy.getFullYear() - ingreso.getFullYear();
    let meses = hoy.getMonth() - ingreso.getMonth();

    if (hoy.getDate() < ingreso.getDate()) {
        meses--;
    }

    if (meses < 0) {
        años--;
        meses += 12;
    }

    let texto = "";

    if (años > 0) {
        texto += `${años} año${años > 1 ? "s" : ""}`;
    }

    if (meses > 0) {
        texto += (texto ? " " : "") + `${meses} mes${meses > 1 ? "es" : ""}`;
    }

    return texto || "0 meses";
}

function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

$(document).ready(function () {
    cargarRequerimientosAlimentarios();
});

$(document).ready(function () {
    const $select = $("#obraSocial");

    $.ajax({
        url: "/obra-social/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.select2({
                data: data,
                placeholder: "Buscar obra social...",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#modalAltaColaborador"),
            });
        },
        error: function (err) {
            console.error("Error cargando obras sociales:", err);
        },
    });
});

$("#obraSocial").on("select2:select", function (e) {
    const data = e.params.data;
    $("#codigoOS").val(data.codigo ?? "");
});

$("#obraSocial").on("select2:clear", function () {
    $("#codigoOS").val("");
});

$("#btnAgregarHijoEdit").on("click", function (e) {
    e.preventDefault();
    $("#divHijosEdit").removeAttr("hidden");

    const html = `
        <div class="row g-3 fila-hijo"> 
            <div class="col-lg-4">
                <label class="form-label">Nombre y Apellido</label>
                <input type="text" name="hijosEdit[0][nombre]" class="form-control" required>
            </div>
            <div class="col-lg-3">
                <label class="form-label">DNI</label>
                <input type="text" name="hijosEdit[0][dni]" class="form-control">
            </div>
            <div class="col-lg-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btnQuitarHijo">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    $("#divHijosEdit").append(html);
});

$(document).on("click", ".btnQuitarHijo", function () {
    $(this).closest(".fila-hijo").remove();
});

$(function () {
    $("#selectLocalidad").select2({
        language: {
            inputTooShort: function () {
                return "Por favor, ingresá 3 o más caracteres";
            },
            searching: function () {
                return "Buscando en Georef...";
            },
            noResults: function () {
                return "No se encontraron localidades";
            },
            errorLoading: function () {
                return "La carga falló. Verificá tu conexión.";
            },
        },
        placeholder: "Buscar localidad...",
        minimumInputLength: 3,
        width: "100%",
        ajax: {
            url: "/geo/localidades",
            dataType: "json",
            delay: 400,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: (data.localidades || []).map(function (l) {
                        let nombreCompleto =
                            l.nombre + " - " + l.provincia.nombre;
                        return {
                            id: nombreCompleto,
                            text: nombreCompleto,
                        };
                    }),
                };
            },
            cache: true,
        },
    });
});

$("#formAltaColaborador").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    Swal.fire({
        title: "Actualizando legajo...",
        text: "Por favor espere",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "/actualizarMiLegajo",
        type: "POST",
        data: formData,

        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "Actualizado",
                    text: response.mensaje,
                    confirmButtonText: "Aceptar",
                }).then(() => {
                    location.reload(); // refresh completo
                });
            } else {
                Swal.fire("Atención", response.mensaje, "warning");
            }
        },

        error: function () {
            Swal.fire("Error", "No se pudo actualizar el legajo", "error");
        },
    });
});

function cargarMiLegajo() {
    $.ajax({
        url: "/verMiLegajo",
        type: "GET",
        success: function (response) {
            if (response.success) {
                const d = response.data;

                $("#inputLegajo").val(d.LEGAJO);
                $("#inputNombre").val(d.COLABORADOR);
                $("#inputEstado").val(d.ESTADO);
                $("#inputDni").val(d.DNI);
                $("#inputCuil").val(d.CUIL);
                $("#inputFechaNacimiento").val(
                    formatearFechaArgentina(d.FECHA_NAC),
                );

                $("#inputEdad").val(calcularEdad(d.FECHA_NAC));
                $("#inputEmail").val(d.CORREO);
                $("#inputTelefono").val(d.TELEFONO);
                $("#inputDomicilio").val(d.DOMICILIO);
                if (d.LOCALIDAD) {
                    let option = new Option(
                        d.LOCALIDAD,
                        d.LOCALIDAD,
                        true,
                        true,
                    );
                    $("#selectLocalidad").append(option).trigger("change");
                }

                cargarReqSeleccionados(d.LEGAJO);

                $("#inputEstadoCivil").val(d.ESTADO_CIVIL);
                $("#inputGenero").val(d.GENERO);
                $("#inputObraSocial").val(d.OBRA_SOCIAL);
                $("#inputCodigoOS").val(d.COD_OS);
                $("#inputTitulo").val(d.TITULO);
                $("#inputDescripTitulo").val(d.DESCRIP_TITULO);
                $("#inputMatricula").val(d.MAT_PROF);

                $("#inputTipoContrato").val(d.TIPO_CONTRATO);
                $("#inputFechaIngreso").val(
                    formatearFechaArgentina(d.FECHA_INGRESO),
                );
                $("#inputFechaFinPrueba").val(
                    formatearFechaArgentina(d.FECHA_FIN_PRUEBA),
                );

                $("#inputAntiguedad").val(calcularAntiguedad(d.FECHA_INGRESO));
                $("#inputFechaEgreso").val(d.FECHA_EGRESO);
                $("#inputArea").val(d.AREA);
                $("#inputServicio").val(d.SERVICIO);
                $("#inputConvenio").val(d.CONVENIO);
                $("#inputCategoria").val(d.CATEGORIA);
                $("#inputRol").val(d.ROL);
                $("#inputRegimen").val(d.REGIMEN);
                $("#inputHorasDiarias").val(d.HORAS_DIARIAS);
                $("#inputCordinador").val(d.COORDINADOR);
                $("#inputAfiliado").val(d.AFILIADO);

                $("#personaEmergencia1Edit").val(d.PERSONA_EMERG1);
                $("#contactoEmergencia1Edit").val(d.CONTACTO_EMERG1);
                $("#parentescoEmergencia1Edit").val(d.PARENTESCO_EMERG1);
                $("#personaEmergencia2Edit").val(d.PERSONA_EMERG2);
                $("#contactoEmergencia2Edit").val(d.CONTACTO_EMERG2);
                $("#parentescoEmergencia2Edit").val(d.PARENTESCO_EMERG2);
                $("#padreColaboradorEdit").val(d.PADRE);
                $("#madreColaboradorEdit").val(d.MADRE);

                cargarHijoColab(d.LEGAJO);
    
                console.log("RESPONSE COMPLETA:", response);
            } else {
                Swal.fire("Error", response.mensaje, "error");
            }
        },

        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo cargar el legajo",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });
}

function cargarRequerimientosAlimentarios() {
    $.ajax({
        url: "/requerimientos-alimentarios",
        type: "GET",
        success: function (data) {
            let html = "";

            data.forEach((r) => {
                html += `
                    <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                        <label class="req-card w-100">
                            <div class="req-check">
                                <input type="checkbox"
                                       name="req_alimenticios[]"
                                       value="${r.id}">
                                <span class="req-title">
                                    ${r.nombre}
                                </span>
                            </div>

                            <small class="req-desc">
                                ${r.descripcion ?? ""}
                            </small>
                        </label>
                    </div>
                `;
            });

            $("#contenedorRequerimientos").html(html);
        },
        error: function () {
            console.error("Error cargando requerimientos alimentarios");
        },
    });
}

$(document).on("change", "input[name='req_alimenticios[]']", function () {
    const idOtro = 12; // ID "Otro"
    const seleccionados = $("input[name='req_alimenticios[]']:checked")
        .map(function () {
            return parseInt($(this).val());
        })
        .get();

    if (seleccionados.includes(idOtro)) {
        $("#rowObservacionReq").slideDown(150);
    } else {
        $("#rowObservacionReq").slideUp(150);
        $("#inputReqOtro").val("");
    }
});

function cargarReqSeleccionados(legajo) {
    $.get(`/personal/${legajo}/req-alimenticios`, function (data) {
        $("input[name='req_alimenticios[]']").prop("checked", false);

        data.forEach((r) => {
            $(`input[value="${r.idRequerimiento}"]`).prop("checked", true);

            if (r.idRequerimiento == 12) {
                $("#rowObservacionReq").show();
                $("#inputReqOtro").val(r.observ);
            }
        });
    });
}

function cargarReqSeleccionados(legajo) {
    $.ajax({
        url: `/personal/${legajo}/req-alimenticios`,
        type: "GET",
        success: function (data) {
            // limpiar todos
            $("input[name='req_alimenticios[]']").prop("checked", false);

            $("#rowObservacionReq").hide();
            $("#inputReqOtro").val("");

            data.forEach((r) => {
                $(
                    `input[name='req_alimenticios[]'][value='${r.idRequerimiento}']`,
                ).prop("checked", true);

                // si es "otro"
                if (r.idRequerimiento == 12) {
                    $("#rowObservacionReq").show();
                    $("#inputReqOtro").val(r.observ);
                }
            });
        },
    });
}

$(document).ready(function () {
    cargarMiLegajo();
});
