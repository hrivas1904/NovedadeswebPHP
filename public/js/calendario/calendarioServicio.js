let fechaActual = new Date();

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

$("#selectColab").on("select2:select", function (e) {
    let data = e.params.data;
    console.log("Seleccionado:", data);
    $("#inputLegajo").val(data.legajo);
    $("#inputServicio").val(data.servicio);
});

$(document).ready(function () {
    $("#selectColab").select2({
        dropdownParent: $("#modalNuevaTarea"),
        placeholder: "Buscar colaborador",
        minimumInputLength: 2,
        width: "100%",
        language: {
            inputTooShort: function () {
                return "Ingrese al menos 2 caracteres";
            },
            searching: function () {
                return "Buscando...";
            },
            noResults: function () {
                return "No se encontraron colaboradores";
            },
            loadingMore: function () {
                return "Cargando más resultados...";
            },
        },
        ajax: {
            url: "/calendario/colaboradores-area",
            dataType: "json",
            delay: 300,
            data: function (params) {
                let idArea = $("#idArea").val();
                return {
                    q: params.term,
                    idArea: idArea,
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
            cache: true,
        },
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
                    // Formateamos la fecha actual del bucle para que coincida con el data-fecha
                    let fechaString = `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`;

                    // Buscamos el div del día correspondiente
                    const contenedorDia = $(
                        `.calendar-day[data-fecha="${fechaString}"] .calendar-events`,
                    );

                    if (contenedorDia.length) {
                        const htmlEvento = `
                            <div class="evento-item badge-turno-${evento.turno_sigla}">
                                <small>${evento.colaborador.split(" ")[0]}</small> 
                                <strong>${evento.turno_sigla}${evento.caja_sigla ? "+" + evento.caja_sigla : ""}</strong>
                            </div>
                        `;
                        contenedorDia.append(htmlEvento);
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
        $("#calendarGrid").append(`<div></div>`);
    }

    for (let dia = 1; dia <= diasMes; dia++) {
        const mesFormateado = month + 1;
        const diaFecha = `${year}-${mesFormateado}-${dia}`;
        $("#calendarGrid").append(`
            <div class="calendar-day" data-fecha="${diaFecha}">
                <div class="calendar-day-header">${dia}</div>
                <div class="calendar-events"></div>
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

        html2canvas(calendario, {
            scale: 2, // mejora resolución
            useCORS: true,
            backgroundColor: "#ffffff",
        }).then((canvas) => {
            const link = document.createElement("a");
            link.download = "calendario.png";
            link.href = canvas.toDataURL("image/png");
            link.click();
        });
    });
