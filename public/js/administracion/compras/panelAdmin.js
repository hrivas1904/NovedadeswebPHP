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
            d.estados = getEstadosSeleccionados().join(",") || null;
            d.autorizaciones =
                getAutorizacionesSeleccionadas().join(",") || null;
            d.desde = $("#filtroDesde").val();
            d.hasta = $("#filtroHasta").val();
            d.tieneFactura = $("#filtroFactura").val();
        },
    },
    order: [[0, "desc"]],
    destroy: true,
    responsive: false,
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
    createdRow: function (row, data) {
        if (
            data.autorizacion === "REQUIERE AUTORIZACIÓN GERENTE" &&
            data.estado === "PENDIENTE"
        ) {
            $(row).addClass("pedido-requiere-gerencia");
        }
    },
    columns: [
        {
            data: "fecha",
            width: "6%",
            render: function (data, type, row) {
                if (type === "sort" || type === "type") {
                    return data;
                }
                return formatearFechaArgentina(data);
            },
        },
        { data: "prioridad", width: "7%" },
        { data: "solicitante", width: "10%" },
        { data: "sector", width: "9%", visible: false },
        { data: "proveedor", width: "12%" },
        { data: "descripcion", className: "align-middle", width: "24%" },
        { data: "autorizacion", width: "11%" },
        { data: "estado", width: "8%" },
        {
            data: null,
            width: "7%",
            className: "text-center",
            render: function (data, type, row) {
                let label = null;
                if (data.factura_descargada === 0) {
                    label = `
                        <span class="badge bg-primary">Nuevo</span>
                    `;
                } else {
                    label = ``;
                }
                if (data.tiene_factura) {
                    return `
                        <span class="badge bg-success">
                            <a href="/administracion/compras/factura/${row.id}/descargar"
                            class="text-white text-decoration-none btnFactura"
                            title="Factura disponible. Descargar.">
                                <i class="fa-solid fa-file-arrow-down"></i> Factura
                            </a>
                        </span>
                        ${label}
                    `;
                }
                return `
                    
                `;
            },
        },        
        {
            data: null,
            width: "5%",
            orderable: false,
            searchable: false,
            className: "text-center acciones-nowrap",

            render: function (data) {

                if (!PUEDE_AUTORIZAR_PEDIDOS) {
                    return "";
                }

                if (data.estado === "GENERADO") {
                    return "";
                }

                const ES_GERENTE = PUEDE_APROBAR_GERENCIA;

                let acciones = "";

                // AUTORIZACIÓN PENDIENTE
                if (data.autorizacion === "PENDIENTE") {

                    if (ES_GERENTE) {
                        return "";
                    }

                    acciones = `
                        <li>
                            <button type="button"
                                class="dropdown-item btnAutorizar"
                                data-id="${data.id}">
                                <i class="fa-regular fa-square-check me-2 text-success"></i>
                                Aprobar pedido
                            </button>
                        </li>

                        <li>
                            <button type="button"
                                class="dropdown-item btnRechazar"
                                data-id="${data.id}">
                                <i class="fa-regular fa-circle-xmark me-2 text-danger"></i>
                                Rechazar pedido
                            </button>
                        </li>
                    `;
                }

                // AUTORIZACIÓN DE GERENCIA
                else if (
                    data.autorizacion === "REQUIERE AUTORIZACIÓN GERENTE" &&
                    ES_GERENTE
                ) {

                    acciones = `
                        <li>
                            <button type="button"
                                class="dropdown-item btnAutorizarGerente"
                                data-id="${data.id}">
                                <i class="fa-regular fa-square-check me-2 text-success"></i>
                                Autorizar como gerente
                            </button>
                        </li>

                        <li>
                            <button type="button"
                                class="dropdown-item btnRechazar"
                                data-id="${data.id}">
                                <i class="fa-regular fa-circle-xmark me-2 text-danger"></i>
                                Rechazar pedido
                            </button>
                        </li>
                    `;
                }

                // PEDIDO APROBADO
                else if (data.autorizacion === "APROBADA") {

                    return `
                        <input
                            type="checkbox"
                            class="form-check-input chkPedido"
                            value="${data.id}">
                    `;
                }

                // SIN ACCIONES DISPONIBLES
                if (!acciones) {
                    return "";
                }

                // MENÚ DE TRES PUNTOS
                return `
                    <div class="dropdown d-inline-block">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary border-0 btn-menu-pedido"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Opciones del pedido">

                            <i class="fa-solid fa-ellipsis-vertical"></i>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            ${acciones}
                        </ul>

                    </div>
                `;
            }
        },
    ],
});

$(document).on("click", ".btnFactura", async function (e) {
    e.preventDefault();
    e.stopPropagation();

    const url = $(this).attr("href");

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error("Error al descargar la factura");
        }

        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);

        const disposition = response.headers.get("Content-Disposition") || "";
        const filenameUTF8 = disposition.match(/filename\*=UTF-8''([^;]+)/i);
        const filenameNormal = disposition.match(/filename="?([^";]+)"?/i);

        let nombreArchivo = "factura.pdf";

        if (filenameUTF8) {
            nombreArchivo = decodeURIComponent(filenameUTF8[1]);
        } else if (filenameNormal) {
            nombreArchivo = filenameNormal[1];
        }

        const enlace = document.createElement("a");
        enlace.href = blobUrl;
        enlace.download = nombreArchivo;

        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();

        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);

        // Refrescar DataTable
        const tabla = $("#tablaPedidosCompras").DataTable();

        tabla.ajax.reload(null, false);
    } catch (error) {
        console.error("Error descargando factura:", error);

        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo descargar la factura.",
        });
    }
});

$(window).on("resize", () =>
    $("#tablaPedidosCompras").DataTable().columns.adjust(),
);

let PEDIDO_RECHAZO_ID = null;

$(document).on("click", ".btnRechazar", function () {
    PEDIDO_RECHAZO_ID = $(this).data("id");

    $("#inputObservRechazo").val("");
    $("#modalObservacionRechazo").modal("show");
});

$("#btnConfirmarRechazo").on("click", function () {
    const motivo = $("#inputObservRechazo").val().trim();

    $.ajax({
        url: "/administracion/compras/rechazar",
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            id: PEDIDO_RECHAZO_ID,
            motivoRechazo: motivo,
        },

        success: function () {
            $("#modalObservacionRechazo").modal("hide");

            PEDIDO_RECHAZO_ID = null;
            $("#inputObservRechazo").val("");

            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Pedido rechazado correctamente.",
                timer: 1800,
                showConfirmButton: false,
            });

            $("#tablaPedidosCompras").DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.mensaje ??
                    "No fue posible rechazar el pedido.",
            });
        },
    });
});

$("#btnCancelarRechazo").on("click", function () {
    PEDIDO_RECHAZO_ID = null;
    $("#inputObservRechazo").val("");
    $("#modalObservacionRechazo").modal("hide");
});

$(document).on(
    "change",
    ".check-Prioridades, .check-Autorizacion, .check-Estados",
    function () {
        $("#tablaPedidosCompras").DataTable().ajax.reload();
    },
);

$("#filtroDesde, #filtroHasta, #filtroFactura").on("change", function () {
    $("#tablaPedidosCompras").DataTable().ajax.reload();
});

$("#btn-limpiar-filtros").on("click", function () {
    $(".check-Prioridades, .check-Autorizacion, .check-Estados").prop(
        "checked",
        false,
    );
    $("#filtroDesde, #filtroHasta, #filtroFactura").val(null);
    $("#tablaPedidosCompras").DataTable().ajax.reload();
});

$("#tablaPedidosCompras tbody").on("click", "tr", function (e) {
    if ($(e.target).closest("button").length) return;
    if ($(e.target).closest("input").length) return;
    if ($(e.target).closest("a").length) return;
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
            cargarCentrosCosto(
                $("#verCentroCosto"),
                $("#modalDetallePedido"),
                c.centroCosto,
            );
            cargarProveedores(
                $("#verProveedor"),
                $("#modalDetallePedido"),
                c.proveedor,
            );
            $("#verEstado").val(c.estado);
            $("#verAutorizacion").val(c.autorizacion);
            $("#verDescripcion").val(c.descripcion);
            $("#reqAutGerente").val(c.autGerente);
            $("#estadoAutGerente").val(c.autorizacion_gerente);
            $("#auditorPedido").val(c.auditor);
            $("#verMotivoRechazo").val(c.motivoRechazo ?? "");
            $("#verMoneda").val(c.moneda);

            let tbody = $("#detalleProductosBody");
            tbody.empty();

            response.detalle.forEach(function (item, index) {
                const precioValor =
                    item.precio === null || item.precio === undefined
                        ? ""
                        : Number(item.precio).toLocaleString("es-AR", {
                              minimumFractionDigits: 2,
                              maximumFractionDigits: 2,
                          });

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
                                type="text"
                                class="form-control text-end input-precio"
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
                    item.producto_id,
                );
            });

            $("#modalDetallePedido").data("pedido-id", id);

            cargarAdjuntosPedido(id);
            cargarOrdenesCompra(id);
            cargarRemitos(id);
            cargarFacturas(id);
            cargarObservaciones(id);
            $("#modalDetallePedido").modal("show");

            if ($("#verEstado").val() === "GENERADO") {
                $("#btnRegenerarExcelFinnegans").removeClass("d-none");
            } else {
                $("#btnRegenerarExcelFinnegans").addClass("d-none");
            }

            if ($("#verAutorizacion").val() === "APROBADA") {
                $(
                    "#divPresupuestosAdjuntos, #btnHabilitarEdicionPedido",
                ).addClass("d-none");
            } else {
                $(
                    "#divPresupuestosAdjuntos, #btnHabilitarEdicionPedido",
                ).removeClass("d-none");
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
            const icono = adj.esImagen ? "fa-file-image" : "fa-file-pdf";

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

        if ($("#verAutorizacion").val() === "APROBADA") {
            $(".btnEliminarPresupuesto").addClass("d-none");
        }
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
    $.get(
        `/administracion/pedidos/${pedidoActualId}/listarObservaciones`,
        function (data) {
            const $hilo = $("#hiloObservaciones").empty();
            if (data.length === 0) {
                $hilo.html(
                    '<div class="text-muted small">Sin observaciones todavía.</div>',
                );
                return;
            }
            data.forEach((obs) => {
                $hilo.append(`
                <div class="mb-2">
                    <div class="small text-muted"><strong>${obs.usuario_nombre}</strong> · ${obs.created_at}</div>
                    <div>${obs.mensaje}</div>
                </div>
            `);
            });
            $hilo.scrollTop($hilo[0].scrollHeight);
        },
    );
}

$(document).on("click", "#btnEnviarObservacion", function () {
    console.log("CLICK NATIVO");
    const mensaje = $("#inputNuevaObservacion").val().trim();
    console.log(mensaje);
    if (!mensaje || !pedidoActualId) return;
    console.log(pedidoActualId);
    $.ajax({
        url: `/administracion/pedidos/${pedidoActualId}/agregarObservaciones`,
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: { mensaje: mensaje },
        success: function (obs) {
            $("#hiloObservaciones").append(`
                <div class="mb-2">
                    <div class="small text-muted"><strong>${obs.usuario_nombre}</strong> · ${obs.created_at}</div>
                    <div>${obs.mensaje}</div>
                </div>
            `);
            $("#inputNuevaObservacion").val("");
            $("#hiloObservaciones").scrollTop(
                $("#hiloObservaciones")[0].scrollHeight,
            );
        },
    });
});

$(document).on("click", "#btnSubirFactura", function () {
    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    const input = $("#inputFactura")[0];
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
        url: `/administracion/compras/${pedidoId}/factura`,
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function () {
            $("#btnSubirFactura").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Subiendo...
                `);
        },

        success: function (response) {
            $("#inputFactura").val("");

            Swal.fire({
                icon: "success",
                title: "Factura adjunta",
                text: response.mensaje ?? "Factura adjuntada correctamente.",
                timer: 1800,
                showConfirmButton: false,
            });

            cargarFacturas(pedidoId);
        },

        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "No se pudo subir la factura",
                text:
                    xhr.responseJSON?.message ??
                    xhr.responseJSON?.mensaje ??
                    "Ocurrió un error.",
            });
        },

        complete: function () {
            $("#btnSubirFactura").prop("disabled", false).html(`
                    <i class="fa-solid fa-upload me-2"></i>
                    Subir archivo
                `);
        },
    });
});

function cargarFacturas(pedidoId) {
    const contenedor = $("#listaFacturasAdjuntas");

    // Limpiamos inmediatamente las facturas del pedido anterior
    contenedor.empty();

    contenedor.html(`
        <div class="text-muted small">
            Cargando facturas...
        </div>
    `);

    $.ajax({
        url: `/administracion/compras/${pedidoId}/facturas`,
        type: "GET",

        success: function (facturas) {
            // Evita mostrar una respuesta vieja si el usuario cambió de pedido
            const pedidoActual = $("#modalDetallePedido").data("pedido-id");

            if (parseInt(pedidoActual) !== parseInt(pedidoId)) {
                return;
            }

            contenedor.empty();

            facturas.forEach(function (factura) {
                contenedor.append(`
                    <div class="d-flex justify-content-between align-items-center
                                border rounded p-2 mb-2">

                        <div class="d-flex align-items-center gap-2 overflow-hidden">

                            <i class="fa-solid fa-file-invoice text-primary"></i>

                            <a
                                href="/storage/${factura.archivo}"
                                target="_blank"
                                class="text-decoration-none text-truncate"
                                title="${factura.nombre_original}"
                            >
                                ${factura.nombre_original}
                            </a>

                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger btnEliminarFactura"
                            data-id="${factura.id}"
                            title="Eliminar factura"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>
                `);
            });
        },

        error: function () {
            const pedidoActual = $("#modalDetallePedido").data("pedido-id");

            if (parseInt(pedidoActual) !== parseInt(pedidoId)) {
                return;
            }

            contenedor.html(`
                <div class="text-danger small">
                    No se pudieron cargar las facturas.
                </div>
            `);
        },
    });
}

$(document).on("click", ".btnEliminarFactura", function (e) {
    e.stopPropagation();

    const facturaId = $(this).data("id");
    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    if (!facturaId || !pedidoId) {
        Swal.fire(
            "Atención",
            "No se pudo identificar la factura o el pedido.",
            "warning",
        );

        return;
    }

    Swal.fire({
        title: "¿Eliminar factura?",
        text: "El archivo será eliminado permanentemente.",
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
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: `/administracion/compras/facturas/${facturaId}`,
            type: "DELETE",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            beforeSend: function () {
                Swal.fire({
                    title: "Eliminando...",
                    allowOutsideClick: false,
                    allowEscapeKey: false,

                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },

            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Factura eliminada",
                    text:
                        response.mensaje ?? "Factura eliminada correctamente.",
                    timer: 1800,
                    showConfirmButton: false,
                });

                cargarFacturas(pedidoId);
            },

            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "No se pudo eliminar",
                    text: xhr.responseJSON?.mensaje ?? "Ocurrió un error.",
                });
            },
        });
    });
});

$(document).on("click", "#btnSubirRemito", function () {
    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    const input = $("#inputRemito")[0];
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
        url: `/administracion/compras/${pedidoId}/remito`,
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function () {
            $("#btnSubirRemito").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Subiendo...
                `);
        },

        success: function (response) {
            $("#inputRemito").val("");

            Swal.fire({
                icon: "success",
                title: "Remito adjunto",
                text: response.mensaje ?? "Remito adjuntado correctamente.",
                timer: 1800,
                showConfirmButton: false,
            });

            cargarRemitos(pedidoId);
        },

        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "No se pudo subir el remito",
                text:
                    xhr.responseJSON?.message ??
                    xhr.responseJSON?.mensaje ??
                    "Ocurrió un error.",
            });
        },

        complete: function () {
            $("#btnSubirRemito").prop("disabled", false).html(`
                    <i class="fa-solid fa-upload me-2"></i>
                    Subir archivo
                `);
        },
    });
});

function cargarRemitos(pedidoId) {
    const contenedor = $("#listaRemitosAdjuntos");

    contenedor.empty();

    contenedor.html(`
        <div class="text-muted small">
            Cargando remitos...
        </div>
    `);

    $.ajax({
        url: `/administracion/compras/${pedidoId}/remitos`,
        type: "GET",

        success: function (remitos) {
            const pedidoActual = $("#modalDetallePedido").data("pedido-id");

            if (parseInt(pedidoActual) !== parseInt(pedidoId)) {
                return;
            }

            contenedor.empty();

            if (!remitos.length) {
                contenedor.html(`
                    <div class="text-muted small">
                        No hay remitos asociados.
                    </div>
                `);

                return;
            }

            remitos.forEach(function (remito) {
                contenedor.append(`
                    <div class="d-flex justify-content-between align-items-center
                                border rounded p-2 mb-2">

                        <div class="d-flex align-items-center gap-2 overflow-hidden">

                            <i class="fa-solid fa-file-signature text-primary"></i>

                            <a
                                href="/storage/${remito.archivo}"
                                target="_blank"
                                class="text-decoration-none text-truncate"
                                title="${remito.nombre_original}"
                            >
                                ${remito.nombre_original}
                            </a>

                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger btnEliminarRemito"
                            data-id="${remito.id}"
                            title="Eliminar remito"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>
                `);
            });
        },

        error: function () {
            const pedidoActual = $("#modalDetallePedido").data("pedido-id");

            if (parseInt(pedidoActual) !== parseInt(pedidoId)) {
                return;
            }

            contenedor.html(`
                <div class="text-danger small">
                    No se pudieron cargar los remitos.
                </div>
            `);
        },
    });
}

$(document).on("click", ".btnEliminarRemito", function (e) {
    e.stopPropagation();

    const remitoId = $(this).data("id");
    const pedidoId = $("#modalDetallePedido").data("pedido-id");

    if (!remitoId || !pedidoId) {
        Swal.fire(
            "Atención",
            "No se pudo identificar el remito o el pedido.",
            "warning",
        );

        return;
    }

    Swal.fire({
        title: "¿Eliminar remito?",
        text: "El archivo será eliminado permanentemente.",
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
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: `/administracion/compras/remitos/${remitoId}`,
            type: "DELETE",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            beforeSend: function () {
                Swal.fire({
                    title: "Eliminando...",
                    allowOutsideClick: false,
                    allowEscapeKey: false,

                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },

            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Remito eliminado",
                    text: response.mensaje ?? "Remito eliminado correctamente.",
                    timer: 1800,
                    showConfirmButton: false,
                });

                cargarRemitos(pedidoId);
            },

            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "No se pudo eliminar",
                    text: xhr.responseJSON?.mensaje ?? "Ocurrió un error.",
                });
            },
        });
    });
});

$("#btnAbrirModalNuevoPedido").on("click", function () {
    cargarCentrosCosto($("#cmbCentroCostoModal"), $("#modalCargaPedido"));
    cargarProveedores($("#cmbProveedorModal"), $("#modalCargaPedido"));
    $("#modalCargaPedido").modal("show");
});
