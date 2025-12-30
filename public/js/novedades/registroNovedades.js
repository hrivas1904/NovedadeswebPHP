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

//sp para cargar selector de colaboradores
$(document).ready(function () {
    cargarColaboradores();
    $('#area').on('change', function () {
        cargarColaboradores();
    });
});

function cargarColaboradores() {
    $.ajax({
        url: "/personal/listar",
        type: "GET",
        data: {
            area_id: $("#area").val()
        },
        success: function (data) {
            const select = $(".js-selector-colaborador");
            const areaFija = $("#area").val();

            select.empty();
            select.append('<option value="">Seleccione colaborador</option>');

            data.forEach((empleado) => {
                select.append(`
                    <option value="${empleado.legajo}">
                        ${empleado.colaborador}
                    </option>
                `);
            });

            // Si el área está fija, bloquea el selector
            if (areaFija) {
                select.prop("disabled", false); // 👈 dejalo seleccionable
            } else {
                select.prop("disabled", false);
            }
        },
        error: function () {
            console.error("Error cargando colaboradores");
        },
    });
}

function calcularDuracion() {
    const fechaDesdeInput = document.querySelector('[name="fechaDesde"]');
    const fechaHastaInput = document.querySelector('[name="fechaHasta"]');
    const duracionInput = document.querySelector('[name="duracion"]');

    if (!fechaDesdeInput.value || !fechaHastaInput.value) {
        duracionInput.value = "";
        return;
    }

    const fechaDesde = new Date(fechaDesdeInput.value);
    const fechaHasta = new Date(fechaHastaInput.value);

    // Validación: fecha hasta no puede ser menor
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

    // Cálculo de días (incluye mismo día)
    const diffTime = fechaHasta.getTime() - fechaDesde.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

    duracionInput.value = diffDays;
}

document.addEventListener("DOMContentLoaded", () => {
    const selectCategoria = document.getElementById("selectCategoria");
    const selectNovedad = document.getElementById("selectNovedad");
    const inputCodigo = document.getElementById("codigoFinnegans");
    const idNovedad = document.getElementById("idNovedad");

    cargarCategorias();

    selectCategoria.addEventListener("change", () => {
        const idCategoria = selectCategoria.value;

        selectNovedad.innerHTML =
            '<option value="">Seleccionar novedad</option>';
        selectNovedad.disabled = true;
        inputCodigo.value = "";

        if (!idCategoria) return;

        fetch(`/novedades/lista/${idCategoria}`)
            .then((res) => res.json())
            .then((data) => {
                if (!Array.isArray(data)) return;

                data.forEach((item) => {
                    const option = document.createElement("option");
                    option.value = item.ID_NOVEDAD;
                    option.textContent = item.NOMBRE;
                    option.dataset.codigo = item.CODIGO_NOVEDAD;

                    selectNovedad.appendChild(option);
                });

                selectNovedad.disabled = false;
            });
    });

    selectNovedad.addEventListener("change", () => {
        const selected = selectNovedad.options[selectNovedad.selectedIndex];
        inputCodigo.value = selected.dataset.codigo || "";
        console.log(idNovedad.value());
    });
});

function cargarCategorias() {
    fetch("/categorias-novedad/lista")
        .then((res) => res.json())
        .then((data) => {
            const select = document.getElementById("selectCategoria");

            data.forEach((cat) => {
                const option = document.createElement("option");
                option.value = cat.ID_CATEG;
                option.textContent = cat.NOMBRE;
                select.appendChild(option);
            });
        });
}

$(document).ready(function () {
    $("#formCargaNovedad").on("submit", function (e) {
        e.preventDefault();

        // Confirmación antes de guardar
        Swal.fire({
            title: "¿Registrar novedad?",
            text:
                "Se cargará la novedad para el legajo " +
                $('input[name="legajo"]').val(),
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, registrar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                enviarNovedad();
            }
        });
    });

    $('#btnRegistrarNovedad').on('click', function () {

    const form = $('#formCargaNovedad');

    $.ajax({
        url: '/novedades/guardar',
        type: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {

            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Correcto',
                    text: response.mensaje
                });

                form[0].reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje
                });
            }
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar la novedad'
            });
        }
    });
