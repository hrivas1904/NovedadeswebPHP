let fechaActual = new Date();

$('#selectColab').on('select2:select', function (e) {
    let data = e.params.data;
    console.log("Seleccionado:", data);
    $('#inputLegajo').val(data.legajo);
    $('#inputServicio').val(data.servicio);

});

$(document).ready(function () {
    $("#selectColab").select2({
        dropdownParent: $("#modalNuevaTarea"),
        placeholder: "Buscar colaborador",
        minimumInputLength: 1,
        width: "100%",
        language: {
            inputTooShort: function () {
                return "Ingrese al menos 1 carácter";
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
                    idArea: idArea
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
});

$("#btnCrearEvento").on("click", function () {
    $("#modalNuevaTarea").modal("show");
});

$("#btnCerrarModalEvento").on("click", function () {
    $("#formNuevoEvento")[0].reset();
    $("#modalNuevaTarea").modal("hide");
});

$("#btnGuardarEvento").on("click", function () {
    Swal.fire({
        title: "Excelente",
        text: "Evento registrado correctamente",
        icon: "success",
    });
    $("#formNuevoEvento")[0].reset();
    $("#modalNuevaTarea").modal("hide");
});

function generarCalendario(fecha) {
    const year = fecha.getFullYear();
    const month = fecha.getMonth();
    const primerDia = new Date(year, month, 1);
    const ultimoDia = new Date(year, month + 1, 0);
    const diasMes = ultimoDia.getDate();
    const diaSemanaInicio = primerDia.getDay();

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

    for (let i = 0; i < diaSemanaInicio; i++) {
        $("#calendarGrid").append(`<div></div>`);
    }

    for (let dia = 1; dia <= diasMes; dia++) {
        $("#calendarGrid").append(`
            <div class="calendar-day" data-fecha="${year}-${month + 1}-${dia}">
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

document.getElementById("btnExportarImagen").addEventListener("click", function () {

    const calendario = document.querySelector(".calendar-grid");

    html2canvas(calendario, {
        scale: 2,          // mejora resolución
        useCORS: true,
        backgroundColor: "#ffffff"
    }).then(canvas => {

        const link = document.createElement("a");
        link.download = "calendario.png";
        link.href = canvas.toDataURL("image/png");
        link.click();

    });

});
