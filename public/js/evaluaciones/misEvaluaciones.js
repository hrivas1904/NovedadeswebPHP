let tablaMisEvaluaciones = null;

$(document).ready(function () {
    tablaMisEvaluaciones = $("#tbMisEvaluaciones").DataTable({
        ajax: {
            url: EDD_MIS.routes.listar,
            type: "GET",
            dataType: "json",
            dataSrc: "data",
            error: mostrarError,
        },
        columns: [
            { data: "periodo" },
            { data: "puesto" },
            { data: "area" },
            { data: "evaluador", render: renderEvaluador },
            { data: "estado", render: renderEstado },
            { data: "evaluacion_final", render: renderEvaluacionFinal },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: renderAccion,
            },
        ],
        order: [[0, "desc"]],
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "54vh",
        scrollCollapse: true,
        paging: false,
        dom: "tir",
    });
});

/* ============================ RENDERS ============================ */

function renderEvaluador(data, type) {
    if (data) return type === "display" ? esc(data) : data;
    return type === "display"
        ? '<span class="text-muted">A definir</span>'
        : "";
}

function renderEstado(data, type) {
    const estado = EDD_MIS.estados[data] || {
        label: data,
        class: "bg-secondary",
    };
    return type === "display"
        ? '<span class="badge ' +
              estado.class +
              '">' +
              esc(estado.label) +
              "</span>"
        : estado.label;
}

// Hasta X,50 → X · desde X,51 → X+1 (misma regla que App\Support\Edd)
function valoracion(promedio) {
    return Math.max(
        1,
        Math.min(4, Math.ceil(Math.round(promedio * 100) / 100 - 0.5)),
    );
}

function renderEvaluacionFinal(data, type) {
    if (data === null || data === undefined)
        return type === "display" ? '<span class="text-muted">-</span>' : -1;
    if (type !== "display") return Number(data);

    const v = valoracion(Number(data));
    return (
        '<span class="fw-bold">' +
        Number(data).toFixed(2).replace(".", ",") +
        "</span>" +
        ' <small class="text-muted">' +
        esc(EDD_MIS.escala[v].label) +
        "</small>"
    );
}

function renderAccion(data, type, row) {
    const url = EDD_MIS.routes.formulario.replace(":id", row.id);

    if (row.accion === "AUTOEVALUAR") {
        return (
            '<a href="' +
            url +
            '" class="btn btn-sm btn-primary">' +
            '<i class="fa-solid fa-pen-to-square me-1"></i> Autoevaluarme</a>'
        );
    }

    if (row.accion === "FIRMAR") {
        return (
            '<a href="' +
            url +
            '" class="btn btn-sm btn-warning">' +
            '<i class="fa-solid fa-file-signature me-1"></i> Ver y firmar</a>'
        );
    }

    return (
        '<a href="' +
        url +
        '" class="btn btn-sm btn-outline-secondary">' +
        '<i class="fa-solid fa-eye me-1"></i> Ver</a>'
    );
}

/* ============================ HELPERS ============================ */

function esc(texto) {
    return $("<div>")
        .text(texto ?? "")
        .html();
}

function mostrarError(xhr) {
    const msg =
        (xhr.responseJSON && xhr.responseJSON.message) ||
        "Ocurrió un error inesperado.";
    Swal.fire({ icon: "error", title: "No se pudo completar", text: msg });
}
