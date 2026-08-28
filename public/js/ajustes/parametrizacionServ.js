let tablaServiciosAreas = null; 

function cargarServiciosAreaSeleccionada(idArea, nombreArea){

    $("#nombreAreaTurnos").text(nombreArea);

    if ($.fn.DataTable.isDataTable("#tb_servicios_areas")) {
        tablaServiciosAreas.ajax.url(`/rrhh/servicios-empleados/por-area/${idArea}`).load();
        return;
    }

    tablaServiciosAreas = $("#tb_servicios_areas").DataTable({
        ajax: {
            url: `/rrhh/servicios-empleados/por-area/${idArea}`,
            type: "GET",
            dataSrc: ""
        },

        columns: [
            { data: "id_servicios", className:"text-start" },
            { data: "servicio", className:"text-start",
                render:function(data){
                    return `
                        <input type="text" class="form-control" value="${data}">
                    `
                }
             },
            { data: "estado", className: "text-center", orderable: false,
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
