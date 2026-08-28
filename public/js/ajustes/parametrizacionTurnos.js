let tablaTurnosAreas = null;

function cargarTurnosAreaSeleccionada(idArea, nombreArea){

    if ($.fn.DataTable.isDataTable("#tb_turnos_areas")) {
        tablaTurnosAreas.ajax.url(`/rrhh/turnos/por-area/${idArea}`).load();
        return;
    }

    tablaTurnosAreas = $("#tb_turnos_areas").DataTable({
        ajax: {
            url: `/rrhh/turnos/por-area/${idArea}`,
            type: "GET",
            dataSrc: ""
        },

        columns: [
            { data: "id", className:"text-start" },
            { data: "nombre", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "hora_inicio", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "hora_fin", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "tolerancia_ingreso", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "cruza", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "horas_reales", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
            },
            { data: "horas_computadas", className:"text-start",
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
        autoWidth: false
    });
}