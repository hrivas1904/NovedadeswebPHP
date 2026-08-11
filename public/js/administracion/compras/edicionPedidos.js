$("#btnHabilitarEdicionPedido").on("click", function(){
    $(this).addClass("d-none");
    $("#btnGuardarCambiosPedidos").removeClass("d-none");
    $("#verPrioridad,#verCentroCosto, #verProveedor").prop("disabled",false);
    cargarCentrosCosto($("#verCentroCosto"), $("#modalDetallePedido"));
    cargarProveedores($("#verProveedor"), $("#modalDetallePedido"));
})

$("#btnGuardarCambiosPedidos").on("click", function(){
    $(this).addClass("d-none");
    $("#btnHabilitarEdicionPedido").removeClass("d-none");
    $("#verPrioridad, #verCentroCosto, #verProveedor").prop("disabled",true);
})

$("#btnSubirPresupuesto").on("click", function () {

    const pedidoId = $("#idPedido").val();

    if (!pedidoId) {
        Swal.fire({
            icon: "warning",
            title: "Pedido no identificado",
            text: "No se pudo determinar el pedido asociado.",
        });
        return;
    }

    const input = document.getElementById("inputPresupuesto");
    const archivo = input.files[0];

    if (!archivo) {
        Swal.fire({
            icon: "warning",
            title: "Seleccione un archivo",
            text: "Debe seleccionar un presupuesto para continuar.",
        });
        return;
    }

    const formData = new FormData();
    formData.append("archivo", archivo);

    $.ajax({
        url: `/administracion/compras/pedidos/${pedidoId}/presupuestos`,
        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function () {
            $("#btnSubirPresupuesto")
                .prop("disabled", true)
                .html(`
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>
                    Subiendo...
                `);
        },

        success: function () {

            $("#inputPresupuesto").val("");

            cargarAdjuntosPedido(pedidoId);

            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Presupuesto cargado correctamente.",
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
                    "No fue posible cargar el presupuesto.",
            });
        },

        complete: function () {

            $("#btnSubirPresupuesto")
                .prop("disabled", false)
                .html(`
                    <i class="fa-solid fa-upload me-2"></i>
                    Subir archivo
                `);
        },
    });
});

$(document).on("click", ".btnEliminarPresupuesto", function (e) {

    e.stopPropagation();

    const adjuntoId = $(this).data("id");
    const pedidoId = $("#idPedido").val();

    Swal.fire({
        title: "¿Eliminar presupuesto?",
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
            url: `/administracion/compras/presupuestos/${adjuntoId}`,
            type: "DELETE",

            headers: {
                "X-CSRF-TOKEN":
                    $('meta[name="csrf-token"]').attr("content"),
            },

            success: function () {

                // Usamos TU función existente
                cargarAdjuntosPedido(pedidoId);

                Swal.fire({
                    icon: "success",
                    title: "¡Operación exitosa!",
                    text: "Presupuesto eliminado correctamente.",
                    timer: 1500,
                    showConfirmButton: false,
                });
            },

            error: function (xhr) {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        xhr.responseJSON?.mensaje ??
                        "No fue posible eliminar el presupuesto.",
                });

            },
        });
    });
});