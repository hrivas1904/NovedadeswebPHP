const fechaActual = new Date();

const mesesFormatoLargo = { month: 'long' };
const mesFormateada = fechaActual.toLocaleDateString('es-ES', opciones).toUpperCase();

document.getElementById('detalleMesFecha').textContent = mesFormateada;

let tablaRepartoTurnos=$("#tbRepartosTurnos");

$(document).ready(function(){
    tablaRepartoTurnos.DataTable({
        ajax:{
            url:"/rrhh/personal/listarColaboradoresDatatable",
            type:"GET",
            dataSrc:"data",
        },
        scrollX:false,
        scrollY:getScrollY(),
        autoWidth:false,
        paging:false,
        language:{
            url:"/js/es-ES.json"
        },
        searching:false,
        columns:[
            {data:"COLABORADOR"},
            {data:"AREA"},
            {data:"DNI"},
            {data:"DNI"},
            {data:"DNI"},
            {data:"DNI"},
            {data:"DNI"},
            {data:"DNI"},
            {data:"DNI"},
        ],
    });
})

$('#modalEquidad').on('shown.bs.modal', function () {
    tablaPeriodos.DataTable().columns.adjust().draw();
});
