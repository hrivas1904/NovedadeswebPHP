let tablaPeriodos = $("#tbPeriodos");

$(document).ready(function () {
    tablaPeriodos.DataTable({
        ajax: {
            url: "",
            type:"GET",
            dataSrc:""
        },
        language:{
            url:"/js/es-ES.json"
        },
        paging: false,
        scrollX:false,
        autoWidth:true,
        ordering:false,
        searching:false,
        dom:"tr"
    });
});

$('#modalConfiguraciones').on('shown.bs.modal', function () {
    tablaPeriodos.DataTable().columns.adjust().draw();
});

