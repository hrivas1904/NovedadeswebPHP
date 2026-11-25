let novedadComprobanteSel = null;
let btnSubirComprobanteSel = null;

// Botones para cada fila de novedad (usar en el render de la tabla/lista)
function botonesComprobantes(idNovedad) {
    return `
        <button type="button" class="btn btn-sm btn-subir-archivo" data-registro="${idNovedad}" title="Subir comprobante">
            <i class="fa-solid fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-ver-archivo" data-registro="${idNovedad}" title="Ver comprobantes">
            <i class="fa-solid fa-eye"></i>
        </button>
    `;
}

// SUBIR COMPROBANTE
$(document).on("click", ".btn-subir-archivo", function (e) {
    e.preventDefault();
    e.stopPropagation();

    novedadComprobanteSel = $(this).data("registro");
    btnSubirComprobanteSel = $(this);

    let input = document.getElementById("inputComprobantes");
    if (!input) {
        $("body").append(
            '<input type="file" id="inputComprobantes" multiple hidden accept=".pdf,.jpg,.jpeg,.png">',
        );
        input = document.getElementById("inputComprobantes");
    }

    input.value = "";
    input.click();
});

$(document).on("change", "#inputComprobantes", function () {
    const files = this.files;
    if (!files || files.length === 0 || !novedadComprobanteSel) return;

    subirComprobantes(files);
});

function subirComprobantes(files) {
    const formData = new FormData();
    formData.append("id_nxe", novedadComprobanteSel);

    for (let i = 0; i < files.length; i++) {
        formData.append("comprobantes[]", files[i]);
    }

    const btn = btnSubirComprobanteSel;
    const iconoOriginal = btn ? btn.html() : "";
    if (btn) {
        btn.prop("disabled", true).html(
            '<i class="fa-solid fa-spinner fa-spin"></i>',
        );
    }

    $.ajax({
        url: RRHH_ROUTES.subirComprobante,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function () {
            Swal.fire({
                icon: "success",
                title:
                    files.length > 1
                        ? "Comprobantes subidos"
                        : "Comprobante subido",
                timer: 1500,
                showConfirmButton: false,
            });
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.message || "No se pudo subir el comprobante";
            Swal.fire("Error", msg, "error");
            console.error(xhr.responseText);
        },
        complete: function () {
            if (btn) btn.prop("disabled", false).html(iconoOriginal);
            $("#inputComprobantes").val("");
            novedadComprobanteSel = null;
            btnSubirComprobanteSel = null;
        },
    });
}

// VER COMPROBANTES
$(document).on("click", ".btn-ver-archivo", function (e) {
    e.preventDefault();
    e.stopPropagation();

    verComprobantes($(this).data("registro"));
});

function verComprobantes(idNovedad) {
    $.ajax({
        url: RRHH_ROUTES.comprobantesNovedad.replace(":id", idNovedad),
        method: "GET",
        dataType: "json",
        success: function (response) {
            const contenedor = $("#contenedorComprobantes");
            contenedor.html("");

            if (!response || response.length === 0) {
                contenedor.html(`
                    <div class="alert alert-info mb-0">
                        Esta novedad no tiene comprobantes.
                    </div>
                `);
            } else {
                response.forEach(function (comp) {
                    const url = RRHH_ROUTES.verComprobante.replace(
                        ":id",
                        comp.id,
                    );

                    contenedor.append(`
                        <div class="mb-4 border rounded p-2">
                            <div class="d-flex justify-content-end mb-2">
                                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-up-right-from-square"></i> Abrir
                                </a>
                            </div>
                            <iframe src="${url}"
                                    style="width:100%; height:600px; border:none;"
                                    loading="lazy"></iframe>
                        </div>
                    `);
                });
            }

            bootstrap.Modal.getOrCreateInstance(
                document.getElementById("modalVerComprobantes"),
            ).show();
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

// Al cerrar el modal se vacían los iframes para liberar memoria
$(document).on("hidden.bs.modal", "#modalVerComprobantes", function () {
    $("#contenedorComprobantes").html("");
});
