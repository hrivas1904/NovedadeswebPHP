(() => {
    'use strict';
    const texto = document.querySelector('#edd-competencias');
    const modo = document.querySelector('#edd-modo-competencias');
    if (texto && modo) {
        const actualizar = () => {
            texto.disabled = modo.value !== 'personal';
            texto.required = modo.value === 'personal';
        };
        modo.addEventListener('change', actualizar);
        actualizar();
        const area = document.querySelector('#edd-area-persona');
        const original = area.value;
        area.addEventListener('change', () => {
            document.querySelector('[data-edd-area-cambiada]').hidden = area.value === original;
        });
    }
    const cargar = document.querySelector('[data-edd-cargar-base]');
    if (cargar && texto) {
        let anterior = '';
        const deshacer = document.querySelector('[data-edd-deshacer-base]');
        cargar.addEventListener('click', () => {
            const codigo = document.querySelector('#edd-catalogo').value;
            const plantilla = [...document.querySelectorAll('[data-edd-catalogo]')].find((item) => item.dataset.eddCatalogo === codigo);
            if (!plantilla) return;
            anterior = texto.value;
            texto.value = plantilla.content.querySelector('textarea').value;
            deshacer.hidden = false;
            texto.dispatchEvent(new Event('input', { bubbles: true }));
            texto.focus();
        });
        deshacer.addEventListener('click', () => {
            texto.value = anterior;
            deshacer.hidden = true;
            texto.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
})();
