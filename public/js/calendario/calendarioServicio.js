let fechaActual = new Date();
let idArea;
let tablaEventos;
let colaboradoresData = [];

//SELECTOR TURNOS --> NOCHE --> CAJA SI
$(document).on("change", ".selectTurno", function () {
    let fila = $(this).closest("tr");
    let turno = $(this).val();
    if (turno === "Noche") {
        fila.find(".selectCaja").val("1");
    }
});

//SELECTOR COLAB --> LEGAJO
$(document).on("select2:select", ".selectColab", function (e) {
    let data = e.params.data;
    let fila = $(this).closest("tr");
    console.log("Seleccionado:", data);
    fila.find(".inputLegajo").val(data.legajo);
});

//SELECTOR DE COLABS
$(document).ready(function () {
    const idArea = parseInt($("#idArea").val());

    $.ajax({
        url: "/calendario/colaboradores-area",
        type: "GET",
        data: { idArea: idArea },
        dataType: "json",
        success: function (data) {
            colaboradoresData = data;
        },
        error: function (err) {
            console.error("Error al cargar colaboradores:", err);
        },
    });
});

//CÁLCULO DE HORAS CON FECHAS
$(document).on("change", ".inputDesde, .inputHasta", function () {
    let fila = $(this).closest("tr");

    let desde = fila.find(".inputDesde").val();
    let hasta = fila.find(".inputHasta").val();

    if (!desde || !hasta) return;

    let f1 = new Date(desde);
    let f2 = new Date(hasta);

    if (f2 < f1) {
        Swal.fire("Error", "La fecha hasta no puede ser menor", "warning");
        fila.find(".inputHasta").val("");
        return;
    }

    let dias = Math.floor((f2 - f1) / (1000 * 60 * 60 * 24)) + 1;
    let horas = dias * 8;

    fila.find(".inputHoras").val(horas);
});

//NUEVO MÉTODO DE CARGA CON DATATABLE
$(document).ready(function () {
    tablaEventos = $("#tbDetalleEventos").DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        columnDefs: [{ targets: "_all", className: "align-middle" }],
        columns: [
            { width: "5%" }, // Legajo
            { width: "20%" }, // Colaborador
            { width: "10%" }, // Turno
            { width: "5%" }, // Caja
            { width: "15%" }, // Desde
            { width: "15%" }, // Hasta
            { width: "5%" }, // Horas
            { width: "20%" }, // Observ
            { width: "5%", className: "text-center" }, // Acciones
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json",
        },
    });
});

$("#btnAgregarDetalle").on("click", function () {
    let fila = tablaEventos.row
        .add([
            `<input type="text" class="form-control form-control inputLegajo" readonly>`,
            `<select class="form-select form-select selectColab">
                <option></option>
            </select>`,
            `<select class="form-select form-select selectTurno">
                <option value="">Turno</option>
                <option value="Mañana">Mañana</option>
                <option value="Refuerzo">Refuerzo</option>
                <option value="Tarde">Tarde</option>
                <option value="Noche">Noche</option>
                <option value="Noche">Adic Recep</option>             
            </select>`,
            `<select class="form-select form-select selectCaja">
                <option value="0">NO</option>
                <option value="1">SI</option>
            </select>`,
            `<input type="text" class="inputDesde form-control form-control">`,
            `<input type="text" class="inputHasta form-control form-control">`,
            `<input type="text" class="form-control form-control inputHoras">`,
            `<input type="text" class="form-control form-control inputObserv">`,
            `<button class="btn btn-danger btn btnEliminarFila">
                <i class="fa fa-trash"></i>
            </button>`,
        ])
        .draw()
        .node();

    flatpickr($(fila).find(".inputDesde")[0], {
        dateFormat: "Y-m-d",
        locale: "es",
    });

    flatpickr($(fila).find(".inputHasta")[0], {
        dateFormat: "Y-m-d",
        locale: "es",
    });

    let $select = $(fila).find(".selectColab");

    $select.select2({
        data: colaboradoresData,
        placeholder: "Seleccione un colaborador",
        allowClear: true,
        width: "100%",
        dropdownParent: $("#modalNuevaTarea"),
    });
});

$(document).on("click", ".btnEliminarFila", function () {
    tablaEventos.row($(this).closest("tr")).remove().draw();
});

//RECORRER DATATABLE Y GUARDAR EVENTO
function obtenerDatosTabla() {
    let datos = [];

    $("#tbDetalleEventos tbody tr").each(function () {
        let fila = $(this);

        let legajo = fila.find(".inputLegajo").val();
        let turno = fila.find(".selectTurno").val();
        let caja = fila.find(".selectCaja").val();
        let desde = fila.find(".inputDesde").val();
        let hasta = fila.find(".inputHasta").val();
        let horas = fila.find(".inputHoras").val();
        let observ = fila.find(".inputObserv").val();

        if (!legajo || !turno || !desde || !hasta) {
            return; // saltea filas incompletas
        }

        datos.push({
            legajo,
            fechaDesde: desde,
            fechaHasta: hasta,
            caja,
            turno,
            horas,
            observ,
        });
    });

    return datos;
}

$("#btnGuardarEvento").on("click", function (e) {
    e.preventDefault();

    let datos = obtenerDatosTabla();

    if (datos.length === 0) {
        Swal.fire("Error", "Debe agregar al menos un evento", "warning");
        return;
    }

    $.ajax({
        url: "/calendario/guardar",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ eventos: datos }),

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        beforeSend: function () {
            $("#btnGuardarEvento").prop("disabled", true).text("Guardando...");
        },

        success: function (response) {
            if (response.success) {
                Swal.fire("Éxito", response.message, "success");

                tablaEventos.clear().draw();
                $("#modalNuevaTarea").modal("hide");

                generarCalendario(fechaActual);
            }
        },

        error: function (xhr) {
            let errorMsg = "Ocurrió un error al guardar.";
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }

            Swal.fire("Error", errorMsg, "error");
        },

        complete: function () {
            $("#btnGuardarEvento").prop("disabled", false).text("Guardar");
        },
    });
});

//APERTURA Y CIERRA MODAL
$("#btnCrearEvento").on("click", function () {
    $("#modalNuevaTarea").modal("show");
});

function cerrarModalTarea() {
    $("#formRegistrarEvento")[0].reset();
    tablaEventos.clear().draw();
    $("#selectColab").val("").trigger("change");
    $("#modalNuevaTarea").modal("hide");
}

//VISUALIZACIÓN DEL CALENDARIO
function pintarEvento(contenedor, evento, fechaString) {
    const apellido = evento.colaborador.split(" ")[0];

    // Si tiene caja, mostramos la C (respetando tu UI)
    const badgeCaja = evento.caja_sigla
        ? `<span class="badge-caja">C</span>`
        : "";

    const idEvento = evento.idEvento ?? evento.id;

    const html = `
        <button
            type="button"
            class="event-item badge-${evento.turno_sigla}"
            data-id-evento="${idEvento}"
            data-fecha="${fechaString}"
            title="Quitar este día (${evento.colaborador})">

            <span class="event-nombre">${apellido}</span>
            <span class="event-info">
                ${evento.turno_sigla}
                ${badgeCaja}
            </span>
        </button>
    `;

    contenedor.append(html);
}

function modificarEventoDirecto(idEvento, fechaInterrupcion) {
    //if (!confirm("¿Quitar este día del evento?")) return;

    Swal.fire({
        title: "¿Borrar evento?",
        text: "El siguiente evento será borrado",
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#004a7c",
        confirmButtonColor: "#00b18d",
        confirmButtonText: "Borrar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/eventos/modificar",
                method: "POST",
                data: {
                    idEvento: idEvento,
                    fechaInterrupcion: fechaInterrupcion,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function () {
                    Swal.fire({
                        icon: "success",
                        title: "Evento modificado",
                        text: "El evento fue modificado correctamente",
                    });
                    generarCalendario(fechaActual);
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo modificar el evento",
                    });
                },
            });
        }
    });
}

$(document).on("click", ".event-item", function () {
    const idEvento = $(this).data("id-evento");
    const fecha = $(this).data("fecha");
    console.log("Evento: " + idEvento + ", " + "fecha: " + fecha);
    modificarEventoDirecto(idEvento, fecha);
});

function cargarEventosMes(year, month) {
    const idArea = $("#idArea").val();

    $.ajax({
        url: `/calendario/eventos/${idArea}`,
        type: "GET",
        success: function (eventos) {
            eventos.forEach((evento) => {
                // Iteramos día por día desde fechaDesde hasta fechaHasta
                let inicio = new Date(evento.fechaDesde + "T00:00:00");
                let fin = new Date(evento.fechaHasta + "T00:00:00");

                for (
                    let d = new Date(inicio);
                    d <= fin;
                    d.setDate(d.getDate() + 1)
                ) {
                    // IMPORTANTE: Formatear con ceros a la izquierda para coincidir con el data-fecha (YYYY-MM-DD)
                    let yyyy = d.getFullYear();
                    let mm = String(d.getMonth() + 1).padStart(2, "0");
                    let dd = String(d.getDate()).padStart(2, "0");
                    let fechaString = `${yyyy}-${mm}-${dd}`;

                    let selectorTurno = "";

                    if (evento.turno_sigla === "TM") selectorTurno = ".tm-row";
                    if (evento.turno_sigla === "TT") selectorTurno = ".tt-row";
                    if (
                        evento.turno_sigla === "TN" ||
                        evento.turno_sigla === "TR"
                    )
                        selectorTurno = ".tn-row";

                    // Buscamos el contenedor específico de ese turno en ese día
                    const contenedorTurno = $(
                        `.calendar-day[data-fecha="${fechaString}"] ${selectorTurno}`,
                    );

                    if (contenedorTurno.length) {
                        // Construimos el HTML simplificado para que entre en la columna
                        const apellido = evento.colaborador.split(" ")[0]; // Tomamos solo el primer apellido
                        const badgeCaja = evento.caja_sigla
                            ? "+" + evento.caja_sigla
                            : "";

                        const htmlEvento = `
                            <button
                                type="button"
                                class="event-item badge-turno-${evento.turno_sigla}"
                                data-id-evento="${evento.idEvento}"
                                data-fecha="${fechaString}"
                                <span class="event-text">
                                    ${apellido}
                                    <small>${evento.turno_sigla}${badgeCaja}</small>
                                </span>
                            </button>
                        `;

                        pintarEvento(contenedorTurno, evento, fechaString);
                    }
                }
            });
        },
    });
}

function generarCalendario(fecha) {
    const year = fecha.getFullYear();
    const month = fecha.getMonth();
    const primerDia = new Date(year, month, 1);
    const ultimoDia = new Date(year, month + 1, 0);
    const diasMes = ultimoDia.getDate();
    let diaSemanaInicio = primerDia.getDay();
    let ajusteDiaInicio = diaSemanaInicio === 0 ? 6 : diaSemanaInicio - 1;

    $("#calendarGrid").html("");
    const diasSemana = ["L", "M", "M", "J", "V", "S", "D"];

    diasSemana.forEach((dia) => {
        $("#calendarGrid").append(`
        <div class="calendar-weekday">${dia}</div>
    `);
    });

    $("#tituloMes").text(
        fecha.toLocaleString("es-AR", { month: "long", year: "numeric" }),
    );

    for (let i = 0; i < ajusteDiaInicio; i++) {
        $("#calendarGrid").append(`<div class="calendar-empty"></div>`);
    }

    for (let dia = 1; dia <= diasMes; dia++) {
        // Forzamos el formato YYYY-MM-DD con ceros a la izquierda
        const mm = String(month + 1).padStart(2, "0");
        const dd = String(dia).padStart(2, "0");
        const diaFecha = `${year}-${mm}-${dd}`;

        $("#calendarGrid").append(`
        <div class="calendar-day" data-fecha="${diaFecha}">
            <div class="calendar-day-header">${dia}</div>
            <div class="calendar-events-container">
                <div class="turno-row tm-row" data-turno="Mañana"></div>
                <div class="turno-row tt-row" data-turno="Tarde"></div>
                <div class="turno-row tn-row" data-turno="Noche"></div>
            </div>
        </div>
    `);
    }

    cargarEventosMes(year, month + 1);
}

$("#btnPrevMes").click(() => {
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    generarCalendario(fechaActual);
});

$("#btnNextMes").click(() => {
    fechaActual.setMonth(fechaActual.getMonth() + 1);
    generarCalendario(fechaActual);
});

generarCalendario(fechaActual);

document
    .getElementById("btnExportarImagen")
    .addEventListener("click", function () {
        const calendario = document.querySelector(".calendar-grid");

        const width = calendario.scrollWidth;
        const height = calendario.scrollHeight;

        html2canvas(calendario, {
            scale: 4,
            useCORS: true,
            allowTaint: false,
            backgroundColor: "#ffffff",
            width: width,
            height: height,
            windowWidth: width,
            windowHeight: height,

            onclone: function (clonedDoc) {
                const clonedCalendar =
                    clonedDoc.querySelector(".calendar-grid");

                clonedCalendar.style.backgroundColor = "#ffffff";
                clonedCalendar.style.width = width + "px";
                clonedCalendar.style.maxWidth = "none";

                const originalEvents = document.querySelectorAll(".event-item");
                const clonedEvents = clonedDoc.querySelectorAll(".event-item");

                clonedEvents.forEach((el, index) => {
                    const originalStyle = window.getComputedStyle(
                        originalEvents[index],
                    );

                    el.style.backgroundColor = originalStyle.backgroundColor;
                    el.style.color = originalStyle.color;
                    el.style.borderColor = originalStyle.borderColor;
                    el.style.borderStyle = "solid";
                    el.style.borderRadius = originalStyle.borderRadius;
                    el.style.fontWeight = "600";
                    el.style.display = "block";
                    el.style.opacity = "1";
                });

                clonedDoc.body.style.fontFamily = window.getComputedStyle(
                    document.body,
                ).fontFamily;
            },
        }).then((canvas) => {
            const link = document.createElement("a");
            const fecha = new Date().toISOString().slice(0, 10);
            link.download = `calendario_turnos_${fecha}_ULTRA.png`;
            link.href = canvas.toDataURL("image/png", 1.0);
            link.click();
        });
    });
