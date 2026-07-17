$("#tablaPedidosCompras").DataTable({
    ajax: "/compras/listar",

    destroy: true,

    responsive: true,

    pageLength: 25,

    language: {
        url: "/js/es-ES.json",
    },

    columns: [
        {
            data: "id",
            orderable: false,
            searchable: false,
            className: "text-center",
            render: function (data) {
                return `
            <input
                type="checkbox"
                class="form-check-input chkPedido"
                value="${data}">
        `;
            },
        },
        { data: "fecha" },

        { data: "prioridad" },

        { data: "solicitante" },

        { data: "sector" },

        { data: "proveedor" },

        { data: "descripcion" },

        { data: "lineas" },

        { data: "adjuntos" },

        { data: "autorizacion" },

        { data: "estado" },

        {
            data: null,

            orderable: false,

            searchable: false,

            className: "text-center",

            render: function (data) {
                if (data.autorizacion == "PENDIENTE") {
                    return `

        <button
            class="btn btn-success btn-sm btnAutorizar"
            data-id="${data.id}">
            <i class="fa fa-check"></i>
        </button>

        <button
            class="btn btn-danger btn-sm btnRechazar"
            data-id="${data.id}">
            <i class="fa fa-times"></i>
        </button>

        `;
                }

                return `
        <button
            class="btn btn-secondary btn-sm"
            disabled>
            <i class="fa fa-lock"></i>
        </button>
    `;
            },
        },
    ],
});

$("#tablaPedidosCompras tbody").on("click", "tr", function (e) {
    if ($(e.target).closest("button").length) return;

    if ($(e.target).closest("input").length) return;

    let tabla = $("#tablaPedidosCompras").DataTable();

    let data = tabla.row(this).data();

    verPedido(data.id);
});

$(document).on("click", ".btnAutorizar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Autorizar pedido?",

        icon: "question",

        showCancelButton: true,

        confirmButtonText: "Autorizar",
    }).then((r) => {
        if (!r.isConfirmed) return;

        $.post(
            "/compras/aprobar",

            {
                _token: $('meta[name="csrf-token"]').attr("content"),

                id: id,
            },

            function () {
                $("#tablaPedidosCompras").DataTable().ajax.reload(null, false);
            },
        );
    });
});

$(document).on("click", ".btnRechazar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Rechazar pedido?",

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Rechazar",
    }).then((r) => {
        if (!r.isConfirmed) return;

        $.post(
            "/compras/rechazar",

            {
                _token: $('meta[name="csrf-token"]').attr("content"),

                id: id,
            },

            function () {
                $("#tablaPedidosCompras").DataTable().ajax.reload(null, false);
            },
        );
    });
});

function exportarExcel() {
    let ids = [];

    $(".chkPedido:checked").each(function () {
        ids.push($(this).val());
    });

    if (ids.length == 0) {
        Swal.fire({
            icon: "warning",
            title: "Seleccione al menos un pedido.",
        });

        return;
    }

    let form = $("<form>", {
        method: "POST",

        action: "/compras/exportar-excel",
    });

    form.append(
        $("<input>", {
            type: "hidden",
            name: "_token",
            value: $('meta[name="csrf-token"]').attr("content"),
        }),
    );

    ids.forEach(function (id) {
        form.append(
            $("<input>", {
                type: "hidden",
                name: "ids[]",
                value: id,
            }),
        );
    });

    $("body").append(form);

    form.submit();
}

$(document).on("change", "#checkTodosPedidos", function () {
    $(".chkPedido").prop("checked", this.checked);
});

$(document).on("change", ".chkPedido", function () {
    const total = $(".chkPedido").length;
    const seleccionados = $(".chkPedido:checked").length;

    $("#checkTodosPedidos").prop(
        "checked",
        total > 0 && total === seleccionados,
    );
});

function verPedido(id) {
    $.ajax({
        url: "/compras/ver/" + id,

        type: "GET",

        dataType: "json",

        success: function (response) {
            let c = response.cabecera;

            $("#verSolicitante").val(c.solicitante);
            $("#verFecha").val(c.fecha);
            $("#verPrioridad").val(c.prioridad);
            $("#verCentroCosto").val(c.centroCosto);
            $("#verProveedor").val(c.proveedor);
            $("#verEstado").val(c.estado);
            $("#verAutorizacion").val(c.autorizacion);
            $("#verDescripcion").val(c.descripcion);

            let tbody = $("#detalleProductosBody");

            tbody.empty();

            response.detalle.forEach(function (item) {
                tbody.append(`
                    <tr>

                        <td>${item.nombre}</td>

                        <td>${item.descripcion_item ?? ""}</td>

                        <td class="text-center">
                            ${item.cantidad}
                        </td>

                        <td class="text-end">
                            $ ${parseFloat(item.precio).toLocaleString(
                                "es-AR",
                                {
                                    minimumFractionDigits: 2,
                                },
                            )}
                        </td>

                    </tr>
                `);
            });

            $("#modalDetallePedido").modal("show");
        },
    });
}
