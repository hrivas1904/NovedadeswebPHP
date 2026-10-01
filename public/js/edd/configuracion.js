(() => {
    'use strict';

    const forms = [...document.querySelectorAll('[data-edd-form]')];
    if (!forms.length) return;

    let busy = false;
    let leaving = false;
    const editor = document.querySelector('[data-edd-instrumento]');
    const publish = document.querySelector('[data-edd-publicar]');
    const formatter = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 2 });

    const message = (form, lines, type = 'danger') => {
        const box = form.querySelector('[data-edd-resultado]');
        box.className = `alert alert-${type} mt-3`;
        box.replaceChildren();
        const list = document.createElement('ul');
        list.className = 'mb-0';
        for (const line of lines) {
            const item = document.createElement('li');
            item.textContent = line;
            list.append(item);
        }
        box.append(list);
        box.focus();
    };

    const refreshButtons = () => {
        forms.forEach((form) => {
            form.querySelectorAll('[data-edd-guardar]').forEach((button) => {
                button.disabled = busy || form.dataset.blocked === '1' || (form === publish && editor?.dataset.dirty === '1');
            });
        });
        const notice = document.querySelector('[data-edd-publicacion-pendiente]');
        if (notice) notice.hidden = editor?.dataset.dirty !== '1';
    };

    const refreshWeights = () => {
        if (!editor) return;
        let total = 0;
        let missing = 0;
        editor.querySelectorAll('[data-edd-bloque]').forEach((block) => {
            if (!block.querySelector('[data-edd-activo]').checked) return;
            const raw = block.querySelector('[data-edd-peso]').value;
            if (!raw || !Number.isFinite(Number(raw))) {
                missing += 1;
                return;
            }
            total += Math.round(Number(raw) * 100);
        });
        editor.querySelector('[data-edd-total-pesos]').textContent = `Ponderación activa: ${formatter.format(total / 100)} % de 100 %${missing ? ` · ${missing} bloque(s) sin peso` : ''}`;
    };

    const markDirty = (form) => {
        if (form !== publish) form.dataset.dirty = '1';
        refreshWeights();
        refreshButtons();
    };

    editor?.querySelectorAll('[data-edd-bloque]').forEach((block) => {
        const items = block.querySelector('[data-edd-items]');
        let removed = null;
        const undo = block.querySelector('[data-edd-deshacer]');
        block.querySelector('[data-edd-agregar]').addEventListener('click', () => {
            if (items.children.length >= 100) {
                message(editor, ['Cada bloque admite hasta 100 criterios.']);
                return;
            }
            const next = Number(block.dataset.siguiente);
            block.dataset.siguiente = String(next + 1);
            const template = document.createElement('template');
            template.innerHTML = block.querySelector('[data-edd-item-template]').innerHTML.replaceAll('__INDICE__', String(next));
            const row = template.content.firstElementChild;
            items.append(row);
            row.querySelector('input:not([type="hidden"])').focus();
            markDirty(editor);
        });
        items.addEventListener('click', (event) => {
            const button = event.target.closest('[data-edd-quitar]');
            if (!button) return;
            const row = button.closest('[data-edd-item]');
            removed = { row, next: row.nextElementSibling };
            row.remove();
            undo.hidden = false;
            markDirty(editor);
        });
        undo.addEventListener('click', () => {
            if (!removed) return;
            items.insertBefore(removed.row, removed.next?.isConnected ? removed.next : null);
            removed = null;
            undo.hidden = true;
            markDirty(editor);
        });
    });

    forms.forEach((form) => {
        form.addEventListener('input', () => markDirty(form));
        form.addEventListener('change', () => markDirty(form));
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (busy || form.dataset.blocked === '1') return;
            if (form.matches('[data-edd-quitar-inscripcion]') && document.querySelector('[data-edd-inscripcion]')?.dataset.dirty === '1') {
                message(form, ['Guardá primero la inscripción que estás editando.']);
                return;
            }
            if (form === publish && editor?.dataset.dirty === '1') {
                message(form, ['Guardá los cambios del instrumento antes de publicar.']);
                return;
            }
            if (!form.reportValidity()) return;

            form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
                field.removeAttribute('aria-invalid');
                field.classList.remove('is-invalid');
            });
            const payload = new FormData(form);
            const controls = forms.flatMap((other) => [...other.querySelectorAll('input, textarea, select, button')])
                .map((element) => [element, element.disabled]);
            controls.forEach(([element]) => { element.disabled = true; });
            busy = true;
            refreshButtons();
            message(form, ['Guardando…'], 'info');
            let serverResponse = false;

            try {
                const response = await fetch(form.action, {
                    method: 'POST', body: payload, credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                serverResponse = true;
                if (response.redirected || !response.headers.get('Content-Type')?.includes('application/json')) {
                    throw new Error('No se pudo confirmar el guardado. Revisá tu sesión y la versión guardada. Tus cambios siguen en pantalla.');
                }
                const data = await response.json();
                if (response.status === 422) {
                    const lines = [];
                    for (const [key, errors] of Object.entries(data.errors || {})) {
                        const parts = key.split('.');
                        const name = parts[0] + parts.slice(1).map((part) => `[${part}]`).join('');
                        const field = [...form.elements].find((element) => element.name === name && element.type !== 'hidden');
                        if (field) {
                            field.setAttribute('aria-invalid', 'true');
                            field.classList.add('is-invalid');
                        }
                        const label = field?.labels?.[0]?.textContent.trim();
                        lines.push(...errors.map((error) => label ? `${label}: ${error}` : error));
                    }
                    message(form, lines.length ? lines : ['Revisá los datos del formulario.']);
                    return;
                }
                if (!response.ok) {
                    if ([401, 403, 409, 419].includes(response.status)) form.dataset.blocked = '1';
                    const detail = response.status === 409 ? data.message
                        : [401, 419].includes(response.status) ? 'La sesión venció. Volvé a ingresar en otra pestaña y revisá la versión guardada antes de continuar.'
                            : response.status === 403 ? 'Tu cuenta no tiene permiso para guardar esta configuración.'
                                : 'No se pudo confirmar el guardado. Tus cambios siguen en pantalla; revisá la versión guardada antes de reintentar.';
                    if (response.status >= 500) form.dataset.blocked = '1';
                    message(form, [detail]);
                    form.querySelector('[data-edd-recargar]').classList.remove('d-none');
                    return;
                }
                const destination = new URL(data.redirect, window.location.origin);
                if (!data.redirect || destination.origin !== window.location.origin) throw new Error('El guardado respondió sin un destino válido. Revisá la versión guardada.');
                leaving = true;
                forms.forEach((other) => { other.dataset.dirty = '0'; });
                window.location.assign(destination.href);
            } catch (error) {
                form.dataset.blocked = '1';
                message(form, [serverResponse ? error.message : 'La conexión se interrumpió y no se pudo confirmar el guardado. Tus cambios siguen en pantalla; revisá la versión guardada antes de reintentar.']);
                form.querySelector('[data-edd-recargar]').classList.remove('d-none');
            } finally {
                if (!leaving) {
                    busy = false;
                    controls.forEach(([element, wasDisabled]) => { element.disabled = wasDisabled; });
                    refreshButtons();
                }
            }
        });
    });

    window.addEventListener('beforeunload', (event) => {
        if (!leaving && (busy || forms.some((form) => form.dataset.dirty === '1'))) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    refreshWeights();
    refreshButtons();
})();
