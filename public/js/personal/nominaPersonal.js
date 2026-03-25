console.log("nominaPersonal.js cargado");

let tablaPersonal;

let tablaHistorialNovedades = null;
let legajoActivo = null;

let registroSeleccionado = null;

function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "60vh";
}

//calculo edad
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

//calculo antiguedad
function calcularAntiguedad(fecha) {
    if (!fecha) return "";

    const ingreso = new Date(fecha);
    const hoy = new Date();

    let años = hoy.getFullYear() - ingreso.getFullYear();
    let meses = hoy.getMonth() - ingreso.getMonth();

    // Ajuste si todavía no cumplió el mes
    if (hoy.getDate() < ingreso.getDate()) {
        meses--;
    }

    // Ajuste si los meses quedan negativos
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

//formato fecha arg
function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

//seleccionar tipo de contrato
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

//subir comprobante
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
        url: "/novedades/subir-comprobante", // 👈 TU RUTA
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

//ver comprobantes
$(document).on("click", ".btn-ver-archivo", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const idNovedad = $(this).data("registro");
    verComprobantes(idNovedad);
});

function verComprobantes(idNovedad) {
    $.ajax({
        url: `/novedades/${idNovedad}/comprobantes`,
        method: "GET",
        dataType: "json",
        success: function (response) {
            const contenedor = $("#contenedorComprobantes");
            contenedor.html("");

            if (!response || response.length === 0) {
                contenedor.html(
                    `<div class="alert alert-info">
                        Esta novedad no tiene comprobantes.
                    </div>`,
                );
            } else {
                response.forEach((comp) => {
                    const url = `/comprobantes/${comp.id}/ver`;

                    contenedor.append(`
                        <div class="mb-4 border rounded p-2">
                            <iframe
                                src="${url}"
                                style="width:100%; height:600px; border:none;"
                                loading="lazy">
                            </iframe>
                        </div>
                    `);
                });
            }

            const modal = new bootstrap.Modal(
                document.getElementById("modalVerComprobantes"),
            );
            modal.show();
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire(
                "Error",
                "No se pudieron cargar los comprobantes",
                "error",
            );
        },
    });
}

function inicializarORefrescarHistorial() {
    if (tablaHistorialNovedades) {
        tablaHistorialNovedades.ajax
            .url(`/novedades/historial/${legajoActivo}`)
            .load(null, false);
        return;
    }

    tablaHistorialNovedades = $("#tb_historialNovedades").DataTable({
        ajax: {
            url: `/novedades/historial/${legajoActivo}`,
            type: "GET",
            dataSrc: "",
        },
        columns: [
            { data: "REGISTRO" },
            {
                data: "FECHA_REGISTRO",
                width: "6%",
                render: function (data) {
                    return formatearFechaArgentina(data);
                },
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
                data: "DURACION",
                className: "text-start",
                render: (d) => d ?? "-",
            },
            {
                data: "REGISTRO",
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function (data) {
                    return `
                        <button type="button" class="btn btn-primary btn-subir-archivo" data-registro="${data}">
                            <i class="fa-solid fa-upload"></i> Subir
                        </button>
                        <button type="button"
                            class="btn btn-secondary btn-ver-archivo"
                            data-registro="${data}">
                            <i class="fa-solid fa-eye"></i> Ver
                        </button>
                    `;
                },
            },
        ],
        scrollX: true,
        paging: false,
        scrollCollapse: true,
        scrollY: "40vh",
        searching: true,
        ordering: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
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
                exportOptions: { columns: ":visible" },
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
                title: "Productos en sucursal",
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                className: "btn-printer",
            },
        ],
    });
}

//ver legajo
function verLegajo(legajoColaborador, nombre) {
    console.log("Mostrando legajo:", legajoColaborador);
    legajoActivo = legajoColaborador;

    $("#tituloLegajo").html(`
        <i class="fa-solid fa-id-card me-2"></i>
        Legajo de <strong>${nombre}</strong>
    `);

    $.ajax({
        url: `/personal/ver-legajo/${legajoColaborador}`,
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
                    $("#selectLocalidadEdit").append(option).trigger("change");
                }

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
                $("#inputArea").val(d.ID_AREA);
                cargarServiciosSimple(d.ID_SERVICIOS);
                $("#inputConvenio").val(d.CONVENIO);
                $("#inputCategoria").val(d.ID_CATEG);
                cargarRolesSimple(d.ID_ROL);
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

                $("#inputNoche").val(d.NOCHE);
                $("#inputUti").val(d.UTI);

                const familiares = response.familiares;

                const $contenedor = $("#divHijosEdit");

                $contenedor.empty().removeAttr("hidden");

                console.log("FAMILIARES:", response.familiares);

                if (familiares && familiares.length > 0) {
                    familiares.forEach((f) => {
                        const htmlFamiliar = `
                            <div class="col-lg-6">
                                <div class="p-2 border rounded bg-light d-flex justify-content-between">
                                    <span><strong>${f.nombre}</strong></span>
                                    <span class="badge bg-primary">${f.parentesco}</span>
                                </div>
                            </div>
                        `;
                        $("#divHijosEdit").append(htmlFamiliar);
                    });

                } else {
                    $contenedor.append(`
                        <div class="text-muted">No hay familiares cargados</div>
                    `);
                }

                $("#modalLegajoColaborador").modal("show");

                // 🔑 inicializar / refrescar historial CUANDO el modal ya está visible
                $("#modalLegajoColaborador")
                    .off("shown.bs.modal")
                    .on("shown.bs.modal", function () {
                        inicializarORefrescarHistorial();
                    });
            } else {
                Swal.fire("Error", response.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo cargar el legajo", "error");
        },
    });
}

//dar de baja empleado
function darDeBaja(legajoColaborador, nombre) {
    Swal.fire({
        title: `¿Dar de baja a <strong>${nombre}</strong>?`,
        text: "El colaborador quedará inactivo",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, dar de baja",
        confirmButtonColor: "#00b18d",
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/personal/baja/${legajoColaborador}`,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (res) {
                    if (res.success) {
                        Swal.fire("Operación exitosa", res.mensaje, "success");
                        tablaPersonal.ajax.reload(null, false);
                    } else {
                        Swal.fire("Atención", resp.mensaje, "warning");
                    }
                },
            });
        }
    });
}

//selector para titulo
$("#select-titulo").on("change", function () {
    const modal = $("#divTitulo");
    const modal2 = $("#divMatricula");
    if ($(this).val() === "SI") {
        modal.removeClass("d-none");
        modal2.removeClass("d-none");
    } else {
        modal.addClass("d-none");
        modal2.addClass("d-none");
    }
});

//edit empleado
function editEmpleados(legajoColaborador, nombre) {
    console.log("Mostrando legajo:", legajoColaborador);
    legajoActivo = legajoColaborador;

    $("#tituloEdit").html(`
        <i class="fa-solid fa-user-pen me-2"></i>
        Editar legajo de <strong>${nombre}</strong>
    `);

    $.ajax({
        url: `/personal/ver-legajo/${legajoColaborador}`,
        type: "GET",
        success: function (response) {
            if (response.success) {
                const d = response.data;

                $("#inputEditLegajo").val(d.LEGAJO);
                $("#inputEditNombre").val(d.COLABORADOR);
                $("#estadoEdit").val(d.ESTADO);
                $("#inputEditDni").val(d.DNI);
                $("#inputEditCuil").val(d.CUIL);
                $("#inputEditFechaNacimiento").val(
                    formatearFechaArgentina(d.FECHA_NAC),
                );

                $("#inputEditEdad").val(calcularEdad(d.FECHA_NAC));
                $("#inputEditEmail").val(d.CORREO);
                $("#inputEditTelefono").val(d.TELEFONO);
                $("#inputEditDomicilio").val(d.DOMICILIO);
                $("#inputEditLocalidad").val(d.LOCALIDAD);

                $("#estadoCivilEdit").val(d.ESTADO_CIVIL);
                $("#generoEdit").val(d.GENERO);
                $("#selectObraSocialEdit").val(d.ID_OS);
                $("#inputEditCodigoOS").val(d.COD_OS);
                $("#inputEditTitulo").val(d.TITULO);
                $("#inputEditDescripTitulo").val(d.DESCRIP_TITULO);
                $("#inputEditMatricula").val(d.MAT_PROF);

                $("#tipoContratoEdit").val(d.TIPO_CONTRATO);
                $("#inputEditFechaIngreso").val(
                    formatearFechaArgentina(d.FECHA_INGRESO),
                );
                $("#inputEditFechaFinPrueba").val(
                    formatearFechaArgentina(d.FECHA_FIN_PRUEBA),
                );

                $("#inputEditAntiguedad").val(
                    calcularAntiguedad(d.FECHA_INGRESO),
                );
                $("#inputEditFechaEgreso").val(d.FECHA_EGRESO);
                $("#areaEdit").val(d.ID_AREA);
                $("#inputEditServicio").val(d.SERVICIO);
                $("#inputEditConvenio").val(d.CONVENIO);
                $("#inputEditCategoria").val(d.CATEGORIA);
                $("#inputEditRol").val(d.ROL);
                $("#inputEditRegimen").val(d.REGIMEN);
                $("#inputEditHorasDiarias").val(d.HORAS_DIARIAS);
                $("#inputEditCordinador").val(d.COORDINADOR);
                $("#inputEditAfiliado").val(d.AFILIADO);

                $("#modalEditColaborador").modal("show");
            } else {
                Swal.fire("Error", response.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo cargar el legajo", "error");
        },
    });
}

$("#formEditEmpleado").on("submit", function (e) {
    e.preventDefault();

    const legajo = $("#edit_legajo").val();

    $.ajax({
        url: `/personal/${legajo}`,
        type: "POST",
        data: $(this).serialize(),
        success: function () {
            alert("Empleado actualizado correctamente");
            $("#modalEditEmpleado").modal("hide");
            tablaPersonal.ajax.reload(null, false);
        },
        error: function () {
            alert("Error al actualizar empleado");
        },
    });
});

function cerrarEdicionEmpleado() {
    $("#formEditColaborador")[0].reset();
    $("#modalEditColaborador").modal("hide");
    $(".modal-backdrop").remove();
}

//sp para cargar selectores filtros
$(document).ready(function () {
    cargarAreas();
    cargarFiltroCateg();
    cargarFiltroConvenio();
    cargarFiltroRegimen();
    cargarFiltroEstados();
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

function cargarFiltroCateg() {
    $.ajax({
        url: "/categorias-empleados/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-categFiltro");

            select.empty();
            select.append('<option value="">Seleccione categoría</option>');

            data.forEach((categoria) => {
                select.append(`
                    <option value="${categoria.id_categ}">
                        ${categoria.nombre}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando categorías");
        },
    });
}

function cargarFiltroConvenio() {
    $.ajax({
        url: "/convenios/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-convenioFiltro");

            select.empty();
            select.append('<option value="">Seleccione convenio</option>');

            data.forEach((convenio) => {
                select.append(`
                    <option value="${convenio.convenio}">
                        ${convenio.convenio}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando categorías");
        },
    });
}

function cargarFiltroRegimen() {
    $.ajax({
        url: "/regimenes/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-regFiltro");

            select.empty();
            select.append('<option value="">Seleccione régimen</option>');

            data.forEach((regimen) => {
                select.append(`
                    <option value="${regimen.regimen}">
                        ${regimen.regimen}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando categorías");
        },
    });
}

function cargarFiltroEstados() {
    $.ajax({
        url: "/estados/lista",
        method: "GET",
        success: function (data) {
            const select = $(".js-select-estadoFiltro");

            select.empty();
            select.append('<option value="">Seleccione estado</option>');

            data.forEach((estado) => {
                select.append(`
                    <option value="${estado.estado}">
                        ${estado.estado}
                    </option>
                `);
            });
        },
        error: function () {
            console.error("Error cargando estado");
        },
    });
}

//carga dt personal
$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        tablaPersonal = $("#tb_personal").DataTable({
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

                    d.categ_id = $("#filtroCategoria").val() || null;
                    d.p_regimen = $("#filtroRegimen").val() || null;
                    d.p_convenio = $("#filtroConvenio").val() || null;
                },
            },
            autoWidth: false,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: getScrollY(),
            responsive: true,
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 0 },
                { responsivePriority: 3, targets: 2 },
                { responsivePriority: 4, targets: 9 },
                { responsivePriority: 100, targets: 8 },
                { responsivePriority: 100, targets: 3 },
                { responsivePriority: 100, targets: 4 },
                { responsivePriority: 100, targets: 5 },
                { responsivePriority: 100, targets: 6 },
                { responsivePriority: 100, targets: 7 },
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            columns: [
                {
                    data: "LEGAJO",
                    width: "5%",
                    className: "text-start",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "COLABORADOR", width: "auto", className: "text-start" },
                { data: "DNI", width: "5%", className: "text-start" },
                { data: "AREA", width: "12%", className: "text-start" },
                { data: "CATEGORIA", width: "9%", className: "text-start" },
                { data: "REGIMEN", width: "5%", className: "text-center" },
                {
                    data: "HORAS_DIARIAS",
                    width: "3%",
                    className: "text-center",
                },
                { data: "CONVENIO", width: "10%", className: "text-start" },
                {
                    data: "ESTADO",
                    width: "3%",
                    orderable: false,
                    className: "text-center",
                    render: function (data) {
                        let clase =
                            data === "ACTIVO" ? "bg-success" : "bg-danger";

                        return `<span class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    className: "text-center acciones-nowrap",
                    width: "10%",
                    orderable: false,
                    render: function (data) {
                        let botones = `
                            <button 
                                class="btn btn-secondary btn-VerLegajo"
                                data-id="${data.LEGAJO}"
                                title='Ver legajo'
                                data-nombre="${data.COLABORADOR}">
                                <i class="fa-solid fa-address-card"></i>
                            </button>
                        `;

                        // SOLO ADMIN
                        if (
                            USER_ROLE === "Administrador/a" &&
                            data.ESTADO === "ACTIVO"
                        ) {
                            botones += `
                                <button 
                                    class="btn btn-danger btn-DarBaja"
                                    data-id="${data.LEGAJO}"
                                    title='Dar de baja'
                                    data-nombre="${data.COLABORADOR}">
                                    <i class="fa-solid fa-square-xmark"></i>
                                </button>
                            `;
                        }

                        // ADMIN + COORDINADOR
                        if (
                            (USER_ROLE === "Administrador/a" ||
                                USER_ROLE === "Coordinador/a" ||
                                USER_ROLE === "Coordinador/a L2") &&
                            data.ESTADO === "ACTIVO"
                        ) {
                            botones += `
                            <button 
                                class="btn btn-primary btn-RegNovedad"
                                title='Cargar novedad'
                                data-id="${data.LEGAJO}">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        `;
                        }

                        return botones;
                    },
                },
            ],
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2' \
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
                    exportOptions: { columns: ":visible" },
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
                    title: "Nómina de personal dt-buttons",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });

        $(
            "#filtroArea, #filtroCategoria, #filtroRegimen, #filtroConvenio, #filtroEstado",
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
            const nombre = $(this).data("nombre");
            console.log("Id recibido: " + legajoColaborador);
            darDeBaja(legajoColaborador, nombre);
        });

        $(document).on("click", ".btn-RegNovedad", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            console.log("Id recibido: " + legajoColaborador);
            registrarNovedad(legajoColaborador);
        });

        $(document).on("click", ".btn-Editar", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            const nombre = $(this).data("nombre");
            console.log("Id recibido: " + legajoColaborador);
            editEmpleados(legajoColaborador, nombre);
        });
    }
});

//sp para cargar selector categorias
$(document).ready(function () {
    cargarCategorias();
});

function cargarCategorias() {
    $.ajax({
        url: "/categorias-empleados/lista",
        type: "GET",
        success: function (data) {
            $(".js-select-categoria").each(function () {
                const select = $(this);
                const valorActual = select.val();

                select.empty();
                select.append('<option value="">Seleccione categoría</option>');

                data.forEach((cat) => {
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

//sp para cargar selector de roles
$(document).on("change", ".js-select-categoria", function () {
    const idCategoria = $(this).val();
    const selectRol = $(".js-select-rol");

    // reset del rol
    selectRol
        .empty()
        .append('<option value="">Seleccione rol</option>')
        .prop("disabled", true);

    if (!idCategoria) return;

    $.ajax({
        url: `/roles-empleados/por-categoria/${idCategoria}`,
        method: "GET",
        success: function (data) {
            data.forEach((rol) => {
                selectRol.append(
                    `<option value="${rol.id_rol}">
                        ${rol.nombre}
                    </option>`,
                );
            });

            selectRol.prop("disabled", false);
        },
        error: function (err) {
            console.error("Error cargando roles", err);
        },
    });
});

//sp para cargar selector de servicios
$(document).on("change", ".js-select-area", function () {
    const idArea = $(this).val();
    const selectServicio = $(".js-select-servicio");

    selectServicio
        .empty()
        .append('<option value="">Seleccione servicio</option>')
        .prop("disabled", true);

    if (!idArea) return;

    $.ajax({
        url: `/servicios-empleados/por-area/${idArea}`,
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

//selectores de os y codigo
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

//mapa de valores para regimen y hs diarias
document.addEventListener("DOMContentLoaded", () => {
    const selectRegimen = document.getElementById("selectRegimen");
    const inputHoras = document.getElementById("horasDiarias");

    const equivalencias = {
        44: 8,
        40: 7.28,
        32: 6.24,
        24: 4.8,
        35: 7,
        30: 6,
        22: 4.4,
        20: 4,
    };

    selectRegimen.addEventListener("change", function () {
        const valor = this.value;

        if (equivalencias[valor]) {
            inputHoras.value = equivalencias[valor];
        } else {
            inputHoras.value = "";
        }
    });
});

//alta de colaborador
function abrirModal() {
    $("#modalAltaColaborador").modal("show");
}

function cerrarModal() {
    $("#selectLocalidad").val(null).trigger("change");
    $("#formAltaColaborador")[0].reset();
    $("#modalAltaColaborador").modal("hide");
}

$("#formAltaColaborador").on("submit", function (e) {
    e.preventDefault();

    let formData = $(this).serialize();

    $.ajax({
        url: "/personal/guardar",
        type: "POST",
        data: formData,
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

                $("#formAltaColaborador")[0].reset();
                $("#modalAltaColaborador").modal("hide");
                tablaPersonal.ajax.reload(null, false);
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: response.mensaje,
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al guardar el colaborador",
            });
            console.error(xhr.responseText);
        },
    });
});

//legajo del colaborador
function abrirModalLegajo() {
    $("#modalLegajoColaborador").modal("show");
}

function cerrarModalLegajo() {
    $("#modalLegajoColaborador").modal("hide");
}

$("#btnAgregarTitulo").on("click", function () {
    $("#divTitulos").removeAttr("hidden");

    const html = `
        <div class="row d-flex">
            <div class="col-lg-6">
                <label class="form-label">Descripción de Título</label>
                <input type="text" name="titulo_descripcion[]" 
                    class="form-control">
            </div>

            <div class="col-lg-2">
                <label class="form-label">M.P.</label>
                <input type="text" name="matricula_profesional[]" 
                    class="form-control">
            </div>

            <div class="col-lg-2 d-flex align-items-end">
                <button type="button" class="btnQuitarTitulo btn-peligro">
                    <i class="fa-solid fa-x"></i>
                </button>
            </div>
        </div>
    `;

    $("#divTitulos").append(html);
});

$(document).on("click", ".btnQuitarTitulo", function () {
    $(this).closest(".col-lg-2").prev().prev().remove();
    $(this).closest(".col-lg-2").prev().remove();
    $(this).closest(".col-lg-2").remove();
});

$("#btnAgregarHijo").on("click", function (e) {
    e.preventDefault();
    $("#divHijos").removeAttr("hidden");

    // Es importante usar el nombre hijos[] para que Laravel lo reciba como un array
    const html = `
        <div class="row d-flex mb-2 fila-hijo"> 
            <div class="col-lg-4">
                <label class="form-label">Nombre y Apellido</label>
                <input type="text" name="hijos[0][nombre]" class="form-control" required>
            </div>
            <div class="col-lg-3">
                <label class="form-label">DNI</label>
                <input type="text" name="hijos[0][dni]" class="form-control">
            </div>
            <div class="col-lg-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btnQuitarHijo">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    $("#divHijos").append(html);
});

$("#btnAgregarHijoEdit").on("click", function (e) {
    e.preventDefault();
    $("#divHijosEdit").removeAttr("hidden");

    // Es importante usar el nombre hijos[] para que Laravel lo reciba como un array
    const html = `
        <div class="row d-flex mb-2 fila-hijo"> 
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

//  eliminar la fila agregada
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
        minimumInputLength: 3, // Subí a 3 para evitar resultados demasiado genéricos
        width: "100%",
        dropdownParent: $("#modalAltaColaborador"),
        ajax: {
            url: "/geo/localidades",
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
            url: "/geo/localidades",
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

$("#btnAbrirModalOs").on("click", function () {
    const modal = $("#modalNuevaOs");
    modal.modal("show");
});

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

function cerrarModalOs() {
    $("#formNuevaOs")[0].reset();
    $("#modalNuevaOs").modal("hide");
}

//selectores de os y codigo para edición
$(document).ready(function () {
    const $select = $("#selectObraSocialEdit");

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
                dropdownParent: $("#modalLegajoColaborador"),
            });
        },
        error: function (err) {
            console.error("Error cargando obras sociales:", err);
        },
    });
});

$("#selectObraSocialEdit").on("select2:select", function (e) {
    const data = e.params.data;
    $("#codigoOS").val(data.codigo ?? "");
});

$("#selectObraSocialEdit").on("select2:clear", function () {
    $("#codigoOS").val("");
});

function cargarServiciosSimple(selected = null) {
    const idArea = $("#inputArea").val();
    const $select = $("#inputServicio");

    $select.empty().append('<option value="">Cargando...</option>');

    if (!idArea) {
        $select.html('<option value="">Seleccione servicio</option>');
        return;
    }

    $.ajax({
        url: `/servicios-empleados/por-area/${idArea}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select
                .empty()
                .append('<option value="">Seleccione servicio</option>');

            data.forEach((servicio) => {
                $select.append(`
                    <option value="${servicio.id_servicios}">
                        ${servicio.servicio}
                    </option>
                `);
            });

            // 🔥 marcar seleccionado
            if (selected) {
                $select.val(selected).trigger("change");
            }
        },
        error: function () {
            $select.html("<option>Error al cargar</option>");
        },
    });
}

function cargarRolesSimple(selected = null) {
    const idCategoria = $("#inputCategoria").val();
    const $select = $("#inputRol");

    $select.empty().append('<option value="">Cargando...</option>');

    if (!idCategoria) {
        $select.html('<option value="">Seleccione rol</option>');
        return;
    }

    $.ajax({
        url: `/roles-empleados/por-categoria/${idCategoria}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.empty().append('<option value="">Seleccione rol</option>');

            data.forEach((rol) => {
                $select.append(`
                    <option value="${rol.id_rol}">
                        ${rol.nombre}
                    </option>
                `);
            });

            // 🔥 marcar seleccionado
            if (selected) {
                $select.val(selected).trigger("change");
            }
        },
        error: function () {
            $select.html("<option>Error al cargar</option>");
        },
    });
}

$("#formEditColaborador").on("submit", function (e) {
    e.preventDefault();

    const legajo = $("#inputLegajo").val();

    $.ajax({
        url: `/personal/${legajo}/actualizar`,
        type: "POST",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {

            if (resp.success) {
                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    text: resp.mensaje
                });
            } else {
                Swal.fire("Atención", resp.mensaje, "warning");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire("Error", "No se pudo actualizar el legajo", "error");
        }
    });
});