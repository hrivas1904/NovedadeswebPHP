/* Persistent library shell: only #bib-view is replaced during navigation. */
(function ($) {
    'use strict';
    const shell = document.querySelector('.bib-app');
    const content = document.getElementById('bib-content');
    const view = document.getElementById('bib-view');
    const feedback = document.getElementById('bib-navigation-feedback');
    if (!shell || !content || !view || !window.BibliotecaView) return;
    const base = new URL(shell.dataset.bibBase).pathname.replace(/\/$/, '');
    const scroller = shell.closest('.content-area') || document.scrollingElement;
    let currentUrl = window.location.href;
    let currentIndex = history.state?.biblioteca?.index ?? 0;
    let pending, generation = 0, posting = false, ignorePop = false;

    function state(url, index, scroll = 0) {
        return {...history.state, biblioteca: {url, index, scroll}};
    }
    history.replaceState(state(currentUrl, currentIndex, scroller.scrollTop), '', currentUrl);

    function isView(url) {
        return url.origin === location.origin && (url.pathname === base || url.pathname.startsWith(base + '/'))
            && !/\/(?:exportar|archivo)(?:\/|$)/.test(url.pathname);
    }
    function message(text, retry) {
        feedback.replaceChildren(document.createTextNode(text));
        feedback.classList.toggle('bib-navigation-error', Boolean(retry));
        feedback.hidden = false;
        if (retry) {
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'btn btn-sm btn-outline-secondary';
            button.textContent = retry.label;
            button.addEventListener('click', retry.run, {once: true});
            feedback.append(button);
        }
    }
    function loading(value) {
        content.setAttribute('aria-busy', String(value));
        view.inert = value;
        shell.classList.toggle('bib-navigating', value);
    }
    function replaceUrl(url) {
        currentUrl = new URL(url, location.href).href;
        history.replaceState(state(currentUrl, currentIndex, scroller.scrollTop), '', currentUrl);
    }
    function recordScroll() {
        if (!pending && location.href === currentUrl) {
            history.replaceState(state(currentUrl, currentIndex, scroller.scrollTop), '', currentUrl);
        }
    }
    scroller.addEventListener('scroll', recordScroll, {passive: true});

    // Preserve the header and navigation nodes; update active state and changed permissions.
    function reconcileLinks(target, source) {
        const existing = new Map([...target.children].map(el => [el.getAttribute('href'), el]));
        const kept = new Set();
        [...source.children].forEach((fresh, index) => {
            const key = fresh.getAttribute('href');
            let node = existing.get(key);
            if (!node) node = fresh.cloneNode(true);
            else {
                [...node.attributes].forEach(attr => { if (!fresh.hasAttribute(attr.name)) node.removeAttribute(attr.name); });
                [...fresh.attributes].forEach(attr => node.setAttribute(attr.name, attr.value));
                if (node.innerHTML !== fresh.innerHTML) node.innerHTML = fresh.innerHTML;
            }
            if (node.classList.contains('active')) node.setAttribute('aria-current', 'page');
            else node.removeAttribute('aria-current');
            kept.add(node);
            if (target.children[index] !== node) target.insertBefore(node, target.children[index] || null);
        });
        [...target.children].forEach(node => { if (!kept.has(node)) node.remove(); });
    }
    function updateShell(fresh) {
        shell.className = fresh.className;
        const token = fresh.dataset.bibToken;
        if (token) {
            shell.dataset.bibToken = token;
            $('meta[name="csrf-token"]').attr('content', token);
        }
        const nextNav = fresh.querySelector('.bib-nav');
        if (nextNav) reconcileLinks(shell.querySelector('.bib-nav'), nextNav);
        const tools = shell.querySelector('.bib-header-tools'), nextTools = fresh.querySelector('.bib-header-tools');
        if (!nextTools) tools?.remove();
        else if (!tools) shell.querySelector('.bib-header').append(nextTools.cloneNode(true));
        else if (tools.innerHTML !== nextTools.innerHTML) tools.innerHTML = nextTools.innerHTML;
        const preview = shell.querySelector('#bib-preview-context'), nextPreview = fresh.querySelector('#bib-preview-context');
        if (preview && nextPreview && preview.innerHTML !== nextPreview.innerHTML) preview.innerHTML = nextPreview.innerHTML;
        const nav = shell.querySelector('.bib-nav'), active = nav.querySelector('.active');
        if (active && nav.scrollWidth > nav.clientWidth) {
            nav.scrollLeft += active.getBoundingClientRect().left - nav.getBoundingClientRect().left
                - (nav.clientWidth - active.offsetWidth) / 2;
        }
    }
    function restorePop(options) {
        if (!options.popState) return;
        const delta = currentIndex - options.popState.index;
        if (delta) { ignorePop = true; history.go(delta); }
    }
    function allowed(options) {
        if (posting) { message('Esperá a que termine la operación antes de cambiar de vista.'); restorePop(options); return false; }
        if (!window.BibliotecaView.canLeave()) { restorePop(options); return false; }
        return true;
    }
    async function visit(href, options = {}) {
        const url = new URL(href, currentUrl);
        if (!isView(url)) { window.location.assign(url.href); return false; }
        if (!allowed(options)) return false;
        const method = (options.method || 'GET').toUpperCase();
        if (!options.popState) recordScroll();
        generation++;
        const sequence = generation;
        pending?.abort();
        const request = new AbortController();
        pending = request; posting = method !== 'GET';
        loading(true);
        message(posting ? 'Procesando…' : 'Cargando vista…');
        try {
            const response = await fetch(url.href, {
                method, body: options.body, signal: request.signal,
                credentials: 'same-origin', cache: 'no-store', referrer: currentUrl,
                headers: {'Accept': 'text/html', 'X-Biblioteca-Navigation': '1',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''}
            });
            if (sequence !== generation) return false;
            if (!response.ok) {
                const messages = {403: 'No tenés permiso para abrir esta vista.', 404: 'La vista solicitada ya no está disponible.',
                    419: 'La sesión venció. Recargá la página para continuar.', 413: 'El archivo supera el tamaño permitido.'};
                throw new Error(messages[response.status] || (posting
                    ? 'No se pudo confirmar la operación. Revisá el estado antes de volver a enviarla.'
                    : 'No se pudo cargar la vista. Tu contenido actual se conserva.'));
            }
            const html = await response.text();
            if (sequence !== generation) return false;
            const page = new DOMParser().parseFromString(html, 'text/html');
            const fresh = page.querySelector('.bib-app'), freshView = fresh?.querySelector('#bib-view');
            if (!freshView) {
                if (response.redirected) { window.location.assign(response.url); return false; }
                throw new Error('No se pudo cargar la vista. Tu contenido actual se conserva.');
            }
            // Fragment scripts are inert. Only the editor's JSON configuration is retained.
            freshView.querySelectorAll('script:not([type="application/json"])').forEach(script => script.remove());
            window.BibliotecaView.dispose();
            updateShell(fresh);
            $(view).empty().append([...freshView.childNodes]);
            if (page.title) document.title = page.title;
            const destination = new URL(response.url);
            destination.hash = url.hash;
            if (options.popState) {
                currentIndex = options.popState.index;
                currentUrl = destination.href;
                history.replaceState(state(currentUrl, currentIndex, options.popState.scroll), '', currentUrl);
            } else if (options.replace || destination.href === currentUrl) {
                replaceUrl(destination.href);
            } else {
                currentIndex++;
                currentUrl = destination.href;
                history.pushState(state(currentUrl, currentIndex), '', currentUrl);
            }
            window.BibliotecaView.mount();
            loading(false);
            feedback.hidden = true;
            const heading = view.querySelector('h2, h3') || view;
            heading.setAttribute('tabindex', '-1');
            heading.focus({preventScroll: true});
            scroller.scrollTop = options.popState?.scroll ?? 0;
            if (destination.hash) {
                let id;
                try { id = decodeURIComponent(destination.hash.slice(1)); } catch (_) { id = destination.hash.slice(1); }
                const anchor = document.getElementById(id);
                if (anchor && view.contains(anchor)) anchor.scrollIntoView({block: 'start'});
            }
            shell.dispatchEvent(new CustomEvent('biblioteca:loaded', {detail: {url: currentUrl}}));
            return true;
        } catch (error) {
            if (sequence !== generation || error.name === 'AbortError') return false;
            restorePop(options);
            const retryUrl = posting ? currentUrl : url.href;
            const retryHistory = !posting && options.popState;
            message(error.message || 'No se pudo cargar la vista.', {
                label: posting ? 'Revisar vista' : 'Reintentar',
                // Never repeat a POST automatically after an uncertain response.
                run: () => {
                    if (retryHistory && retryHistory.index !== currentIndex) history.go(retryHistory.index - currentIndex);
                    else visit(retryUrl, {replace: Boolean(retryHistory)});
                }
            });
            return false;
        } finally {
            if (sequence === generation) { pending = null; posting = false; loading(false); }
        }
    }
    $(shell).on('click.bibliotecaNavigation', 'a[href]', function (event) {
        if (event.isDefaultPrevented() || event.button > 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
            || this.hasAttribute('download') || this.hasAttribute('data-bib-native') || (this.target && this.target !== '_self')
            || this.closest('[contenteditable="true"]')) return;
        const url = new URL(this.href, location.href);
        if (!isView(url) || (url.pathname === location.pathname && url.search === location.search && url.hash)) return;
        event.preventDefault();
        visit(url.href);
    });
    $(shell).on('submit.bibliotecaNavigation', 'form', function (event) {
        if (event.isDefaultPrevented() || this.hasAttribute('data-bib-native') || (this.target && this.target !== '_self')) return;
        const url = new URL(this.action || currentUrl, currentUrl);
        if (!isView(url)) return;
        event.preventDefault();
        if (!this.reportValidity()) return;
        const data = new FormData(this), submitter = event.originalEvent?.submitter;
        if (submitter?.name) data.append(submitter.name, submitter.value);
        const method = (this.method || 'GET').toUpperCase();
        if (method === 'GET') {
            url.search = new URLSearchParams(data).toString();
            visit(url.href);
        } else visit(url.href, {method, body: data});
    });
    window.addEventListener('popstate', event => {
        if (ignorePop) { ignorePop = false; return; }
        const target = event.state?.biblioteca;
        if (!target || !isView(new URL(location.href))) { window.location.reload(); return; }
        visit(location.href, {popState: target});
    });
    window.addEventListener('beforeunload', event => {
        if (posting) { event.preventDefault(); event.returnValue = ''; }
    });
    window.BibliotecaNavigation = {visit, replaceUrl, updateNavigation(html) {
        const template=document.createElement('template');template.innerHTML=html;
        const next=template.content.querySelector('.bib-nav');
        if(next)reconcileLinks(shell.querySelector('.bib-nav'),next);
    }};
    window.BibliotecaView.mount();
    shell.querySelectorAll('.bib-nav a.active').forEach(link => link.setAttribute('aria-current', 'page'));
})(jQuery);
