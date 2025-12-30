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
                    option.value = item.CODIGO_NOVEDAD;
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

    function enviarNovedad() {
        let form = $("#formCargaNovedad");

        $.ajax({
            url: $("#formCargaNovedad").attr("action"),
            method: "POST",
            data: form.serialize(),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $('button[type="submit"]')
                    .prop("disabled", true)
                    .html(
                        '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...'
                    );
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: response.message,
                }).then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                $('button[type="submit"]')
                    .prop("disabled", false)
                    .html(
                        '<i class="fa-solid fa-floppy-disk me-1"></i> Registrar novedad'
                    );
                let msg = xhr.responseJSON
                    ? xhr.responseJSON.message
                    : "Error al procesar la solicitud";
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: msg,
                });
            },
        });
    }
});
