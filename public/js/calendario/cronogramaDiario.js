const hoy = new Date();

const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
const fechaFormateada = hoy.toLocaleDateString('es-ES', opciones).toUpperCase();

document.getElementById('detalleFechaLarga').textContent = fechaFormateada;
