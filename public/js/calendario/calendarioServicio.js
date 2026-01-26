let fechaActual = new Date();

$('#btnCrearEvento').on('click',function(){
    $('#modalNuevaTarea').modal('show');
})

$('#btnCerrarModalEvento').on('click',function(){
    $('#formNuevoEvento')[0].reset();
    $('#modalNuevaTarea').modal('hide');
})

$('#btnGuardarEvento').on('click',function(){
    Swal.fire({
        title: "Excelente",
        text: "Evento registrado correctamente",
        icon: "success"
    });
    $('#formNuevoEvento')[0].reset();
    $('#modalNuevaTarea').modal('hide');
})

function generarCalendario(fecha) {
    const year = fecha.getFullYear();
    const month = fecha.getMonth();
    const primerDia = new Date(year, month, 1);
    const ultimoDia = new Date(year, month + 1, 0);
    const diasMes = ultimoDia.getDate();
    const diaSemanaInicio = primerDia.getDay();

    $('#calendarGrid').html('');

    $('#tituloMes').text(
        fecha.toLocaleString('es-AR', { month: 'long', year: 'numeric' })
    );

    for (let i = 0; i < diaSemanaInicio; i++) {
        $('#calendarGrid').append(`<div></div>`);
    }

    for (let dia = 1; dia <= diasMes; dia++) {
        $('#calendarGrid').append(`
            <div class="calendar-day" data-fecha="${year}-${month + 1}-${dia}">
                <div class="calendar-day-header">${dia}</div>
                <div class="calendar-events"></div>
            </div>
        `);
    }

    cargarEventosMes(year, month + 1);
}

$('#btnPrevMes').click(() => {
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    generarCalendario(fechaActual);
});

$('#btnNextMes').click(() => {
    fechaActual.setMonth(fechaActual.getMonth() + 1);
    generarCalendario(fechaActual);
});

generarCalendario(fechaActual);

