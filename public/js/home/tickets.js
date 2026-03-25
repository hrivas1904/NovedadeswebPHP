let tablaTickets = null;

$("#btnEmitirTicket").on("click", function () {
    let tipo = $("#selectorTipoTicket").val();
    let descripcion = $("input[name='descripcion']").val(); 

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
                $("#selectorTipoTicket").val("");
                $("input[name='descripcion']").val("");
                if (typeof tablaTickets !== 'undefined') {
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
                data: "descripcion",
                width: "45%",
                render: function (data) {
                    return `
                        <div class="descripcion-ticket">
                            ${data ?? ""}
                        </div>
                    `;
                },
            },
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
                        return `<span class="text-muted"></span>`;
                    }
                    if (d.estado === "RESUELTO") {
                        return `<span class="text-muted">—</span>`;
                    }
                    return `
                        <button class="btn btn-primary btnResolverTicket"
                            data-id="${d.id}" title="Resolver ticket">
                            <i class="fa-solid fa-check"></i>
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
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
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
});
