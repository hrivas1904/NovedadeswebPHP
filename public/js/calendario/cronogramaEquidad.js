const hoy = new Date();

const opciones = { month: 'long' };
const mesFormateada = hoy.toLocaleDateString('es-ES', opciones).toUpperCase();

document.getElementById('detalleMesFecha').textContent = mesFormateada;
