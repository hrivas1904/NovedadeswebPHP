/* =====================================================================
   HP3C · RRHH · Mi descriptivo (vista del colaborador)
   ===================================================================== */
// ajusta el alto del textarea a su contenido
function ajustarAlto(el) {
    el.style.height = "auto";
    el.style.height = el.scrollHeight + 2 + "px";
}

function pintarCompetenciasMias($lista, items) {
    $lista.empty();
    if (!items.length) {
        $lista.append(
            $('<div class="list-group-item text-muted fst-italic">').text(
                "Sin competencias",
            ),
        );
        return;
    }
    items.forEach(function (c) {
        const $item = $('<div class="list-group-item">').append(
            $('<div class="fw-semibold">').text(c.nombre),
        );
        if (c.descripcion && c.descripcion !== "A definir.") {
            $item.append($('<small class="text-muted">').text(c.descripcion));
        }
        $lista.append($item);
    });
}

function cargarMiDescriptivo() {
    $.ajax({
        url: "/rrhh/descriptivos/mi-descriptivo/datos",
        type: "GET",
        dataType: "json",
        success: function (res) {
            const avisos = {
                SIN_VINCULO:
                    "No tenés un vínculo vigente en relación de dependencia.",
                SIN_PUESTO:
                    "Todavía no tenés un puesto asignado. Consultá con RRHH.",
                SIN_DESCRIPTIVO:
                    "Tu puesto todavía no tiene un descriptivo publicado.",
            };

            if (avisos[res.situacion] || !res.data) {
                $("#lblNombreRolDescriptivo").text(res.puesto || "");
                $("#divEstadoMiDescriptivo").html(
                    $('<div class="alert alert-secondary mb-0">').text(
                        avisos[res.situacion] ||
                            "No se encontró tu descriptivo.",
                    ),
                );
                $("#divContenidoMiDescriptivo").addClass("d-none");
                return;
            }

            const d = res.data;
            idMiDescriptivo = d.id;
            $("#lblNombreRolDescriptivo").text(
                d.nombre_puesto + " · versión " + d.version,
            );

            const campos = {
                inputArea: d.area,
                inputSector: d.servicio,
                inputReporta: d.reporta_a,
                inputSupervisor: d.supervisor_directo,
                inputSupervisa: d.supervisa_a,
                inputProposito: d.proposito_puesto,
                inputTareas: d.tareas,
                inputRelInternas: d.relaciones_internas,
                inputRelExternas: d.relaciones_externas,
                inputFormacion: d.formacion,
                inputExperiencia: d.experiencia,
                inputConocimientos: d.conocimientos,
                inputCapacitacion: d.capacitacion_continua,
                inputNormas: d.normas_calidad,
                inputCompromiso: d.compromiso_ambiental,
                inputSeguridadSalud: d.seguridad_salud_ocupacional,
                inputEtica: d.etica_confidencialidad,
                inputContribucion: d.contribucion,
            };
            $.each(campos, function (id, valor) {
                $("#" + id)
                    .val(valor || "")
                    .attr("placeholder", "Sin completar");
            });

            const comp = res.competencias || [];
            pintarCompetenciasMias(
                $("#listaMiCompGenericas"),
                comp.filter((c) => c.tipo === "ORGANIZACIONAL"),
            );
            pintarCompetenciasMias(
                $("#listaMiCompEspecificas"),
                comp.filter((c) => c.tipo === "FUNCIONAL"),
            );

            $("#divContenidoMiDescriptivo").removeClass("d-none");
            $(".texto-descriptivo").each(function () {
                ajustarAlto(this);
            });

            if (res.situacion === "ACEPTADO") {
                const [fecha, hora] = String(res.signed_at).split(/[ T]/);
                const [a, m, dd] = fecha.split("-");
                $("#divEstadoMiDescriptivo").html(
                    $('<div class="alert alert-success mb-0">')
                        .html('<i class="fa-solid fa-circle-check me-2"></i>')
                        .append(
                            document.createTextNode(
                                `Aceptaste este descriptivo el ${dd}/${m}/${a} a las ${(hora || "").substring(0, 5)}.`,
                            ),
                        ),
                );
                $("#divAceptarMiDescriptivo").addClass("d-none");
            } else {
                $("#divEstadoMiDescriptivo").html(
                    $('<div class="alert alert-warning mb-0">')
                        .html(
                            '<i class="fa-solid fa-triangle-exclamation me-2"></i>',
                        )
                        .append(
                            document.createTextNode(
                                "Tenés este descriptivo pendiente de aceptación. Leelo completo y confirmá al final de la página.",
                            ),
                        ),
                );
                $("#divAceptarMiDescriptivo").removeClass("d-none");
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo cargar tu descriptivo.", "error");
        },
    });
}

$(document).on("click", "#btnAceptarMiDescriptivo", function () {
    Swal.fire({
        title: "Aceptar descriptivo de puesto",
        text: "Confirmás que leíste tu descriptivo y estás de acuerdo con su contenido.",
        icon: "question",
        input: "checkbox",
        inputValue: 0,
        inputPlaceholder: "Leí el descriptivo completo",
        inputValidator: (v) =>
            !v ? "Marcá la casilla para confirmar" : undefined,
        showCancelButton: true,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
    }).then(function (r) {
        if (!r.isConfirmed) return;

        const $btn = $("#btnAceptarMiDescriptivo").prop("disabled", true);

        $.ajax({
            url: "/rrhh/descriptivos/mi-descriptivo/aceptar",
            type: "POST",
            dataType: "json",
            data: {
                idDescriptivo: idMiDescriptivo,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1800,
                    showConfirmButton: false,
                });
                cargarMiDescriptivo();
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    (xhr.responseJSON || {}).message ||
                        "No se pudo registrar la aceptación.",
                    "error",
                );
            },
            complete: function () {
                $btn.prop("disabled", false);
            },
        });
    });
});

$(window).on("resize", function () {
    $(".texto-descriptivo").each(function () {
        ajustarAlto(this);
    });
});

$(document).ready(cargarMiDescriptivo);
