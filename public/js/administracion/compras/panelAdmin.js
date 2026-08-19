$(function () {
    filtrarPedidos();
});

function formatearFechaArgentina(fecha) {
    if (!fecha) return "";

    const soloFecha = fecha.split(" ")[0]; // 2026-07-16
    const partes = soloFecha.split("-");

    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "68vh";
}

function filtrarPedidos() {
    const valor = $("#buscarPedido").val();
    $("#tablaPedidosCompras").DataTable().search(valor).draw();
}

$("#tablaPedidosCompras").DataTable({
    ajax: {
        url: "/administracion/compras/listar",
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
    scrollX: false,
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
        { data: "prioridad",},
        { data: "solicitante" },
        { data: "sector", },
        { data: "proveedor", },
        { data: "descripcion", className: "align-middle" },
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
                        <button class="btn btn-sm btnAutorizar" data-id="${data.id}" style="color:var(--color-accent-green)" title="Aprobar pedido">
                            <i class="fs-5 fa-regular fa-square-check"></i>
                        </button>
                        <button class="btn btn-sm btnRechazar" data-id="${data.id}" style="color:var(--color-accent-red)" title="Rechazar pedido">
                            <i class="fs-5 fa-regular fa-circle-xmark"></i>
                        </button>
                    `;
                }

                const PUEDE_APROBAR_GERENTE = [1, 5].includes(USER_ID);
                if (
                    data.autorizacion === "REQUIERE AUTORIZACIÓN GERENTE" &&
                    PUEDE_APROBAR_GERENTE
                ) {
                    return `
                        <button class="btn btn-sm btnAutorizarGerente" data-id="${data.id}" style="color:var(--color-accent-green)" title="Aprobar pedido">
                            <i class="fs-5 fa-regular fa-square-check"></i>
                        </button>
                        <button class="btn btn-sm btnRechazar" data-id="${data.id}" style="color:var(--color-accent-red)" title="Rechazar pedido">
                            <i class="fs-5 fa-regular fa-circle-xmark"></i>
                        </button>
                    `;
                }

                if (data.autorizacion == "APROBADA") {
                    return `
                        <input type="checkbox" class="form-check-input chkPedido" value="${data.id}">
                    `;
                }

                return `
                    <button class="btn btn-sm btnVerDetalle" data-id="${data.id}" style="color:var(--color-default)" title="Ver pedido">
                        <i class="fs-5 fa fa-eye"></i>
                    </button>
                `;
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

$(document).on("click", ".btnVerDetalle", function (e) {
    e.stopPropagation();

    const id = $(this).data("id");

    $("#modalDetallePedido").data("pedido-id", id);

    verPedido(id);
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
            "/administracion/compras/aprobar",
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
            "/administracion/compras/aprobar-gerente",
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
            "/administracion/compras/rechazar",
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

    exportarPedidosExcel(ids);
}

function exportarPedidosExcel(ids) {
    if (!ids || ids.length === 0) {
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
        action: "/administracion/compras/exportar-excel",
    });

    form.append(
        $("<input>", {
            type: "hidden",
            name: "_token",
            value: $('meta[name="csrf-token"]').attr("content"),
        })
    );

    ids.forEach(function (id) {
        form.append(
            $("<input>", {
                type: "hidden",
                name: "ids[]",
                value: id,
            })
        );
    });

    $("body").append(form);
    form.submit();
    form.remove();

    $("#tablaPedidosCompras").DataTable().ajax.reload(null, false);
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

$("#btnRegenerarExcelFinnegans").on("click", function () {
    const id = $("#idPedido").val();

    exportarPedidosExcel([id]);
});

function verPedido(id) {
    $.ajax({
        url: "/administracion/compras/ver/" + id,
        type: "GET",
        dataType: "json",
        success: function (response) {
            let c = response.cabecera;
            $("#idPedido").val(c.id);
            $("#verSolicitante").val(c.solicitante);
            $("#verFecha").val(formatearFechaArgentina(c.fecha));
            $("#verPrioridad").val(c.prioridad);
            cargarCentrosCosto($("#verCentroCosto"), $("#modalDetallePedido"), c.centroCosto);
            cargarProveedores($("#verProveedor"), $("#modalDetallePedido"), c.proveedor);
            $("#verEstado").val(c.estado);
            $("#verAutorizacion").val(c.autorizacion);
            $("#verDescripcion").val(c.descripcion);
            $("#reqAutGerente").val(c.autGerente);
            $("#estadoAutGerente").val(c.autorizacion_gerente);
            $("#auditorPedido").val(c.auditor);

            let tbody = $("#detalleProductosBody");
            tbody.empty();

            response.detalle.forEach(function (item, index) {

                const precioValor =
                    item.precio === null || item.precio === undefined
                        ? ""
                        : parseFloat(item.precio);

                tbody.append(`
                    <tr data-detalle-id="${item.id}">
                        <td>
                            <select
                                id="productoDetalle_${index}"
                                class="form-control selector-producto"
                                disabled>
                            </select>
                        </td>

                        <td>
                            <input
                                class="form-control input-descripcion"
                                value="${item.descripcion_item ?? ""}"
                                readonly>
                        </td>

                        <td class="text-center">
                            <input
                                class="form-control input-cantidad"
                                value="${item.cantidad}"
                                readonly>
                        </td>

                        <td class="text-end">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control input-precio"
                                value="${precioValor}"
                                readonly>
                        </td>

                        <td class="text-center campoDeleteTabla d-none">
                            <button
                                type="button"
                                class="btn btn-sm btnEliminarProducto"
                                style="color:var(--color-accent-red);">

                                <i class="fs-5 fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `);

                inicializarSelectProducto(
                    $("#productoDetalle_" + index),
                    $("#modalDetallePedido"),
                    item.producto_id
                );
            });
            
            $("#modalDetallePedido").data("pedido-id", id);

            cargarAdjuntosPedido(id);
            cargarOrdenesCompra(id);
            cargarObservaciones(id);
            $("#modalDetallePedido").modal("show");

            if ($("#verEstado").val()==='GENERADO'){
                $("#btnRegenerarExcelFinnegans").removeClass("d-none");
            }
            else {
                $("#btnRegenerarExcelFinnegans").addClass("d-none");
            }

            if ($("#verAutorizacion").val()==='APROBADA'){
                $("#divPresupuestosAdjuntos, #btnHabilitarEdicionPedido").addClass("d-none");
            } else {
                $("#divPresupuestosAdjuntos, #btnHabilitarEdicionPedido").removeClass("d-none");
            }
        },
    });
}

let DETALLES_ELIMINADOS = [];
$(document).on("click", ".btnEliminarProducto", function () {

    const fila = $(this).closest("tr");
    const detalleId = fila.data("detalle-id");

    // Si ya existe en BD, guardamos el ID para eliminarlo al confirmar
    if (detalleId) {
        DETALLES_ELIMINADOS.push(detalleId);
    }

    fila.remove();
});

function cargarAdjuntosPedido(pedidoId) {
    $("#detalleAdjuntosBody").html("");
    $("#sinAdjuntosMsg").addClass("d-none");

    $.get(`/administracion/compras/${pedidoId}/adjuntos`, function (resp) {

        if (!resp.data.length) {
            $("#sinAdjuntosMsg").removeClass("d-none");
            return;
        }

        resp.data.forEach((adj) => {

            const icono = adj.esImagen
                ? "fa-file-image"
                : "fa-file-pdf";

            const col = `
                <div class="col-md-3 col-6">
                    <div class="d-flex align-items-center border rounded p-2 gap-2">

                        <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden"
                            style="cursor:pointer;"
                            onclick="verAdjunto('${adj.url}', '${adj.nombre}', ${adj.esImagen})">

                            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:38px; height:38px;">
                                <i class="fa-solid ${icono} fs-4 text-secondary"></i>
                            </div>

                            <div class="overflow-hidden">
                                <div class="small fw-semibold text-truncate"
                                    title="${adj.nombre}">
                                    ${adj.nombre}
                                </div>

                                <div class="text-muted" style="font-size: 0.72rem;">
                                    Ver archivo
                                </div>
                            </div>

                        </div>

                        <button type="button"
                            class="btn btn-sm btn-outline-danger btnEliminarPresupuesto flex-shrink-0"
                            data-id="${adj.id}"
                            title="Eliminar presupuesto">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>

                    </div>
                </div>
            `;

            $("#detalleAdjuntosBody").append(col);
        });

        if ($("#verAutorizacion").val()==='APROBADA'){
            $(".btnEliminarPresupuesto").addClass('d-none');
        }
    })
    .fail(function () {

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
        url: `/administracion/compras/${pedidoId}/orden-compra`,
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

    $.get(
        `/administracion/compras/${pedidoId}/adjuntos/ORDEN_COMPRA`,
        function (resp) {
            if (!resp.data.length) {
                $("#sinOCMsg").removeClass("d-none");
                return;
            }
            resp.data.forEach((adj) => {
                const icono = adj.esImagen ? "fa-file-image" : "fa-file-pdf";
                const col = `
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center border rounded p-2 gap-2 adjunto-item">

                            <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden"
                                style="cursor:pointer;"
                                onclick="verAdjunto('${adj.url}', '${adj.nombre}', ${adj.esImagen})">

                                <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:38px; height:38px;">

                                    <i class="fa-solid ${icono} fs-4 text-secondary"></i>

                                </div>

                                <div class="overflow-hidden">
                                    <div class="small fw-semibold text-truncate"
                                        title="${adj.nombre}">
                                        ${adj.nombre}
                                    </div>

                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        Ver archivo
                                    </div>
                                </div>

                            </div>

                            <button type="button"
                                class="btn btn-sm btn-outline-danger btnEliminarOrdenCompra flex-shrink-0"
                                data-id="${adj.id}"
                                title="Eliminar orden de compra">

                                <i class="fa-regular fa-trash-can"></i>

                            </button>

                        </div>
                    </div>
                `;
                $("#detalleOCBody").append(col);
            });
        },
    );
}

$(document).on("click", ".btnEliminarOrdenCompra", function (e) {
    e.stopPropagation();

    const adjuntoId = $(this).data("id");
    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    Swal.fire({
        title: "¿Eliminar orden de compra?",
        text: "El archivo será eliminado definitivamente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn btn-danger me-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: `/administracion/compras/orden-compra/${adjuntoId}`,
            type: "DELETE",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            success: function (response) {

                // Recargamos las OC del pedido
                cargarOrdenesCompra(pedidoId);

                Swal.fire({
                    icon: "success",
                    title: "¡Operación exitosa!",
                    text: "Orden de compra eliminada correctamente.",
                    timer: 1500,
                    showConfirmButton: false,
                });
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        xhr.responseJSON?.mensaje ??
                        "No fue posible eliminar la orden de compra.",
                });
            },
        });
    });
});

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
        url: `/administracion/compras/${pedidoId}/orden-compra`,
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

let pedidoActualId = null;  

function cargarObservaciones(pedidoId) {
    pedidoActualId = pedidoId;
    $.get(`/administracion/pedidos/${pedidoActualId}/listarObservaciones`, function (data) {
        const $hilo = $('#hiloObservaciones').empty();
        if (data.length === 0) {
            $hilo.html('<div class="text-muted small">Sin observaciones todavía.</div>');
            return;
        }
        data.forEach(obs => {
            $hilo.append(`
                <div class="mb-2">
                    <div class="small text-muted"><strong>${obs.usuario_nombre}</strong> · ${obs.created_at}</div>
                    <div>${obs.mensaje}</div>
                </div>
            `);
        });
        $hilo.scrollTop($hilo[0].scrollHeight);
    });
}

$(document).on('click', '#btnEnviarObservacion', function () {
    console.log("CLICK NATIVO");
    const mensaje = $('#inputNuevaObservacion').val().trim();
    console.log(mensaje);
    if (!mensaje || !pedidoActualId) return;
    console.log(pedidoActualId)
    $.ajax({
        url: `/administracion/pedidos/${pedidoActualId}/agregarObservaciones`,
        method: 'POST',
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: { mensaje: mensaje },
        success: function (obs) {
            $('#hiloObservaciones').append(`
                <div class="mb-2">
                    <div class="small text-muted"><strong>${obs.usuario_nombre}</strong> · ${obs.created_at}</div>
                    <div>${obs.mensaje}</div>
                </div>
            `);
            $('#inputNuevaObservacion').val('');
            $('#hiloObservaciones').scrollTop($('#hiloObservaciones')[0].scrollHeight);
        }
    });
});

$("#btnAbrirModalNuevoPedido").on("click", function(){
    cargarCentrosCosto($("#cmbCentroCostoModal"), $("#modalCargaPedido"));
    cargarProveedores($("#cmbProveedorModal"), $("#modalCargaPedido"));      
    $("#modalCargaPedido").modal("show");
})