let tablaFuncionesAdicAreas = null;

function cargarFuncionesAreaSeleccionada(idArea, nombreArea){

    if ($.fn.DataTable.isDataTable("#tb_funciones_adic")) {
        tablaTurnosAreas.ajax.url(`/rrhh/funcionesAdicionales/por-area/${idArea}`).load();
        return;
    }

    tablaTurnosAreas = $("#tb_funciones_adic").DataTable({
        ajax: {
            url: `/rrhh/funcionesAdicionales/por-area/${idArea}`,
            type: "GET",
            dataSrc: ""
        },

        columns: [
            { data: "id", className:"text-start" },
            { data: "funcion", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "marca", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "cod_liq", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "unidad", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "activo", className: "text-center", orderable: false,
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }
                    const checked = Number(data) === 1 ? "checked" : "";
                    return `
                        <div class="form-check form-switch d-flex justify-content-center m-0">
                            <input
                                class="form-check-input switchActivoServicio"
                                type="checkbox"
                                role="switch"
                                data-id="${row.id}"
                                ${checked}>
                        </div>
                    `;
                },
            },
        ],

        language: {
            url: "/js/es-ES.json"
        },
        paging: false,
        searching: false,
        info: false,
        autoWidth: false,
        scrollX:false,
        scrollY:"55vh",
        scrollCollapse: true,
    });
}