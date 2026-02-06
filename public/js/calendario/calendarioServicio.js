let fechaActual = new Date();
let idArea;

$(document).ready(function () {
    function validarFechas() {
        const fechaDesde = $("#inputFechaDesde").val();
        const fechaHasta = $("#inputFechaHasta").val();

        if (fechaDesde && fechaHasta) {
            if (new Date(fechaDesde) > new Date(fechaHasta)) {
                alert("La fecha Desde no puede ser mayor a la fecha Hasta");
                $("#inputFechaHasta").val("");
                $("#inputFechaDesde").val("");
            }
        }
    }
    $("#inputFechaDesde, #inputFechaHasta").on("change", function () {
        validarFechas();
    });
});

$("#selectTurnoEvento").on("change", function () {
    const selectorTurno = $(this).val();

    if (selectorTurno === "Noche") {
        $("#selectCaja").val("1");
    }
});

//select2 de colabs
$("#selectColab").on("select2:select", function (e) {
    let data = e.params.data;
    console.log("Seleccionado:", data);
    $("#inputLegajo").val(data.legajo);
    $("#inputServicio").val(data.servicio);
});

$(document).ready(function () {
    const idArea = parseInt($("#idArea").val());
    const $select = $("#selectColab");

    $.ajax({
        url: "/calendario/colaboradores-area",
        type: "GET",
        data: { idArea: idArea },
        dataType: "json",
        success: function (data) {
            // Inicializar Select2 UNA SOLA VEZ con los datos
            $select.select2({
                data: data,
                placeholder: "Seleccione un colaborador",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#modalNuevaTarea")
            });
        },
        error: function (err) {
            console.error("Error al cargar colaboradores:", err);
        }
    });
});

$("#btnCrearEvento").on("click", function () {
    $("#modalNuevaTarea").modal("show");
});

$("#btnCerrarModalEvento").on("click", function () {
    $("#formRegistrarEvento")[0].reset();
    $("#selectColab").val("").trigger("change");
    $("#modalNuevaTarea").modal("hide");
});

function pintarEvento(contenedor, evento) {
    const apellido = evento.colaborador.split(" ")[0];
    const badgeCaja = evento.caja_sigla
        ? `<span class="badge-caja">C</span>`
        : "";

    const html = `
        <div class="event-item badge-${evento.turno_sigla}"
             title="${evento.colaborador}">
            <span class="event-nombre">${apellido}</span>
            <span class="event-info">${evento.turno_sigla}${badgeCaja}</span>
        </div>
    `;

    contenedor.append(html);
}

$(document).ready(function () {
    $("#btnGuardarEvento").on("click", function (e) {
        e.preventDefault();

        // Referencia al formulario
        const formulario = $("#formRegistrarEvento");

        // Validación básica de HTML5 (required)
        if (formulario[0].checkValidity()) {
            const formData = formulario.serialize();

            $.ajax({
                url: "/calendario/guardar", // Genera la URL de Laravel
                type: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                beforeSend: function () {
                    $("#btnGuardarEvento")
                        .prop("disabled", true)
                        .text("Guardando...");
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        formulario[0].reset();
                        $("#selectColab").val(null).trigger("change");
                        $("#modalNuevaTarea").modal("hide");
                        generarCalendario(fechaActual);
                    }
                },
                error: function (xhr) {
                    let errorMsg = "Ocurrió un error al guardar.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                },
                complete: function () {
                    $("#btnGuardarEvento")
                        .prop("disabled", false)
                        .text("Guardar");
                },
            });
        } else {
            formulario[0].reportValidity();
        }
    });
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
                    if (evento.turno_sigla === "TN"||evento.turno_sigla === "TR") selectorTurno = ".tn-row";

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
                            <div class="event-item badge-turno-${evento.turno_sigla}" title="${evento.colaborador}">
                                <span>${apellido}</span>
                                <small>${evento.turno_sigla}${badgeCaja}</small>
                            </div>
                        `;

                        pintarEvento(contenedorTurno, evento);
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
            scale: 4, // Ultra HD
            useCORS: true,
            allowTaint: true,
            backgroundColor: "#ffffff",
            width: width,
            height: height,
            windowWidth: width,
            windowHeight: height,

            onclone: function (clonedDoc) {
                const clonedCalendar =
                    clonedDoc.querySelector(".calendar-grid");

                // ===== FIX layout =====
                clonedCalendar.style.width = width + "px";
                clonedCalendar.style.maxWidth = "none";

                // ===== FIX eventos (texto + color) =====
                clonedDoc.querySelectorAll(".event-item").forEach((el) => {
                    const style = window.getComputedStyle(el);

                    // Forzar estilos inline (html2canvas ama esto)
                    el.style.backgroundColor = style.backgroundColor;
                    el.style.color = style.color;
                    el.style.borderRadius = style.borderRadius;
                    el.style.fontWeight = "600";
                    el.style.opacity = "1";
                });

                // ===== FIX tipografías =====
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
