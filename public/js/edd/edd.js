(() => {
    'use strict';

    const equipo = document.querySelector('[data-edd-equipo]');
    if (!equipo) return;

    const filas = Array.from(equipo.querySelectorAll('[data-edd-fila]'));
    if (filas.length === 0) return;

    const buscar = equipo.querySelector('[data-edd-buscar]');
    const estado = equipo.querySelector('[data-edd-estado]');
    const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();

    const filtrar = () => {
        const termino = normalizar(buscar.value);
        let visibles = 0;

        filas.forEach((fila) => {
            const coincide = normalizar(fila.dataset.busqueda).includes(termino)
                && (!estado.value || fila.dataset.estado === estado.value);
            fila.hidden = !coincide;
            if (coincide) visibles += 1;
        });

        equipo.querySelector('[data-edd-sin-resultados]').hidden = visibles > 0;
        equipo.querySelector('[data-edd-conteo]').textContent = `${visibles} de ${filas.length} evaluaciones`;
    };

    buscar.addEventListener('input', filtrar);
    estado.addEventListener('change', filtrar);
    equipo.querySelector('[data-edd-limpiar]').addEventListener('click', () => {
        buscar.value = '';
        estado.value = '';
        filtrar();
        buscar.focus();
    });

    filtrar();
})();
