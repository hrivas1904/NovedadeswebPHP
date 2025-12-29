console.log("nominaPersonal.js cargado");

document.addEventListener("DOMContentLoaded", () => {
    const inputFecha = document.getElementById("fechaNacimiento");
    const inputEdad = document.getElementById("edad");

    if (!inputFecha || !inputEdad) return;

    inputFecha.addEventListener("change", () => {
        const fechaNac = new Date(inputFecha.value);
        if (isNaN(fechaNac)) {
            inputEdad.value = "";
            return;
        }

        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();

        const mes = hoy.getMonth() - fechaNac.getMonth();
        const dia = hoy.getDate() - fechaNac.getDate();

        if (mes < 0 || (mes === 0 && dia < 0)) {
            edad--;
        }

        inputEdad.value = edad >= 0 ? edad : "";
    });
});

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

$(document).ready(function () {
    let tablaPersonal = $("#tb_personal");

    if (tablaPersonal.length > 0) {
        let dt = new DataTable("#tb_personal", {
            ajax: {
                url: "/personal/listar",
                type: "GET",
                data: function (d) {
                    // Al usar el ID directamente aquí, evitamos el error de definición
                    d.area_id = $("#area").val();
                },
            },
            paging: true,
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
                    render: function () {
                        return `
                        <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i>Ver legajo</button>
                        <button class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pen"></i>Editar</button>
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

                $('#tb_personal').ajax.reload();
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
