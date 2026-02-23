console.log("nominaPersonal.js cargado");

let tablaPersonal;

let tablaHistorialNovedades = null;
let legajoActivo = null;

let registroSeleccionado = null;

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

//función para tomar el último día del mes
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
                        <button type="button" class="btn-terciario btn-subir-archivo" data-registro="${data}">
                            <i class="fa-solid fa-upload"></i> Subir
                        </button>
                        <button type="button"
                            class="btn-secundario btn-ver-archivo"
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
        dom: "<'d-top d-flex align-items-center gap-2 mt-2 mx-2'B<'d-flex ms-auto'f>><'m-2'rt><'d-bottom d-flex align-items-center justify-content-between mx-2 mb-2'>",
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
                $("#inputLocalidad").val(d.LOCALIDAD);

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
        cancelButtonText: "Cancelar",
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

$('#select-titulo').on('change',function(){
    const modal=$('#divTitulo');
    const modal2=$('#divMatricula');
    if ($(this).val()==="SI"){
        modal.removeClass('d-none');
        modal2.removeClass('d-none');
    } else {
        modal.addClass('d-none');
        modal2.addClass('d-none');
    }
});

//carga novedad
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
                $("#osEdit").val(d.ID_OS);
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
                    if (USER_ROLE !== "Administrador/a") {
                        d.area_id = USER_AREA_ID;
                    } else {
                        d.area_id = $("#filtroArea").val() || null;
                    }

                    d.categ_id = $("#filtroCategoria").val() || null;
                    d.p_regimen = $("#filtroRegimen").val() || null;
                    d.p_convenio = $("#filtroConvenio").val() || null;
                },
            },
            autoWidth: true,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: "60vh",
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
                { data: "CATEGORIA", width: "4%", className: "text-start" },
                { data: "REGIMEN", width: "6", className: "text-center" },
                {
                    data: "HORAS_DIARIAS",
                    width: "2%",
                    className: "text-center",
                    width: "auto",
                },
                { data: "CONVENIO", width: "2%", className: "text-start" },
                {
                    data: "ESTADO",
                    width: "3%",
                    orderable: false,
                    className: "text-center",
                    render: function (data) {
                        let clase =
                            data === "ACTIVO" ? "bg-success" : "bg-danger";
                        return `<span style="font-size: 0.80rem;" class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    className: "text-center",
                    width: "auto",
                    orderable: false,
                    render: function (data) {
                        let botones = `
                            <button 
                                class="btn-secundario btn-VerLegajo"
                                data-id="${data.LEGAJO}"
                                title='Ver legajo'
                                data-nombre="${data.COLABORADOR}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        `;

                        // SOLO ADMIN
                        if (
                            USER_ROLE === "Administrador/a" &&
                            data.ESTADO === "ACTIVO"
                        ) {
                            botones += `
                            <button 
                                class="btn-peligro btn-DarBaja"
                                data-id="${data.LEGAJO}"
                                title='Dar de baja'
                                data-nombre="${data.COLABORADOR}">
                                <i class="fa-solid fa-x"></i>
                            </button>
                            
                            <button 
                                class="btn-alerta btn-Editar d-none"
                                title='Editar'
                                data-id="${data.LEGAJO}"
                                data-nombre="${data.COLABORADOR}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        `;
                        }

                        // ADMIN + COORDINADOR
                        if (
                            (USER_ROLE === "Administrador/a" ||
                                USER_ROLE === "Coordinador/a") &&
                            data.ESTADO === "ACTIVO"
                        ) {
                            botones += `
                            <button 
                                class="btn-primario btn-RegNovedad"
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
            dom: "<'d-top d-flex align-items-center gap-2 mt-1'B<'d-flex ms-auto'f>><'my-2'rt><'d-bottom d-flex align-items-center justify-content-center'i>",
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
                    title: "Nómina de personal",
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
document.addEventListener("DOMContentLoaded", () => {
    cargarObrasSociales();

    const select = document.getElementById("obraSocial");
    const inputCodigo = document.getElementById("codigoOS");

    select.addEventListener("change", function () {
        const selected = this.options[this.selectedIndex];
        const codigo = selected.getAttribute("data-codigo");

        inputCodigo.value = codigo ? codigo : "";
    });
});

function cargarObrasSociales() {
    fetch("/obra-social/lista")
        .then((response) => response.json())
        .then((data) => {
            const select = document.getElementById("obraSocial");

            data.forEach((item) => {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.nombre;
                option.setAttribute("data-codigo", item.codigo);

                select.appendChild(option);
            });
        })
        .catch((error) => {
            console.error("Error cargando obras sociales:", error);
        });
}

document.addEventListener("DOMContentLoaded", () => {
    cargarObrasSocialesEdit();

    const select = document.getElementById("osEdit");
    const inputCodigo = document.getElementById("inputEditCodigoOS");

    select.addEventListener("change", function () {
        const selected = this.options[this.selectedIndex];
        const codigo = selected.getAttribute("data-codigo");

        inputCodigo.value = codigo ? codigo : "";
    });
});

function cargarObrasSocialesEdit() {
    fetch("/obra-social/lista")
        .then((response) => response.json())
        .then((data) => {
            const select = document.getElementById("osEdit");

            data.forEach((item) => {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.nombre;
                option.setAttribute("data-codigo", item.codigo);

                select.appendChild(option);
            });
        })
        .catch((error) => {
            console.error("Error cargando obras sociales:", error);
        });
}

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
                tablaPersonal.ajax.reload();
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

//#########REGISTRO DE NOVEDADES#########//
function abrirModalNovedad() {
    $("#modalRegNovedadColaborador").modal("show");
}

function cerrarModalNovedad() {
    $("#formCargaNovedad")[0].reset();
    $("#selectNovedad").val(null).trigger("change");
    $("#modalRegNovedadColaborador").modal("hide");
}

//calculo periodo de novedad
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

//selector de categorías de novedades de sueldo
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

function calcularDuracion() {
    const fechaDesdeInput = document.getElementById("fechaDesdeNovedad");
    const fechaHastaInput = document.getElementById("fechaHastaNovedad");
    const duracionInput = document.getElementById("inputDias");

    if (!fechaDesdeInput || !fechaHastaInput || !duracionInput) {
        console.error("No se encontraron los inputs de fechas o duración");
        return;
    }

    if (!fechaDesdeInput.value || !fechaHastaInput.value) {
        duracionInput.value = "";
        return;
    }

    const fechaDesde = new Date(fechaDesdeInput.value);
    const fechaHasta = new Date(fechaHastaInput.value);

    if (fechaHasta < fechaDesde) {
        Swal.fire({
            icon: "warning",
            title: "Fecha incorrecta",
            text: "La fecha hasta no puede ser anterior a la fecha desde.",
        });

        fechaHastaInput.value = "";
        duracionInput.value = "";
        return;
    }

    const diffTime = fechaHasta - fechaDesde;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

    duracionInput.value = diffDays;
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

$("#formCargaNovedad").on("submit", function (e) {
    e.preventDefault();

    const dias = document.getElementById("inputDias").value.trim();
    const horas = document.getElementById("inputHoras").value.trim();
    const pesosRaw = document.getElementById("inputImporte").value.trim();

    let valorFinal = null;

    if (pesosRaw !== "") {
        valorFinal = parsearMonto(pesosRaw);
    } else if (horas !== "") {
        valorFinal = horas;
    } else if (dias !== "") {
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
                $('#selectNovedad').val(null).trigger('change');
                $("#modalRegNovedadColaborador").modal("hide");

                // 🔑 refrescar historial SIN recrear
                if (tablaHistorialNovedades) {
                    tablaHistorialNovedades.ajax.reload(null, false);
                }

                // refrescar tabla principal si existe
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

document.addEventListener("DOMContentLoaded", () => {
    const fechaInicio = document.getElementById("fechaDesdeNovedad");
    const fechaFin = document.getElementById("fechaHastaNovedad");
    let novedadSeleccionada = null;

    // Escuchar Select2
    $("#selectNovedad").on("select2:select", function (e) {
        novedadSeleccionada = e.params.data;
        calcularFechaFin();
    });

    fechaInicio.addEventListener("change", calcularFechaFin);

    function calcularFechaFin() {
        if (
            !fechaInicio.value ||
            !novedadSeleccionada ||
            isNaN(novedadSeleccionada.limite)
        )
            return;

        const limiteLicencia = parseInt(novedadSeleccionada.limite);

        if (isNaN(limiteLicencia)) {
            console.log("La novedad no tiene límite definido (es NULL).");
            fechaFin.value = "";
            console.log(fechaFin);
        }

        let fecha = new Date(fechaInicio.value);

        fecha.setDate(fecha.getDate() + limiteLicencia);

        // Formatear manualmente para el input date (YYYY-MM-DD)
        const yyyy = fecha.getFullYear();
        const mm = String(fecha.getMonth() + 1).padStart(2, "0");
        const dd = String(fecha.getDate()).padStart(2, "0");

        fechaFin.value = `${yyyy}-${mm}-${dd}`;
    }

    fechaFin.addEventListener("change", () => {
        if (!fechaInicio.value || !fechaFin.value) return;

        // Para comparar, también usamos el desglose manual para ser precisos
        const d1 = new Date(fechaInicio.value.replace(/-/g, "\/"));
        const d2 = new Date(fechaFin.value.replace(/-/g, "\/"));

        if (d2 < d1) {
            alert(
                "La fecha de fin no puede ser anterior a la fecha de inicio.",
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

//  eliminar la fila agregada
$(document).on("click", ".btnQuitarHijo", function() {
    $(this).closest(".fila-hijo").remove();
});