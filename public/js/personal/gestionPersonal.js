let legajoActivo = null;
let registroSeleccionado = null;
let tablaDetallePrest = null;

$(document).ready(function () {
    cargarSelectAreas();
    cargarFiltroCateg();
    cargarFiltroConvenio();
    cargarCategorias();
});

$(document).on("click", "#btnAbrirModalOs", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const modalElement = document.getElementById("modalNuevaOs");
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
});

$(document).on("show.bs.modal", ".modal", function () {
    const zIndex = 1040 + 10 * $(".modal:visible").length;
    $(this).css("z-index", zIndex);
    setTimeout(() => {
        $(".modal-backdrop")
            .not(".modal-stack")
            .css("z-index", zIndex - 1)
            .addClass("modal-stack");
    }, 0);
});

flatpickr("#fecha_nacimiento, #fechaBajaColab", {
    locale: "es",
    altInput: true,
    altFormat: "d/m/Y",
    dateFormat: "Y-m-d",
    maxDate: "today",
});

//SELECCIONAR TIPO CONTRATO
document.addEventListener("DOMContentLoaded", () => {
    const tipoContrato = document.getElementById("tipoContrato");
    const fechaInicio = document.getElementById("fechaInicio");
    const fechaFin = document.getElementById("fechaFin");

    if (!tipoContrato || !fechaInicio || !fechaFin) return;

    function calcularFechaFinContrato() {
        const tipo = tipoContrato.value;
        const inicio = fechaInicio.value;

        if (!inicio) {
            fechaFin.value = "";
            return;
        }

        const fecha = new Date(inicio);

        if (tipo === "Tiempo indeterminado") {
            fecha.setMonth(fecha.getMonth() + 6);
            fechaFin.value = fecha.toISOString().split("T")[0];
        } else if (tipo === "Pasantía" || tipo === "Práctica profesional") {
            fecha.setMonth(fecha.getMonth() + 3);
            fechaFin.value = fecha.toISOString().split("T")[0];
        } else {
            fechaFin.value = "";
        }
    }

    tipoContrato.addEventListener("change", calcularFechaFinContrato);
    fechaInicio.addEventListener("change", calcularFechaFinContrato);

    fechaFin.addEventListener("change", () => {
        if (!fechaInicio.value || !fechaFin.value) return;

        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);

        if (fin < inicio) {
            alert(
                "La fecha de fin no puede ser anterior a la fecha de inicio.",
            );
            fechaFin.value = "";
        }
    });
});

//SUBIR COMPROBANTE DE NOVEDAD
$(document).on("click", ".btn-subir-archivo", function (e) {
    e.preventDefault();
    e.stopPropagation();

    registroSeleccionado = $(this).data("registro");
    $("#inputComprobantes").click();
});

$("#inputComprobantes").on("change", function () {
    const files = this.files;
    if (!files || files.length === 0) return;

    subirComprobantes(files);
});

function subirComprobantes(files) {
    let formData = new FormData();
    formData.append("id_nxe", registroSeleccionado);

    for (let i = 0; i < files.length; i++) {
        formData.append("comprobantes[]", files[i]);
    }

    $.ajax({
        url: "/rrhh/novedades/subir-comprobante",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            alert("Comprobante subido exitosamente");
        },
        error: function (xhr) {
            Swal.fire("Error", "No se pudo subir el comprobante", "error");
            console.error(xhr.responseText);
        },
    });
}

//SELECTORES CATEGORÍAS Y ROLES
function cargarCategorias() {
    $.ajax({
        url: "/rrhh/categorias-empleados/lista",
        type: "GET",
        success: function (data) {
            $(".js-select-categoria").each(function () {
                const select = $(this);
                const valorActual = select.val();

                select.empty();
                select.append('<option value="">Seleccione categoría</option>');

                data.forEach((cat) => {
                    if (Number(cat.estado) !== 1) return;
                    select.append(
                        `<option value="${cat.id_categ}">
                            ${cat.nombre}
                        </option>`,
                    );
                });

                // mantiene selección si existía (edición)
                if (valorActual) {
                    select.val(valorActual);
                }
            });
        },
        error: function (err) {
            console.error("Error cargando categorías", err);
        },
    });
}

$(document).on("change", ".js-select-categoria", function () {
    const idCategoria = $(this).val();
    const categorySelect = this;
    const selectRol = $(this).closest("form").find('[name="rol_interno_id"]');
    const selectedRole = selectRol.val();

    // reset del rol
    selectRol
        .empty()
        .append('<option value="">Seleccione rol</option>')
        .prop("disabled", true);

    if (!idCategoria) return;

    $.ajax({
        url: `/rrhh/roles-empleados/por-categoria/${idCategoria}`,
        method: "GET",
        success: function (data) {
            if ($(categorySelect).val() !== idCategoria) return;
            data.forEach((rol) => {
                selectRol.append(
                    `<option value="${rol.id_rol}">
                        ${rol.nombre}
                    </option>`,
                );
            });

            selectRol.val(selectedRole || "").prop("disabled", false);
        },
        error: function (err) {
            console.error("Error cargando roles", err);
        },
    });
});

//SELECTORES ÁREAS Y SERVICIOS
function cargarSelectAreas() {
    const $select = $("#area");

    $select.empty().append('<option value="">Cargando...</option>');

    $.ajax({
        url: "/rrhh/areas/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.empty().append('<option value="">Seleccione área</option>');

            data.forEach((area) => {
                $select.append(`
                    <option value="${area.id_area}">
                        ${area.nombre}
                    </option>
                `);
            });
        },
        error: function (err) {
            console.error("Error cargando áreas:", err);
            $select.html('<option value="">Error al cargar</option>');
        },
    });
}

$(document).on("change", ".js-select-area", function () {
    const idArea = $(this).val();
    const selectServicio = $(".js-select-servicio");

    selectServicio
        .empty()
        .append('<option value="">Seleccione servicio</option>')
        .prop("disabled", true);

    if (!idArea) return;

    $.ajax({
        url: `/rrhh/servicios-empleados/por-area/${idArea}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            if (!Array.isArray(data)) {
                console.error("Respuesta inválida:", data);
                return;
            }

            data.forEach((servicio) => {
                selectServicio.append(
                    `<option value="${servicio.id_servicios}">
                        ${servicio.servicio}
                    </option>`,
                );
            });

            selectServicio.prop("disabled", false);
        },
        error: function (err) {
            console.error("Error cargando servicios", err);
        },
    });
});

//SELECTORES OS Y CODIGO OS
$(document).ready(function () {
    const $select = $("#obraSocial");

    $.ajax({
        url: "/rrhh/obra-social/lista",
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

let REGIMENES = [];

//MAPEO DE REGIMEN CON HORAS DIARIAS
document.addEventListener("DOMContentLoaded", function () {
    cargarRegimenesColaboradores();
});

function cargarRegimenesColaboradores() {
    $.ajax({
        url: "/rrhh/parametros/listarRegimenesColab",
        type: "GET",
        dataType: "json",

        success: function (regimenes) {
            cargarSelectorRegimen($("#selectRegimen"), regimenes, true);

            cargarSelectorRegimen($("#inputRegimen"), regimenes, false);
        },

        error: function (xhr) {
            console.error("Error al cargar regímenes:", xhr.responseText);
        },
    });
}

function cargarSelectorRegimen(selector, regimenes, soloActivos = true) {
    selector.empty();
    selector.append(`
        <option value="">
            Seleccione régimen
        </option>
    `);

    regimenes.forEach(function (item) {
        if (soloActivos && Number(item.activo) !== 1) {
            return;
        }

        const regimen = parseFloat(item.regimen);
        const horasDiarias = parseFloat(item.horasDiarias);

        const estado = Number(item.activo) === 1 ? "" : " - Inactivo";

        selector.append(`
            <option
                value="${regimen}"
                data-horas="${horasDiarias}">
                ${regimen}
            </option>
        `);
    });
}

$(document).on("change", "#selectRegimen, #inputRegimen", function () {
    const horas = $(this).find("option:selected").data("horas");

    if (this.id === "selectRegimen") {
        $("#horasDiarias").val(horas ?? "");
    } else {
        $("#inputHorasDiarias").val(horas ?? "");
    }
});

//API GEOREST
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
        minimumInputLength: 3, // Subí a 3 para evitar resultados demasiado genéricos
        width: "100%",
        dropdownParent: $("#modalAltaColaborador"),
        ajax: {
            url: "/rrhh/geo/localidades",
            dataType: "json",
            delay: 400, // Un poco más de delay ayuda a no disparar tantas peticiones
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

$(function () {
    $("#selectLocalidadEdit").select2({
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
        minimumInputLength: 3, // Subí a 3 para evitar resultados demasiado genéricos
        width: "100%",
        dropdownParent: $("#modalLegajoColaborador"),
        ajax: {
            url: "/rrhh/geo/localidades",
            dataType: "json",
            delay: 400, // Un poco más de delay ayuda a no disparar tantas peticiones
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

//CONSTRUIT CUIL COLAB
function armarCuil() {
    const cuilStart = $("#firstPartCuil").val();
    const cuilMiddle = $("#middlePartCuil").val();
    const cuilEnd = $("#lastPartCuil").val();

    if (cuilStart && cuilMiddle && cuilEnd) {
        const cuilCompleto = cuilStart + "-" + cuilMiddle + "-" + cuilEnd;
        $("#cuil").val(cuilCompleto);
    } else {
        $("#cuil").val("");
    }
}

$("#dni").on("input", function () {
    const dni = $(this).val();
    $("#middlePartCuil").val(dni);
    armarCuil();
});

$("#firstPartCuil, #lastPartCuil").on("input", function () {
    armarCuil();
});

//DAR DE ALTA COLABORADOR
function abrirModal() {
    $("#modalAltaColaborador").modal("show");
}

function cerrarModal() {
    $("#selectLocalidad").val(null).trigger("change");
    $("#obraSocial").val(null).trigger("change");
    $("#formAltaColaborador")[0].reset();
    $("#modalAltaColaborador").modal("hide");
}

$("#formAltaColaborador").on("submit", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $("#btnGuardarColaborador").prop("disabled", true);

    let datos = $form.serialize(); // no incluye campos disabled
    const idPersonal = $form.data("idPersonal");
    if (idPersonal) datos += "&id_personal=" + idPersonal; // reintento: solo vínculo

    $.ajax({
        url: "/rrhh/personal/guardar",
        type: "POST",
        data: datos,
        success: function (res) {
            $form.removeData("idPersonal");
            let texto = res.mensaje;
            if (res.avisos && res.avisos.length) {
                texto += "\n\nPendientes: " + res.avisos.join(" | ");
            }
            Swal.fire({
                title: "Operación exitosa",
                text: texto,
                icon: res.avisos && res.avisos.length ? "warning" : "success",
                customClass: { confirmButton: "btn btn-primary" },
                buttonsStyling: false,
            }).then(() => cerrarModal());
        },
        error: function (xhr) {
            const r = xhr.responseJSON || {};
            if (r.id_personal) {
                // la persona quedó creada; el próximo envío solo reintenta el vínculo
                $form.data("idPersonal", r.id_personal);
            }
            Swal.fire({
                title: "Atención",
                text: r.mensaje || "Error del servidor.",
                icon: "warning",
                customClass: { confirmButton: "btn btn-primary" },
                buttonsStyling: false,
            });
        },
        complete: function () {
            $btn.prop("disabled", false);
        },
    });
});

//CREACIÓN NUEVA OBRA SOCIAL
function cerrarModalOs() {
    $("#formNuevaOs")[0].reset();

    const modalElement = document.getElementById("modalNuevaOs");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }
}

$(document).on("submit", "#formNuevaOs", function (e) {
    e.preventDefault();
    e.stopPropagation();

    let form = $(this);

    $.ajax({
        url: form.attr("action"),
        method: "POST",
        data: form.serialize(),
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: "OK",
                    text: response.mensaje,
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // 👈 recarga toda la vista
                    }
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response.mensaje,
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.mensaje || "Error inesperado",
            });
        },
    });

    return false;
});
