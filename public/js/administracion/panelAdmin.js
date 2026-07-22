$(function () {
    filtrarPedidos();
});

function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const partes = fecha.split("-"); // yyyy-mm-dd
    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "57vh";
}

function filtrarPedidos() {
    const valor = $("#buscarPedido").val();
    $("#tablaPedidosCompras").DataTable().search(valor).draw();
}

$("#tablaPedidosCompras").DataTable({
    ajax: {
        url: "/compras/listar",
        type: "GET",
        data: function (d) {
            d.prioridades = getPrioridadesSeleccionadas().join(",") || null;
            d.estados = getEstadosSeleccionadas().join(",") || null;
            d.autorizaciones =
                getAutorizacionesSeleccionadas().join(",") || null;
            d.desde = $("#filtroDesde").val();
            d.hasta = $("#filtroHasta").val();
        },
    },
    order: [[0, "desc"]],
    destroy: true,
    responsive: true,
    language: {
        url: "/js/es-ES.json",
    },
    scrollX: true,
    paging: false,
    searching: true,
    autoWidth: false,
    scrollCollapse: true,
    scrollY: getScrollY(),
    dom: "tir",
    columnDefs: [
        {
            targets: [4, 5],
            className: "text-wrap",
        },
    ],
    columns: [
        {
            data: "fecha",
            width: "5%",
            render: function (data, type, row) {
                if (type === "sort" || type === "type") {
                    return data;
                }
                return formatearFechaArgentina(data);
            },
        },
        { data: "prioridad", width: "7%" },
        { data: "solicitante" },
        { data: "sector" },
        { data: "proveedor" },
        { data: "descripcion" },
        { data: "lineas", visible: false },
        { data: "adjuntos", visible: false },
        { data: "autorizacion" },
        { data: "estado" },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: "text-center",
            render: function (data) {
                if (!PUEDE_AUTORIZAR_PEDIDOS) {
                    return "";
                }

                if (data.estado == "GENERADO") {
                    return "";
                }

                if (data.autorizacion == "PENDIENTE") {
                    return `
                        <button class="btn btn-primary btn-sm btnAutorizar" data-id="${data.id}">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btnRechazar" data-id="${data.id}">
                            <i class="fa fa-times"></i>
                        </button>
                    `;
                }

                const PUEDE_APROBAR_GERENTE = [1, 5].includes(USER_ID);
                if (
                    data.autorizacion === "REQUIERE AUTORIZACIÓN GERENTE" &&
                    PUEDE_APROBAR_GERENTE
                ) {
                    return `
                        <button class="btn btn-primary btn-sm btnAutorizarGerente" data-id="${data.id}">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btnRechazar" data-id="${data.id}">
                            <i class="fa fa-times"></i>
                        </button>
                    `;
                }

                if (data.autorizacion == "APROBADA") {
                    return `
                        <input type="checkbox" class="form-check-input chkPedido" value="${data.id}">
                    `;
                }

                return "";
            },
        },
    ],
});

$(document).on(
    "change",
    ".check-Prioridades, .check-Autorizacion, .check-Estados",
    function () {
        $("#tablaPedidosCompras").DataTable().ajax.reload();
    },
);

$("#filtroDesde, #filtroHasta").on("change", function () {
    $("#tablaPedidosCompras").DataTable().ajax.reload();
});

$("#btn-limpiar-filtros").on("click", function () {
    $(".check-Prioridades, .check-Autorizacion, .check-Estados").prop(
        "checked",
        false,
    );
    $("#filtroDesde, #filtroHasta").val(null);
    $("#tablaPedidosCompras").DataTable().ajax.reload();
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
        title: "Autorización de pedido de compra",
        html: `
            <div class="text-start mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipoAprobacion" id="aprobacionDirecta" value="DIRECTA" checked>
                    <label class="form-check-label" for="aprobacionDirecta">
                        Aprobar directamente
                    </label>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="tipoAprobacion" id="aprobacionGerente" value="GERENTE">
                    <label class="form-check-label" for="aprobacionGerente">
                        Requiere aprobación del gerente
                    </label>
                </div>
            </div>
        `,
        icon: "question",
        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Continuar",
        customClass: {
            confirmButton: "btn btn-primary me-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
        preConfirm: () => {
            return $('input[name="tipoAprobacion"]:checked').val();
        },
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.post(
            "/compras/aprobar",
            {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: id,
                requiereGerente:
                    $('input[name="tipoAprobacion"]:checked').val() ===
                    "GERENTE"
                        ? 1
                        : 0,
            },
            function () {
                let mensaje =
                    result.value === "DIRECTA"
                        ? "Pedido autorizado correctamente."
                        : "El pedido fue enviado para aprobación del gerente.";

                Swal.fire({
                    icon: "success",
                    title: "¡Operación exitosa!",
                    text: mensaje,
                    timer: 2000,
                    showConfirmButton: false,
                });

                $("#tablaPedidosCompras").DataTable().ajax.reload(null, false);
            },
        );
    });
});

$(document).on("click", ".btnAutorizarGerente", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Autorizar pedido?",
        text: "El pedido quedará aprobado por Gerencia.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Autorizar",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn btn-primary me-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.post(
            "/compras/aprobar-gerente",
            {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: id,
            },
            function () {
                Swal.fire({
                    icon: "success",
                    title: "¡Operación exitosa!",
                    text: "El pedido fue aprobado por Gerencia.",
                    timer: 1800,
                    showConfirmButton: false,
                });

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
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn btn-primary me-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    }).then((r) => {
        if (!r.isConfirmed) return;

        $.post(
            "/compras/rechazar",
            {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: id,
            },
            function () {
                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa!",
                    text: "Pedido rechazado correctamente.",
                    timer: 1800,
                    showConfirmButton: false,
                });
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
            customClass: {
                confirmButton: "btn btn-primary",
            },
            buttonsStyling: false,
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
            $("#verFecha").val(formatearFechaArgentina(c.fecha));
            $("#verPrioridad").val(c.prioridad);
            $("#verCentroCosto").val(c.centroCosto);
            $("#verProveedor").val(c.proveedor);
            $("#verEstado").val(c.estado);
            $("#verAutorizacion").val(c.autorizacion);
            $("#verDescripcion").val(c.descripcion);
            $("#reqAutGerente").val(c.autGerente);
            $("#estadoAutGerente").val(c.autorizacion_gerente);

            let tbody = $("#detalleProductosBody");
            tbody.empty();
            response.detalle.forEach(function (item) {
                const precioTexto =
                    item.precio === null || item.precio === undefined
                        ? "Sin especificar"
                        : "$ " +
                          parseFloat(item.precio).toLocaleString("es-AR", {
                              minimumFractionDigits: 2,
                          });

                tbody.append(`
                    <tr>
                        <td>${item.producto}</td>
                        <td>${item.descripcion_item ?? ""}</td>
                        <td class="text-center">
                            ${item.cantidad}
                        </td>
                        <td class="text-end">
                            ${precioTexto}
                        </td>
                    </tr>
                `);
            });

            // guardamos el id actual en el propio modal, para que btnSubirOrdenCompra lo pueda leer
            $("#modalDetallePedido").data("pedido-id", id);

            cargarAdjuntosPedido(id);
            cargarOrdenesCompra(id);
            $("#modalDetallePedido").modal("show");
        },
    });
}

function cargarAdjuntosPedido(pedidoId) {
    $("#detalleAdjuntosBody").html("");
    $("#sinAdjuntosMsg").addClass("d-none");

    $.get(`/compras/${pedidoId}/adjuntos`, function (resp) {
        if (!resp.data.length) {
            $("#sinAdjuntosMsg").removeClass("d-none");
            return;
        }

        resp.data.forEach((adj) => {
            const icono = adj.esImagen ? "fa-file-image" : "fa-file-pdf";

            const col = `
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 text-center h-100" style="cursor:pointer;" onclick="verAdjunto('${adj.url}', '${adj.nombre}', ${adj.esImagen})">
                        <i class="fa-solid ${icono} fa-2x mb-1 text-secondary"></i>
                        <div class="small text-truncate" title="${adj.nombre}">${adj.nombre}</div>
                    </div>
                </div>
            `;
            $("#detalleAdjuntosBody").append(col);
        });
    }).fail(function () {
        Swal.fire(
            "Error",
            "No se pudieron cargar los adjuntos del pedido.",
            "error",
        );
    });
}

function verAdjunto(url, nombre, esImagen) {
    $("#visorAdjuntoNombre").html(
        `<i class="fa-solid fa-file me-2"></i>${nombre}`,
    );

    let contenido;
    if (esImagen) {
        contenido = `<img src="${url}" class="w-100 h-100" style="object-fit: contain;" alt="${nombre}">`;
    } else {
        contenido = `<iframe src="${url}" class="w-100 h-100" style="border: none;"></iframe>`;
    }

    $("#visorAdjuntoContenido").html(contenido);
    $("#modalVisorAdjunto").modal("show");
}

$("#modalVisorAdjunto").on("hidden.bs.modal", function () {
    $("#visorAdjuntoContenido").html("");
});

$(document).on("click", "#btnSubirOrdenCompra", function () {
    const pedidoId = $("#detallePedidoId").val();
    const input = $("#inputOrdenCompra")[0];
    const archivo = input.files[0];

    if (!pedidoId) {
        Swal.fire("Atención", "No se pudo identificar el pedido.", "warning");
        return;
    }

    if (!archivo) {
        Swal.fire("Atención", "Seleccione un archivo.", "warning");
        return;
    }

    const formData = new FormData();
    formData.append("archivo", archivo);

    $.ajax({
        url: `/compras/${pedidoId}/orden-compra`,
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function () {
            $("#btnSubirOrdenCompra").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Subiendo...
                `);
        },

        success: function (response) {
            $("#inputOrdenCompra").val("");

            Swal.fire({
                icon: "success",
                title: "Archivo adjuntado",
                text: response.mensaje,
                timer: 1800,
                showConfirmButton: false,
            });

            cargarOrdenesCompra(pedidoId);
        },

        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "No se pudo subir el archivo",
                text:
                    xhr.responseJSON?.message ??
                    xhr.responseJSON?.mensaje ??
                    "Ocurrió un error.",
            });
        },

        complete: function () {
            $("#btnSubirOrdenCompra").prop("disabled", false).html(`
                    <i class="fa-solid fa-upload me-2"></i>
                    Subir archivo
                `);
        },
    });
});

function cargarOrdenesCompra(pedidoId) {
    $("#detalleOCBody").html("");
    $("#sinOCMsg").addClass("d-none");

    $.get(`/compras/${pedidoId}/adjuntos/ORDEN_COMPRA`, function (resp) {
        if (!resp.data.length) {
            $("#sinOCMsg").removeClass("d-none");
            return;
        }
        resp.data.forEach((adj) => {
            const icono = adj.esImagen ? "fa-file-image" : "fa-file-pdf";
            const col = `
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 text-center h-100" style="cursor:pointer;" onclick="verAdjunto('${adj.url}', '${adj.nombre}', ${adj.esImagen})">
                        <i class="fa-solid ${icono} fa-2x mb-1 text-secondary"></i>
                        <div class="small text-truncate" title="${adj.nombre}">${adj.nombre}</div>
                    </div>
                </div>
            `;
            $("#detalleOCBody").append(col);
        });
    });
}

$(document).on("click", "#btnSubirOrdenCompra", function () {
    const input = document.getElementById("inputOrdenCompra");
    const file = input.files[0];

    if (!file) {
        Swal.fire("Atención", "Seleccioná un archivo primero.", "warning");
        return;
    }

    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    const formData = new FormData();
    formData.append("archivo", file);

    $.ajax({
        url: `/compras/${pedidoId}/orden-compra`,
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire(
                "Listo",
                "Orden de compra cargada correctamente.",
                "success",
            );
            input.value = "";
            cargarOrdenesCompra(pedidoId);
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al subir el archivo.";
            Swal.fire("Error", msg, "error");
        },
    });
});
