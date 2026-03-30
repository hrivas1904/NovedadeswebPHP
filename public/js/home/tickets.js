let tablaTickets = null;
let idTicketGlobal = null;

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$("#selectorTipoTicketArea").on("change", function () {
    $("#selectorTipoTicketRecursos").val("");
    $("#selectorTipoTicketSistemas").val("");
    $("#inputTipoTicket").val("");

    if ($(this).val() === "RRHH") {
        $("#divTipoTicketRecursos").removeClass("d-none");
        $("#divTipoTicketSistemas").addClass("d-none");
    } else if ($(this).val() === "SISTEMAS") {
        $("#divTipoTicketSistemas").removeClass("d-none");
        $("#divTipoTicketRecursos").addClass("d-none");
    }
});

$("#selectorTipoTicketRecursos").on("change", function () {
    $("#inputTipoTicket").val($(this).val());
});

$("#selectorTipoTicketSistemas").on("change", function () {
    $("#inputTipoTicket").val($(this).val());
});

function limpiarFormulario() {
    $("#selectorTipoTicketArea").val("");
    $("#selectorTipoTicketRecursos").val("");
    $("#selectorTipoTicketSistemas").val("");
    $("#inputTipoTicket").val("");
    $("#inputDescripcion").val("");

    $("#divTipoTicketRecursos").addClass("d-none");
    $("#divTipoTicketSistemas").addClass("d-none");
}

$("#btnEmitirTicket").on("click", function () {
    let tipo = $("#inputTipoTicket").val();
    let descripcion = $("#inputDescripcion").val();
    let area = $("#selectorTipoTicketArea").val();

    if (!area) {
        Swal.fire(
            "Atención",
            "Debe seleccionar un área para el ticket",
            "warning",
        );
        return;
    }

    if (!tipo) {
        Swal.fire("Atención", "Debe seleccionar un tipo de ticket", "warning");
        return;
    }

    if (!descripcion) {
        Swal.fire("Atención", "Debe ingresar una descripción", "warning");
        return;
    }

    $.ajax({
        url: "/tickets/registrar",
        method: "POST",
        data: {
            tipo: tipo,
            descripcion: descripcion,
            area: area,
        },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            const icono = resp.ok ? "success" : "error";
            const titulo = resp.ok ? "Operación Exitosa" : "Error";

            Swal.fire({
                icon: icono,
                title: titulo,
                text: resp.mensaje,
            });

            if (resp.ok) {
                limpiarFormulario();
                if (typeof tablaTickets !== "undefined") {
                    tablaTickets.ajax.reload(null, false);
                }
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo conectar con el servidor", "error");
        },
    });
});

function resolverTicket(idTicket) {
    Swal.fire({
        title: "¿Resolver ticket?",
        text: "El ticket se marcará como resuelto",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, resolver",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "btn-primario",
            cancelButton: "btn-secundario",
        },
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: "/tickets/resolver",
            type: "POST",
            data: {
                idTicket: idTicket,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            success: function (resp) {
                if (resp.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "Ticket resuelto",
                        text: resp.mensaje,
                    });

                    tablaTickets.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", resp.mensaje, "error");
                }
            },

            error: function () {
                Swal.fire("Error", "No se pudo resolver el ticket", "error");
            },
        });
    });
}

$("#inputMensaje").on("keypress", function (e) {
    if (e.which === 13) {
        $("#btnEnviarMensaje").click();
    }
});

$("#btnEnviarMensaje").on("click", function () {
    let mensaje = $("#inputMensaje").val().trim();

    if (!mensaje) return;

    $.ajax({
        url: "/tickets/responder",
        type: "POST",
        data: {
            idTicket: idTicketGlobal,
            mensaje: mensaje,
        },

        success: function (resp) {
            $("#inputMensaje").val("");

            verChat(idTicket);
        },
        error: function () {
            Swal.fire("Error", "No se pudo enviar el mensaje", "error");
        },
    });
});

function abrirChat(idTicket) {
    $("#idTicketActual").val(idTicket);
    $("#modalChatTicket").modal("show");
    verChat(idTicket);
}

function verChat(idTicket) {
    $.ajax({
        url: "/tickets/verChat",
        type: "GET",
        data: { id: idTicket },

        success: function (res) {
            let html = "";

            res.forEach((m) => {
                let esMio = m.usuario_id == $("#chatContainer").data("user-id");

                html += `
                    <div class="mb-3 d-flex ${esMio ? "text-right" : "text-left"}">                        
                        <div class="d-flex ${esMio ? "flex-row-reverse" : ""} align-items-start" style="max-width: 70%;">                            
                            <div class="mx-2">
                                <i class="fa-solid fa-circle-user fs-4 text-secondary"></i>
                            </div>
                            <div class="bg-white p-2 rounded shadow-sm">                                
                                <small class="text-muted d-block mb-1">
                                    ${m.name ?? ""} - ${m.fechaHora}
                                </small>
                                <div>
                                    ${m.mensaje}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $("#chatContainer").html(html);

            // Scroll automático al final
            $("#chatContainer").scrollTop($("#chatContainer")[0].scrollHeight);
        },
    });
}

$(document).ready(function () {
    tablaTickets = $("#tablaTickets").DataTable({
        ajax: {
            url: "/tickets/lista",
            type: "GET",
            dataSrc: "data",
        },

        columns: [
            { data: "id", width: "3%" },
            { data: "fecha", width: "6%" },
            { data: "colaborador", width: "16%" },
            { data: "tipo" },
            {
                data: "estado",
                width: "5%",
                render: function (data) {
                    if (data === "PENDIENTE") {
                        return `<span class="badge bg-warning">Pendiente</span>`;
                    }

                    if (data === "RESUELTO") {
                        return `<span class="badge bg-success">Resuelto</span>`;
                    }

                    return data;
                },
            },
            { data: "fechaResolucion", width: "6%" },
            { data: "quienResuelve", width: "10%" },

            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function (d) {                    
                    if (USER_ROLE !== "Administrador/a") {
                        return `
                            <button class="btn btn-secondary btnVerChat"
                                data-id="${d.id}" title="Resolver ticket">
                                <i class="fa-regular fa-message"></i>
                            </button>
                        `;
                    }
                    if (d.estado === "RESUELTO") {
                        return `
                            <button class="btn btn-secondary btnVerChat"
                                data-id="${d.id}" title="Resolver ticket">
                                <i class="fa-regular fa-message"></i>
                            </button>
                        `;
                    }
                    return `
                        <button class="btn btn-primary btnResolverTicket"
                            data-id="${d.id}" title="Ver chat">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <button class="btn btn-secondary btnVerChat"
                            data-id="${d.id}" title="Resolver ticket">
                            <i class="fa-regular fa-message"></i>
                        </button>                        
                    `;
                },
            },
        ],
        order: [[0, "desc"]],
        scrollX: true,
        paging: false,
        scrollCollapse: true,
        scrollY: "32vh",
        language: {
            lengthMenu: "_MENU_",
            paginate: {
                first: "<<",
                previous: "<",
                next: ">",
                last: ">>",
            },
        },
        autoWidth: false,
    });

    $(document).on("click", ".btnResolverTicket", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idTicket = $(this).data("id");
        console.log("Id registro recibido: " + idTicket);
        resolverTicket(idTicket);
    });

    $(document).on("click", ".btnVerChat", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idTicket = $(this).data("id");
        console.log("Id registro recibido: " + idTicket);
        idTicketGlobal = idTicket;
        abrirChat(idTicket);
    });
});
