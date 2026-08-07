let tablaTransacciones = $("#tbTransacciones");

function getScrollY() {
    return window.innerWidth < 768 ? "40vh" : "70vh";
}

tablaTransacciones.DataTable({
    destroy: true,
    ajax: {
        url: "/log/listarLogTransaccional",
        dataSrc: "",
        data: function (d) {
            d.fechaDesde=$("#inputFechaDesde").val();
            d.fechaHasta=$("#inputFechaHasta").val();
        },
    },
    language: {
        url: "/js/es-ES.json",
    },
    order: [[0, 'desc']],
    scrollX: false,
    scrollY: getScrollY(),
    autoWidth: true,
    paging: false,
    scrollCollapse: true,
    processing:true,
    dom: "tir",
    columns: [
        { data: "idLog", className:"text-start" },
        { data: "fecha", className:"text-start" },
        { data: "accion", className:"text-start" },
        { data: "usuario", className:"text-start" },
        { data: "modulo", className:"text-start" },
        { data: "tablaAfectada", className:"text-start" },
        { data: "idRegistroAfectado", className:"text-start" },
        { data: "descripcion", className:"text-start" },
    ],
});

$(document).on("change", "#inputFechaDesde, #inputFechaHasta", function(){
    tablaTransacciones.DataTable().ajax.reload();
})

$("#btnLimpiarFiltros").on("click", function(){
    $("#inputFechaDesde, #inputFechaHasta").val("");
    tablaTransacciones.DataTable().ajax.reload();
})