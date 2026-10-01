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
        if (area) {
            const original = area.value;
            area.addEventListener('change', () => {
                document.querySelector('[data-edd-area-cambiada]').hidden = area.value === original;
            });
        }
    }
    const cargar = document.querySelector('[data-edd-cargar-base]');
    if (cargar && texto) {
        let anterior = '';
        let modoAnterior = '';
        const deshacer = document.querySelector('[data-edd-deshacer-base]');
        cargar.addEventListener('click', () => {
            const codigo = document.querySelector('#edd-catalogo').value;
            const plantilla = [...document.querySelectorAll('[data-edd-catalogo]')].find((item) => item.dataset.eddCatalogo === codigo);
            if (!plantilla) return;
            anterior = texto.value;
            modoAnterior = modo?.value || '';
            if (modo) {
                modo.value = 'personal';
                modo.dispatchEvent(new Event('change', { bubbles: true }));
            }
            texto.value = plantilla.content.querySelector('textarea').value;
            deshacer.hidden = false;
            texto.dispatchEvent(new Event('input', { bubbles: true }));
            texto.focus();
        });
        deshacer.addEventListener('click', () => {
            texto.value = anterior;
            if (modo) {
                modo.value = modoAnterior;
                modo.dispatchEvent(new Event('change', { bubbles: true }));
            }
            deshacer.hidden = true;
            texto.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
    document.querySelectorAll('[data-edd-seleccionar]').forEach((button) => {
        button.addEventListener('click', () => {
            const boxes = [...button.closest('form').querySelectorAll('input[type="checkbox"]')].filter((box) => !box.matches(':disabled'));
            const selected = boxes.some((box) => !box.checked);
            boxes.forEach((box) => { box.checked = selected; });
            button.closest('form').dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
})();
