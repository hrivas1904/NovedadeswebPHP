let tablaPeriodos = $("#tbPeriodos");
const MESES_CRONO = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
];
const DOW_CRONO = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

function poblarSelectoresPeriodo() {
    MESES_CRONO.forEach((m, i) =>
        $("#selectorMesPeriodo").append(
            `<option value="${i + 1}">${m}</option>`,
        ),
    );
    const actual = new Date().getFullYear();
    for (let a = actual - 1; a <= actual + 2; a++)
        $("#selectorAnnioPeriodo").append(`<option value="${a}">${a}</option>`);
}

function badgeEstado(estado) {
    const clases = {
        ABIERTO: "bg-secondary",
        PUBLICADO: "bg-success",
        CERRADO: "bg-danger",
    };
    return `<span class="badge ${clases[estado] || "bg-secondary"}">${estado}</span>`;
}

$(document).ready(function () {
    poblarSelectoresPeriodo();

    tablaPeriodos.DataTable({
        ajax: { url: RUTAS_CRONO_PERIODOS.listar, type: "GET", dataSrc: "" },
        language: { url: "/js/es-ES.json" },
        paging: false,
        scrollX: false,
        autoWidth: true,
        ordering: false,
        searching: false,
        dom: "tr",
        columns: [
            { data: "periodo" },
            { data: "dias" },
            { data: "primer_dia", render: (d) => DOW_CRONO[d] },
            { data: "asignaciones" },
            { data: "estado", render: badgeEstado },
            {
                data: null,
                render: (row) => `<div class="form-check form-switch">
                <input class="form-check-input chk-visible" type="checkbox" data-periodo="${row.periodo}" ${row.visible ? "checked" : ""}></div>`,
            },
            { data: "observaciones", defaultContent: "" },
            {
                data: null,
                render: (row) => {
                    const disabled =
                        row.asignaciones > 0
                            ? 'disabled title="Tiene cronograma cargado"'
                            : "";
                    return `<button class="btn btn-sm btn-outline-danger btn-eliminar-periodo" data-periodo="${row.periodo}" ${disabled}>
                    <i class="fa-solid fa-trash"></i></button>`;
                },
            },
        ],
    });
});

$("#modalConfiguraciones").on("shown.bs.modal", function () {
    tablaPeriodos.DataTable().columns.adjust().draw();
});

$("#btnAbrirPeriodoSelec").on("click", function () {
    const mes = $("#selectorMesPeriodo").val(),
        anio = $("#selectorAnnioPeriodo").val();
    if (!mes || !anio)
        return Swal.fire("Atención", "Elegí mes y año.", "warning");
    const periodo = `${anio}-${String(mes).padStart(2, "0")}`;

    $.post(RUTAS_CRONO_PERIODOS.abrir, {
        _token: $('meta[name="csrf-token"]').attr("content"),
        periodo,
        visible: 1,
    })
        .done((resp) => {
            if (resp.ok) {
                Swal.fire("Listo", `Período ${periodo} abierto.`, "success");
                tablaPeriodos.DataTable().ajax.reload();
            }
        })
        .fail((xhr) =>
            Swal.fire(
                "Atención",
                xhr.responseJSON?.msg || "No se pudo abrir el período.",
                "warning",
            ),
        );
});

$("#btnAbrirTodosMeses").on("click", function () {
    const anio = $("#selectorAnnioPeriodo").val();
    if (!anio) return Swal.fire("Atención", "Elegí el año.", "warning");

    Swal.fire({
        title: `¿Abrir los 12 meses de ${anio}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, abrir",
    }).then((r) => {
        if (!r.isConfirmed) return;
        $.post(RUTAS_CRONO_PERIODOS.abrirAnio, {
            _token: $('meta[name="csrf-token"]').attr("content"),
            anio,
        }).done((resp) => {
            Swal.fire(
                "Listo",
                `Se abrieron ${resp.abiertos.length} período(s) nuevos.`,
                "success",
            );
            tablaPeriodos.DataTable().ajax.reload();
        });
    });
});

$(document).on("change", ".chk-visible", function () {
    const periodo = $(this).data("periodo"),
        visible = $(this).is(":checked") ? 1 : 0;
    $.ajax({
        url: RUTAS_CRONO_PERIODOS.toggleVisible.replace(":periodo", periodo),
        type: "PATCH",
        data: { _token: $('meta[name="csrf-token"]').attr("content"), visible },
    });
});

$(document).on("click", ".btn-eliminar-periodo", function () {
    const periodo = $(this).data("periodo");
    Swal.fire({
        title: `¿Eliminar el período ${periodo}?`,
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((r) => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: RUTAS_CRONO_PERIODOS.eliminar.replace(":periodo", periodo),
            type: "DELETE",
            data: { _token: $('meta[name="csrf-token"]').attr("content") },
        }).done(() => tablaPeriodos.DataTable().ajax.reload());
    });
});
