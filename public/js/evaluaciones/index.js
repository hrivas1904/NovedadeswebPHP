let tablaEvaluaciones=null;

$(document).ready(function(){
    tablaEvaluaciones=$("#tbEvaluaciones").DataTable({
        ajax:{
            url:"",
            type:"GET",
            dataType:"json",
            data:{
                p_periodo:$("#selectorFiltroPeriodoEdd").val(),
                p_area:$("#selectorFiltroAreaEdd").val(),
                p_servicio:$("#selectorFiltroServicioEdd").val(),
                p_estado:$("#selectorFiltroEstadoEdd").val(),
            },
        },
        language:{
            url:"/js/es-ES.json"
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "54vh",
        scrollCollapse: true,
        paging:false,
        dom: "tir"
    })
})