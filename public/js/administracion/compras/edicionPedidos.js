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