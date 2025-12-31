console.log("nominaPersonal.js cargado");

let tablaPersonal;

//calculo edad
function calcularEdad(fecha) {
    if (!fecha) return '';

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
    if (!fecha) return '';

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

    let texto = '';

    if (años > 0) {
        texto += `${años} año${años > 1 ? 's' : ''}`;
    }

    if (meses > 0) {
        texto += (texto ? ' ' : '') + `${meses} mes${meses > 1 ? 'es' : ''}`;
    }

    return texto || '0 meses';
}

//formato fecha arg
function formatearFechaArgentina(fecha) {
    if (!fecha) return '';

    const partes = fecha.split('-'); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}-${partes[1]}-${partes[0]}`;
}


document.addEventListener("DOMContentLoaded", () => {
    const tipoContrato = document.getElementById("tipoContrato");
    const fechaInicio = document.getElementById("fechaInicio");
    const fechaFin = document.getElementById("fechaFin");

    if (!tipoContrato || !fechaInicio || !fechaFin) return;

    function calcularFechaFin() {
        const tipo = tipoContrato.value;
        const inicio = fechaInicio.value;

        if (!inicio) {
            fechaFin.value = "";
            return;
        }

        const fecha = new Date(inicio);

        if (tipo === "Plazo fijo") {
            fecha.setMonth(fecha.getMonth() + 6);
            fechaFin.value = fecha.toISOString().split("T")[0];
        } else if (tipo === "Pasantía" || tipo === "Práctica profesional") {
            fecha.setMonth(fecha.getMonth() + 3);
            fechaFin.value = fecha.toISOString().split("T")[0];
        } else {
            fechaFin.value = "";
        }
    }

    tipoContrato.addEventListener("change", calcularFechaFin);
    fechaInicio.addEventListener("change", calcularFechaFin);

    fechaFin.addEventListener("change", () => {
        if (!fechaInicio.value || !fechaFin.value) return;

        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);

        if (fin < inicio) {
            alert(
                "La fecha de fin no puede ser anterior a la fecha de inicio."
            );
            fechaFin.value = "";
        }
    });
});

$(document).ready(function () {
    // Escuchar el evento 'change' del selector de título
    $("#select-titulo").on("change", function () {
        const valor = $(this).val();
        const inputs = $("#input-descripcion, #input-mp");

        if (valor === "Si") {
            // Habilitar inputs y limpiar si es necesario
            inputs.prop("disabled", false);
        } else {
            // Deshabilitar y limpiar el contenido si eligen "No" o vacío
            inputs.prop("disabled", true).val("");
        }
    });
});

//ver legajo
function verLegajo(legajoColaborador, nombre) {
    console.log("Mostrando legajo:", legajoColaborador);

    $('#tituloLegajo').html(`
        <i class="fa-solid fa-id-card me-2"></i>
        Legajo de <strong>${nombre}</strong>
    `);

    $.ajax({
        url: `/personal/ver-legajo/${legajoColaborador}`,
        type: 'GET',
        success: function (response) {
            if (response.success) {

                const d = response.data;

                // Datos personales
                $('#inputLegajo').val(d.LEGAJO);
                $('#inputNombre').val(d.COLABORADOR);
                $('#inputEstado').val(d.ESTADO);
                $('#inputDni').val(d.DNI);
                $('#inputCuil').val(d.CUIL);
                $('#inputFechaNacimiento').val(formatearFechaArgentina(d.FECHA_NAC));
                $('#inputEdad').val(calcularEdad(d.FECHA_NAC));

                // Contacto
                $('#inputEmail').val(d.CORREO);
                $('#inputTelefono').val(d.TELEFONO);
                $('#inputDomicilio').val(d.DOMICILIO);
                $('#inputLocalidad').val(d.LOCALIDAD);

                // Socioeconómicos
                $('#inputEstadoCivil').val(d.ESTADO_CIVIL);
                $('#inputGenero').val(d.GENERO);
                $('#inputObraSocial').val(d.OBRA_SOCIAL);
                $('#inputCodigoOS').val(d.COD_OS);
                $('#inputTitulo').val(d.TITULO);
                $('#inputDescripTitulo').val(d.DESCRIP_TITULO);
                $('#inputMatricula').val(d.MAT_PROF);

                // Laborales
                $('#inputTipoContrato').val(d.TIPO_CONTRATO);
                $('#inputFechaIngreso').val(formatearFechaArgentina(d.FECHA_INGRESO));
                $('#inputFechaFinPrueba').val(formatearFechaArgentina(d.FECHA_FIN_PRUEBA));
                $('#inputAntiguedad').val(calcularAntiguedad(d.FECHA_INGRESO));
                $('#inputFechaEgreso').val(d.FECHA_EGRESO);
                $('#inputArea').val(d.AREA);
                $('#inputServicio').val(d.SERVICIO);
                $('#inputConvenio').val(d.CONVENIO);
                $('#inputCategoria').val(d.CATEGORIA);
                $('#inputRol').val(d.ROL);
                $('#inputRegimen').val(d.REGIMEN);
                $('#inputHorasDiarias').val(d.HORAS_DIARIAS);
                $('#inputCordinador').val(d.COORDINADOR);
                $('#inputAfiliado').val(d.AFILIADO);

                $("#modalLegajoColaborador").modal("show");

            } else {
                Swal.fire("Error", response.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo cargar el legajo", "error");
        }
    });
}

//dar de baja empleado
function darDeBaja(legajoColaborador) {
    Swal.fire({
        title: '¿Dar de baja?',
        text: 'El empleado quedará inactivo',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/personal/baja/${legajoColaborador}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    Swal.fire('OK', res.mensaje, 'success');
                    tablaPersonal.ajax.reload(null, false);
                }
            });
        }
    });
    tablaPersonal.ajax.reload();
}

//carga dt personal
$(document).ready(function () {
    tablaPersonal = $("#tb_personal");

    if (tablaPersonal.length > 0) {
        let dt = new DataTable("#tb_personal", {
            ajax: {
                url: "/personal/listar",
                type: "GET",
                data: function (d) {
                    d.area_id = $("#area").val();
                },
            },
            paging: true,
            paginate: {
                first: "",
                last: "",
                next: "",
                previous: "",
            },
            columns: [
                { data: "LEGAJO" },
                { data: "COLABORADOR" },
                { data: "DNI" },
                { data: "ANTIGUEDAD" },
                { data: "AREA" },
                { data: "CATEGORIA" },
                { data: "REGIMEN" },
                { data: "HORAS_DIARIAS" },
                { data: "CONVENIO" },
                {
                    data: "ESTADO",
                    render: function (data) {
                        let clase =
                            data === "Activo" ? "bg-success" : "bg-danger";
                        return `<span style="font-size: 0.80rem;" class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    render: function (data) {
                        return `
                        <button 
                            class="btn-secundario btn-VerLegajo"
                            data-id="${data.LEGAJO}" data-nombre="${data.COLABORADOR}">                            
                            <i class="fa-solid fa-eye"></i> Legajo
                        </button>
                        <button 
                            class="btn-peligro btn-DarBaja"
                            data-id="${data.LEGAJO}">
                            <i class="fa-solid fa-x"></i> Baja
                        </button>
                        <button 
                            class="btn-alerta btn-DarBaja"
                            data-id="${data.LEGAJO}">
                            <i class="fa-solid fa-pencil"></i> Editar
                        </button>
                        <button 
                            class="btn-primario btn-DarBaja"
                            data-id="${data.LEGAJO}">
                            <i class="fa-solid fa-floppy-disk"></i> Novedad
                        </button>
                    `;
                    },
                },
            ],
            scrollX: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            paging: false,
            autoWidth: false,
            dom: "<'dt-top d-flex align-items-center justify-content-between'Bf>rt<'dt-bottom'p>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: "Exportar Excel",
                    className: "btn-export-excel",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "pdfHtml5",
                    text: "Exportar PDF",
                    className: "btn-export-pdf",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "print",
                    text: "Imprimir",
                    title: "Productos en sucursal",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });

        $("#area").on("change", function () {
            dt.ajax.reload();
        });

        setTimeout(function () {
            if ($("#area").val() !== "" && $("#area").val() !== null) {
                dt.ajax.reload();
            }
        }, 500);

        $(document).on('click', '.btn-VerLegajo', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data('id');
            const nombre = $(this).data('nombre');
            console.log("Legajo recibido: " + legajoColaborador);
            verLegajo(legajoColaborador, nombre);
        });

        $(document).on('click', '.btn-DarBaja', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data('id');
            console.log("Id recibido: " + legajoColaborador);
            darDeBaja(legajoColaborador);
        });
    }
});

function previewImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById("previewFoto").src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

//sp para cargar selector areas
$(document).ready(function () {
    cargarAreas();
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
                        </option>`
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
                    </option>`
                );
            });

            selectRol.prop("disabled", false);
        },
        error: function (err) {
            console.error("Error cargando roles", err);
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

function abrirModalLegajo() {
    $("#modalLegajoColaborador").modal("show");
}

function cerrarModalLegajo() {
    $("#modalLegajoColaborador").modal("hide");
}