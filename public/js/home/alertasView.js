function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "70vh";
}

$("#tbTodasAlertas").DataTable({
    destroy: true,
    ajax: {
        url: "/alertas/listarTodasAlertas",
        dataSrc: "data",
        data: function (d) {
            d.fechaDesde = $("#inputFechaDesde").val();
            d.fechaHasta = $("#inputFechaHasta").val();
            d.leidas = $("#selectorLeidas").val();
        },
    },
    language: {
        url: "/js/es-ES.json",
    },
    scrollY: getScrollY(),
    scrollX: false,
    autoWidth: true,
    paging: false,
    scrollCollapse: true,
    dom: "tir",
    order:[0,'desc'],
    columns: [
        { data: "id" },
        { data: "fecha" },
        { data: "modulo" },
        { data: "mensaje" },
        { data: "idReferencia", visible: false },
        { data: "url", visible: false },
        {
            data: null,
            className: "text-center",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                if (row.leida == 1) {
                    return "";
                }

                return `<input type="checkbox" class="checkAlertas" data-id="${row.id}">`;
            },
        },
    ],
});

$(document).on(
    "change",
    "#inputFechaDesde, #inputFechaHasta, #selectorLeidas",
    function () {
        $("#tbTodasAlertas").DataTable().ajax.reload();
    },
);

$(document).on("click", "#seleccionarTodasAlertas", function () {
    let isChecked = $(this).is(":checked");
    let table = $("#tbTodasAlertas").DataTable();
    $(table.rows().nodes()).find(".checkAlertas").prop("checked", isChecked);
});

$(document).on("change", ".checkAlertas", function () {
    let total = $(".checkAlertas").length;
    let seleccionadas = $(".checkAlertas:checked").length;

    $("#seleccionarTodasAlertas").prop(
        "checked",
        total > 0 && total === seleccionadas,
    );
});

$(document).on("click", "#btnMarcarLeidas", function () {
    let ids = [];

    $(".checkAlertas:checked").each(function () {
        ids.push($(this).data("id"));
    });

    if (ids.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Sin selección",
            text: "Seleccioná al menos una notificación.",
        });
        return;
    }

    Swal.fire({
        title: "¿Marcar como leídas?",
        text: `Se marcarán ${ids.length} notificaciones.`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: "/alertas/marcarLeidaMasivo",
            type: "POST",
            data: {
                ids: ids,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                $("#tbTodasAlertas").DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa",
                    text: "Las notificaciones fueron marcadas como leídas.",
                    timer: 1800,
                    showConfirmButton: false,
                });
            },
        });
    });
});
