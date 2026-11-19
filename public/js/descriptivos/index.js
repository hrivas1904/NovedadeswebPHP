let tablaDescriptivos = null;

$(document).ready(function () {
    tablaDescriptivos = $("#tb_descriptivos").DataTable({
        ajax: {
            url: "/rrhh/descriptivos/obtenerDescriptivos",
            type: "GET",
            dataType: "json",
            data: {
                p_id_area: null,
                p_estado: null,
                p_solo_ultima: null,
            },
        },
        language: {
            url: "/js/ES-es.json",
        },
        scrollX: false,
        autoWidth: true,
        scrollY: "62vh",
        scrollCollapse: true,
        dom: "tir",
        columns: [
            { data: "nombre_puesto" },
            { data: "area" },
            {
                data: "version",
                render: function (v) {
                    return "v" + v;
                },
            },
            {
                data: "estado",
                render: function (estado) {
                    const colores = {
                        BORRADOR: "bg-secondary",
                        VALIDADO: "bg-info text-dark",
                        PUBLICADO: "bg-success",
                        REEMPLAZADO: "bg-dark",
                    };
                    return (
                        '<span class="badge ' +
                        (colores[estado] || "bg-secondary") +
                        '">' +
                        estado +
                        "</span>"
                    );
                },
            },
        ],
        createdRow: function (row) {
            $(row).addClass("fila-clickeable");
        },
    });

    $("#tb_descriptivos tbody").on("click", "tr", function () {
        const fila = tablaDescriptivos.row(this).data();
        if (!fila) return; // fila de "No hay datos" o fila hija
        verDetalleDescriptivo(fila.id);
    });
});

function llenarModalDescriptivo(d, competencias) {
    const $modal = $("#modalVerDescriptivo");

    $modal.find("[data-campo]").each(function () {
        const $el = $(this);
        const valor = d[$el.data("campo")];
        const texto =
            valor === null || valor === undefined ? "" : String(valor);

        if ($el.is("input, textarea")) {
            $el.val(texto).attr("placeholder", "Sin completar");
            // alto según la cantidad de líneas (tareas, normas, etc.)
            if ($el.is("textarea")) {
                $el.attr("rows", Math.max(2, texto.split("\n").length));
            }
        } else {
            $el.text(texto);
        }
    });

    const colores = {
        BORRADOR: "bg-secondary",
        VALIDADO: "bg-info text-dark",
        PUBLICADO: "bg-success",
        REEMPLAZADO: "bg-dark",
    };
    $("#badgeEstadoDescriptivo")
        .attr("class", "badge " + (colores[d.estado] || "bg-secondary"))
        .text(d.estado);

    const pintarCompetencias = function ($lista, items) {
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
            const $item = $(
                '<div class="list-group-item d-flex justify-content-between align-items-start gap-2">',
            );
            const $texto = $("<div>")
                .append($('<div class="fw-semibold">').text(c.nombre))
                .append(
                    $('<small class="text-muted">').text(c.descripcion || ""),
                );
            const $btn = $(
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-editar-competencia" title="Editar">' +
                    '<i class="fa-solid fa-pencil"></i>' +
                    "</button>",
            ).attr("data-id", c.id);

            $lista.append($item.append($texto, $btn));
        });
    };
    competencias = competencias || [];
    pintarCompetencias(
        $("#listaCompGenericas"),
        competencias.filter((c) => c.tipo === "ORGANIZACIONAL"),
    );
    pintarCompetencias(
        $("#listaCompEspecificas"),
        competencias.filter((c) => c.tipo === "FUNCIONAL"),
    );

    // auditoría
    const partes = [
        "Creado por " + (d.creado_por || "-") + " el " + (d.created_at || "-"),
    ];
    if (d.validated_at)
        partes.push("validado por " + d.validado_por + " el " + d.validated_at);
    if (d.published_at)
        partes.push(
            "publicado por " + d.publicado_por + " el " + d.published_at,
        );
    $("#lblAuditoriaDescriptivo").text(partes.join(" · "));

    // botones del circuito según el estado
    $modal.data("idDescriptivo", d.id);
    $modal
        .find('[data-accion="validar"]')
        .toggleClass("d-none", d.estado !== "BORRADOR");
    $modal
        .find('[data-accion="devolver"], [data-accion="publicar"]')
        .toggleClass("d-none", d.estado !== "VALIDADO");

    $modal.modal("show");
}

function verDetalleDescriptivo(idDescriptivo) {
    $.ajax({
        url: "/rrhh/descriptivos/verDetalleDescriptivo",
        type: "GET",
        dataType: "json",
        data: {
            p_id_descriptivo: idDescriptivo,
        },
        success: function (res) {
            llenarModalDescriptivo(res.data, res.competencias);
        },
        error: function (xhr) {
            const msg =
                (xhr.responseJSON && xhr.responseJSON.message) ||
                "No se pudo cargar el descriptivo.";
            Swal.fire("Error", msg, "error");
        },
    });
}

$(document).on("click", ".btn-estado-descriptivo", function () {
    const accion = $(this).data("accion");
    const idDescriptivo = $("#modalVerDescriptivo").data("idDescriptivo");

    const textos = {
        validar: {
            titulo: "¿Validar el descriptivo?",
            texto: "Queda listo para que RRHH lo publique.",
        },
        devolver: {
            titulo: "¿Devolver a borrador?",
            texto: "Indicá qué hay que corregir.",
        },
        publicar: {
            titulo: "¿Publicar el descriptivo?",
            texto: "Pasa a ser la versión vigente del puesto y reemplaza a la anterior.",
        },
    };

    Swal.fire({
        title: textos[accion].titulo,
        text: textos[accion].texto,
        icon: "question",
        input: accion === "devolver" ? "textarea" : undefined,
        inputPlaceholder: "Motivo de la devolución",
        inputValidator:
            accion === "devolver"
                ? (v) =>
                      !v || !v.trim() ? "El motivo es obligatorio" : undefined
                : undefined,
        showCancelButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
    }).then(function (r) {
        if (!r.isConfirmed) return;

        $.ajax({
            url: "/rrhh/descriptivos/cambiarEstadoDescriptivo",
            type: "POST",
            dataType: "json",
            data: {
                idDescriptivo: idDescriptivo,
                accion: accion,
                motivo: accion === "devolver" ? r.value : null,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1500,
                    showConfirmButton: false,
                });
                verDetalleDescriptivo(idDescriptivo); // recarga el modal con el estado nuevo
                tablaDescriptivos.ajax.reload(null, false);
            },
            error: function (xhr) {
                const res = xhr.responseJSON || {};
                Swal.fire(
                    "Error",
                    res.message || "No se pudo cambiar el estado.",
                    "error",
                );
            },
        });
    });
});
