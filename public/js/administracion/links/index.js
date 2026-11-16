function escaparHTML(valor) {
    return String(valor ?? "").replace(/[&<>"']/g, function (caracter) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
        }[caracter];
    });
}

function cargarLinksImportacion() {
    console.log("Iniciando carga de links...");
    console.log("Estado seleccionado:", $("#selectorEstado").val());
    $.ajax({
        url: "/administracion/links/obtenerLinksImportacion",
        type: "GET",
        dataType: "JSON",
        data: { p_estado: $("#selectorEstado").val() },

        success: function (res) {
            let html = "";

            if (!res.data || res.data.length === 0) {
                $("#contenedorLinks").html(`
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-link-slash fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">
                            No hay links de importación disponibles.
                        </h5>
                    </div>
                `);
                return;
            }

            $.each(res.data, function (index, item) {
                let claseBadge = "";
                let textoEstado = "";
                let claseMensaje = "";

                switch (item.estado) {
                    case "NO_DISPONIBLE":
                        claseBadge = "bg-secondary";
                        textoEstado = "No disponible";
                        claseMensaje =
                            "bg-secondary-subtle text-secondary-emphasis";
                        break;

                    case "EN_ACTUALIZACION":
                        claseBadge = "bg-warning text-dark";
                        textoEstado = "En actualización";
                        claseMensaje =
                            "bg-warning-subtle text-warning-emphasis";
                        break;

                    default:
                        claseBadge = "bg-success";
                        textoEstado = "Activo";
                        break;
                }

                const disponible = item.estado === "ACTIVO";
                const visible = Number(item.visible) === 1;

                // Validar la clase Font Awesome
                const icono = /^[a-zA-Z0-9 _-]+$/.test(item.icono || "")
                    ? item.icono
                    : "fa-solid fa-link";

                // Validar color CSS
                const color = /^(#[0-9a-fA-F]{3,8}|[a-zA-Z]+)$/.test(
                    item.color || "",
                )
                    ? item.color
                    : "blue";

                // Mensaje informativo para artefactos no activos
                let mensajeEstado = "";

                if (!disponible) {
                    mensajeEstado = `
                        <div class="rounded p-3 ${claseMensaje}">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-circle-info"></i>
                                <h6 class="fw-bold mb-0">${textoEstado}</h6>
                            </div>

                            <p class="small mb-0">
                                ${escaparHTML(item.mensaje_estado || "Temporalmente no disponible.")}
                            </p>
                        </div>
                    `;
                }

                // URL del artefacto
                let url = "";

                try {
                    const destino = new URL(
                        /^https?:\/\//i.test(item.url || "")
                            ? item.url
                            : `https://${item.url}`,
                    );

                    if (["http:", "https:"].includes(destino.protocol)) {
                        url = destino.href;
                    }
                } catch (e) {
                    url = "";
                }

                html += `
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card p-3 h-100 ${!visible ? "opacity-75" : ""}">
                            <div class="d-flex flex-column gap-3 h-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="icono-link" style="color: ${color}">
                                        <i class="${icono}"></i>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <span class="badge ${claseBadge} fs-6">
                                            ${textoEstado}
                                        </span>
                                        ${
                                            !visible
                                                ? `
                                            <span class="badge bg-dark">
                                                <i class="fa-solid fa-eye-slash me-1"></i>
                                                Oculto
                                            </span>
                                        `
                                                : ""
                                        }

                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="fw-bold">
                                        ${escaparHTML(item.nombre)}
                                    </h5>
                                    <p class="text-muted mb-0">
                                        ${escaparHTML(item.descripcion)}
                                    </p>
                                </div>
                                ${mensajeEstado}
                                <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                                    <button type="button" class="btn btn-sm btn-secondary btn-abrir-link" data-url="${escaparHTML(url)}" ${!disponible || !url ? "disabled" : ""}>
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir artefacto
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-editar-link" data-id="${item.id}" title="Editar link">                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-eliminar-link" data-id="${item.id}" title="Eliminar link">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $("#contenedorLinks").html(html);
        },

        error: function (xhr) {
            console.error(
                "Error al obtener links de importación:",
                xhr.responseText,
            );
        },
    });
}

const iconosDisponibles = [
    { clase: "fa-solid fa-link", nombre: "Enlace" },
    { clase: "fa-solid fa-file-excel", nombre: "Excel" },
    { clase: "fa-solid fa-file-pdf", nombre: "PDF" },
    { clase: "fa-solid fa-file-csv", nombre: "CSV" },
    { clase: "fa-solid fa-file-import", nombre: "Importación" },
    { clase: "fa-solid fa-file-export", nombre: "Exportación" },
    { clase: "fa-solid fa-database", nombre: "Base de datos" },
    { clase: "fa-solid fa-users", nombre: "Colaboradores" },
    { clase: "fa-solid fa-user-doctor", nombre: "Médicos" },
    { clase: "fa-solid fa-hospital", nombre: "Hospital" },
    { clase: "fa-solid fa-folder-open", nombre: "Carpeta" },
    { clase: "fa-solid fa-cloud-arrow-up", nombre: "Subir archivo" },
    { clase: "fa-solid fa-cloud-arrow-down", nombre: "Descargar" },
    { clase: "fa-solid fa-calendar-days", nombre: "Calendario" },
    { clase: "fa-solid fa-chart-column", nombre: "Reportes" },
    { clase: "fa-solid fa-gear", nombre: "Configuración" },
];

function cargarIconosDisponibles() {
    let html = "";
    iconosDisponibles.forEach(icono => {
        html += `
            <div class="col-3">
                <button type="button"
                        class="btn btn-outline-secondary w-100 btn-icono"
                        data-icono="${icono.clase}"
                        title="${icono.nombre}">
                    <i class="${icono.clase} fs-4"></i>
                </button>
            </div>
        `;
    });
    $("#galeriaIconos").html(html);
}


$("#btnSeleccionarIcono").on("click", function () {
    $("#contenedorSelectorIconos").toggleClass("d-none");
});

$(document).on("click", ".btn-icono", function () {
    const icono = $(this).data("icono");
    $("#inputIconoArtefacto").val(icono + " fs-5");
    $("#previewIconoArtefacto").attr("class", icono + " fs-5");
    $(".btn-icono").removeClass("active");
    $(this).addClass("active");
    $("#contenedorSelectorIconos").addClass("d-none");
});

cargarIconosDisponibles();

$(document).ready(function () {
    cargarLinksImportacion();
});

$("#formAltaLink").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.ajax({
        url: "/administracion/links/crearNuevoLinkImportacion",
        type: "POST",
        data: formData,
        dataType: "JSON",
        beforeSend: function () {
            $("#btnGuardar").text("Guardando").prop("disabled", true);
            $("#btnCancelar").prop("disabled", true);
        },
        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Operación exitosa!",
                text: res.mensaje,
                timer: 1200,
                showConfirmButton: false,
            });
            $("#modalAltaLink").modal("hide");
            $("#formAltaLink")[0].reset();
            cargarLinksImportacion();
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: xhr.responseJSON?.mensaje || "Ocurrió un error al crear el link.",
                showConfirmButton: true,
            });
            console.error(error);
        },
        complete: function(){
            $("#btnGuardar").text("Guardar").prop("disabled", false);
            $("#btnCancelar").prop("disabled", false);
        }
    });
});