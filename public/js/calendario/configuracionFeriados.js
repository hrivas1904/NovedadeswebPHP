let tablaFeriados = $("#tbFeriados");

$(document).ready(function () {
    tablaFeriados.DataTable({
        ajax: {
            url: "/eventosProgramados/lista",
            type:"GET",
            dataSrc:"data"
        },
        language:{
            url:"/js/es-ES.json"
        },
        paging: false,
        scrollX:false,
        scrollY:getScrollY(),
        autoWidth:true,
        dom:"tir",
        columns:[
            {data:"idEvento", className:"text-start", visible: false},
            {data:"fechaEvento", className:"text-start",
                render: function(data,type,row){
                    return `
                        <input class="form-control" type="date" value=${data}>
                    `
                }
            },
            {data:"fechaEvento",
                className:"text-start",
                render: function(data,type,row){
                    if (type === 'sort' || type === 'type') {
                        return data;
                    }
                    return obtenerNombreDia(data);
                }
            },
            {data:"tituloEvento", className:"text-start",
                render: function(data,type,row){
                    return `
                        <input class="form-control" type="text" value=${data}>
                    `
                }
            },
            {
                data: "tipoEvento",
                className: "text-start",
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        return data;
                    }

                    const valorActual = data || "";

                    return `
                        <select class="form-select form-select-sm select-tipo-evento" data-id="${row.id || row.idEvento}">
                            <option value="Feriado nacional" ${valorActual === "Feriado nacional" ? "selected" : ""}>Feriado nacional</option>
                            <option value="Feriado provincial" ${valorActual === "Feriado provincial" ? "selected" : ""}>Feriado provincial</option>
                            <option value="Capacitación" ${valorActual === "Capacitación" ? "selected" : ""}>Capacitación</option>
                        </select>
                    `;
                }
            },
            {
                data: "caracter", 
                className: "text-start",
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        return data;
                    }

                    const valorActual = data || "";
                    
                    return `
                        <select class="form-select form-select-sm select-caracter" data-id="${row.id || row.idEvento}">
                            <option value="Inamovible" ${valorActual === "Inamovible" ? "selected" : ""}>Inamovible</option>
                            <option value="Trasladable" ${valorActual === "Trasladable" ? "selected" : ""}>Trasladable</option>
                            <option value="Puente turístico" ${valorActual === "Puente turístico" ? "selected" : ""}>Puente turístico</option>
                            <option value="No laborable" ${valorActual === "No laborable" ? "selected" : ""}>No laborable</option>
                        </select>
                    `;
                }
            },
            {
                data: "verificado",
                className: "text-center align-middle",
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        return data;
                    }

                    const esVerificado = (data === true || data == 1 || data === "true");
                    const checkedAttr = esVerificado ? 'checked' : '';

                    return `
                        <div class="form-check form-switch m-0 p-0 d-flex justify-content-start">
                            <input class="form-check-input switch-verificado" 
                                type="checkbox" 
                                role="switch" 
                                data-id="${row.id}" 
                                ${checkedAttr} 
                                style="cursor: pointer; transform: scale(1.1); margin-left: 2em;">
                        </div>
                    `;
                }
            },
            {data:null,
                className:"text-center",
                render: function(data,row){
                    return `
                        <button type="button" class="btn" style="color: red;" title="Eliminar feriado">
                            <i class="fs-5 fa-regular fa-trash-can"></i>
                        </button>
                    `
                }
            },
        ]
    });
});
