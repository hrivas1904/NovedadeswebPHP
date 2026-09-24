/* Biblioteca institucional: Blade forms enhanced with the application's jQuery. */
(function ($) {
    'use strict';
    if (!document.getElementById('bib-view')) return;
    let current;
    function createView() {
    const app = $('#bib-view');
    const cleanups = [], pendingReads = new Set();
    let assignmentSaving = false, visibilitySaving = false;
    function onDispose(callback) { cleanups.push(callback); }
    function read(options) {
        const xhr = $.ajax(options);
        pendingReads.add(xhr);
        xhr.always(() => pendingReads.delete(xhr));
        return xhr;
    }
    const controller = {
        isPending: () => dirty || busy || assignmentSaving || visibilitySaving,
        canLeave: () => {
            if (busy || assignmentSaving || visibilitySaving) { error('Esperá a que termine el guardado antes de cambiar de vista.'); return false; }
            return !dirty || window.confirm('¿Salir sin guardar los cambios?');
        },
        dispose: () => {
            cleanups.forEach(callback => callback());
            pendingReads.forEach(xhr => xhr.abort());
            app.off();
        }
    };
    const clone = value => JSON.parse(JSON.stringify(value));
    const uuid = () => crypto.randomUUID();
    const paragraph = (text = '') => ({id: uuid(), kind: 'paragraph', text});
    let dirty = false, busy = false;
    const alertBox = $('.bib-alert');
    function error(message) { alertBox.removeClass('d-none alert-success').addClass('alert-danger').text(message).trigger('focus'); alertBox[0].scrollIntoView({block: 'center', behavior: 'smooth'}); }
    function request(url, data) {
        return $.ajax({url, method: 'POST', contentType: 'application/json', dataType: 'json', headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), Accept: 'application/json'}, data: JSON.stringify(data)});
    }
    function errorMessage(xhr) { const data=xhr.responseJSON; return data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'No se pudo completar la operación. Verificá la conexión e intentá nuevamente.'); }
    function visibilityState(form,state,message='Guardado') {
        form.attr('data-confirmed',state.visible?'1':'0').removeAttr('data-uncertain');
        form.find('[name=revision]').val(state.revision);
        form.find('[data-visibility-toggle]').prop('checked',state.visible);
        form.find('[data-visibility-label]').text(state.visible?'Visible':'Oculto');
        form.find('[data-visibility-status]').removeClass('is-error').text(message);
        form.find('[data-visibility-retry]').prop('hidden',true);
        if(state.navigation)window.BibliotecaNavigation.updateNavigation(state.navigation);
    }
    function visibilityBusy(value) {
        visibilitySaving=value;
        app.find('[data-visibility-toggle],[data-visibility-retry]').prop('disabled',value);
        app.find('[data-visibility-control][data-uncertain] [data-visibility-toggle]').prop('disabled',true);
        app.find('[data-live-filter] :input').prop('disabled',value);
    }
    async function verifyVisibility(form) {
        const state=await read({url:form.attr('data-state-url'),dataType:'json',cache:false,headers:{Accept:'application/json'}});
        visibilityState(form,state);return state;
    }
    function unknownVisibility(form) {
        form.attr('data-uncertain','1');
        form.find('[data-visibility-status]').addClass('is-error').text('No se pudo verificar el estado. Revisá la conexión y verificá antes de cambiarlo.');
        form.find('[data-visibility-retry]').prop('hidden',false);
    }
    app.on('submit','[data-visibility-control]',e=>e.preventDefault());
    app.on('change','[data-visibility-toggle]',async function() {
        const form=$(this).closest('[data-visibility-control]'),previous=form.attr('data-confirmed')==='1',desired=this.checked;
        if(visibilitySaving){this.checked=previous;return;}
        app.trigger('biblioteca:visibility-saving');visibilityBusy(true);form.attr('aria-busy','true');
        form.find('[data-visibility-status]').removeClass('is-error').text('Guardando…');
        try {
            const state=await request(form.attr('action'),{key:form.find('[name=key]').val(),visible:desired,revision:Number(form.find('[name=revision]').val())});
            visibilityState(form,state);
        } catch(xhr) {
            form.find('[data-visibility-toggle]').prop('checked',previous);
            try {
                const state=await verifyVisibility(form);
                if(xhr.status===409||state.visible!==desired)form.find('[data-visibility-status]').addClass('is-error').text(xhr.status===409?'Otra persona cambió esta opción. Se muestra el estado actual; podés volver a cambiarlo.':'No se guardó el cambio. Podés intentarlo nuevamente.');
            } catch {unknownVisibility(form);}
        } finally {form.attr('aria-busy','false');visibilityBusy(false);}
    });
    app.on('click','[data-visibility-retry]',async function() {
        if(visibilitySaving)return;
        const form=$(this).closest('[data-visibility-control]');
        app.trigger('biblioteca:visibility-saving');visibilityBusy(true);form.attr('aria-busy','true');
        form.find('[data-visibility-status]').text('Verificando…');
        try {await verifyVisibility(form);}catch {unknownVisibility(form);}
        finally {form.attr('aria-busy','false');visibilityBusy(false);}
    });
    app.on('click','[data-bib-print]',()=>window.print());
    app.on('click','[data-bib-action]',async function () {
        if (busy) return; const bar=$(this).closest('[data-version]'), action=$(this).data('bib-action');
        let reason='';
        if(action==='publish'&&!window.confirm('¿Publicar esta versión? La vigente anterior quedará en el historial.'))return;
        if(action==='retire'){reason=window.prompt('Motivo del retiro. La versión se conservará en el historial.');if(!reason?.trim())return;}
        busy=true;bar.find('button').prop('disabled',true);
        try {const result=await request(bar.data('url'),{action,revision:Number(bar.data('revision')),reason,confirmed:action==='publish'});busy=false;window.BibliotecaNavigation.visit(action==='draft'?result.editUrl:result.url);}
        catch(xhr){error(errorMessage(xhr));busy=false;bar.find('button').prop('disabled',false);}
    });
    $('#bib-review').on('submit',async function(e){
        e.preventDefault();if(busy)return;
        const action=e.originalEvent.submitter?.value || 'save';
        if(action!=='save'&&!window.confirm(action==='publish'?'¿Validar y publicar este descriptivo como vigente?':'¿Confirmar que verificaste nombre, área y contenido?'))return;
        const data=Object.fromEntries(new FormData(this));data.action=action;data.revision=Number(data.revision);data.documentRevision=Number(data.documentRevision);data.confirmed=action!=='save';
        busy=true;$(this).find('button').prop('disabled',true);
        try{const result=await request($(this).data('url'),data);busy=false;window.BibliotecaNavigation.visit(result.url);}
        catch(xhr){error(errorMessage(xhr));busy=false;$(this).find('button').prop('disabled',false);}
    });
    function syncListing(form,url) {
        const query=new URL(url,window.location.href).searchParams;
        ['sort','direction'].forEach(key=>{if(query.has(key))form.find('[name="'+key+'"]').val(query.get(key));});
        if (form.is('[data-home-search]')) {
            const homeUrl = new URL(document.querySelector('.bib-app').dataset.bibBase);
            homeUrl.search = new URL(url, window.location.href).search;
            homeUrl.searchParams.set('home_search', '1');
            window.BibliotecaNavigation.replaceUrl(homeUrl.href);
        } else if(new URL(url,window.location.href).pathname===window.location.pathname)window.BibliotecaNavigation.replaceUrl(url);
    }
    $('[data-live-filter]').each(function () {
        const form = $(this), results = $(form.attr('data-live-filter'));
        const feedback = $(form.attr('data-live-feedback')), button = form.find('[data-live-submit]');
        const home = form.is('[data-home-search]'), query = form.find('[name=q]');
        const originalButton = button.html();
        let timer, pending, generation = 0;
        if (!home) button.hide();
        function cancel() {
            clearTimeout(timer); generation++;
            if (pending) { pending.abort(); pending = null; }
        }
        onDispose(cancel);
        app.on('biblioteca:visibility-saving',()=>{cancel();results.removeAttr('inert').attr('aria-busy','false');});
        function homeResults(show) {
            if (!home) return;
            form.find('[data-home-results]').prop('hidden', !show);
            results.prop('hidden', !show);
            app.find('[data-home-browse]').prop('hidden', show);
            form.find('.bib-home-clear').prop('hidden', !show && !query.val().trim());
        }
        function resetHome() {
            cancel();
            query.val('');
            form.find('[name=kind][value=""]').prop('checked', true);
            results.empty().attr('aria-busy', 'false');
            feedback.empty();
            button.html(originalButton).show();
            homeResults(false);
            window.BibliotecaNavigation.replaceUrl(document.querySelector('.bib-app').dataset.bibBase);
        }
        function load(url) {
            if(visibilitySaving)return;
            cancel();
            const current = generation;
            homeResults(true);
            feedback.text('Buscando…'); results.attr('aria-busy', 'true');if(results.is('#visibility-results'))results.attr('inert','');
            pending = read({url, dataType: 'json', headers: {Accept: 'application/json'}})
                .done(function (data) {
                    if (generation !== current) return;
                    results.html(data.html);
                    if (home) button.html(originalButton).show(); else button.hide();
                    syncListing(form,url);
                    feedback.text(results.find('.bib-result-count').text());
                    if (home && !results.find('.bib-document-link').length) {
                        results.html($('#index-empty').html());
                    }
                })
                .fail(function (xhr, status) {
                    if (generation !== current || status === 'abort') return;
                    feedback.text(results.find('.bib-document-link').length
                        ? 'No se pudieron actualizar los resultados. Se muestran los anteriores. Intentá nuevamente.'
                        : 'No se pudo completar la búsqueda. Intentá nuevamente.');
                    button.show().text('Reintentar');
                })
                .always(function () {
                    if (generation === current) { pending = null; results.removeAttr('inert').attr('aria-busy', 'false'); }
                });
        }
        function url() { return form.attr('action') + '?' + form.serialize(); }
        form.on('input', 'input:not([type=hidden]):not([type=checkbox]):not([type=radio])', function () {
            cancel();
            if (home && !query.val().trim() && !form.find('[name=kind]:checked').val()) {
                resetHome();
                return;
            }
            homeResults(true);
            feedback.text('Buscando…'); results.attr('aria-busy', 'true');
            timer = setTimeout(() => load(url()), 300);
        });
        form.on('change', 'select, input[type=checkbox], input[type=radio]', () => load(url()));
        form.on('submit', function (event) { event.preventDefault(); load(url()); });
        results.on('click', '.bib-pagination a, [data-bib-sort]', function (event) { event.preventDefault(); load(this.href); });
        if (home) {
            const restored = new URL(window.location.href).searchParams;
            if (restored.get('home_search') === '1') {
                ['q', 'per_page', 'sort', 'direction'].forEach(key => {
                    if (restored.has(key)) form.find('[name="' + key + '"]').val(restored.get(key));
                });
                form.find('[name=kind]').each(function () { this.checked = this.value === (restored.get('kind') || ''); });
                load(url());
            }
            app.on('click', '[data-home-clear]', function () { resetHome(); query.trigger('focus'); });
            app.on('click', '[data-home-all]', function (event) {
                event.preventDefault();
                resetHome();
                load(url());
                query.trigger('focus');
            });
            query.on('keydown', function (event) {
                if (event.key === 'Escape') { event.preventDefault(); resetHome(); }
            });
        }
    });
    const coverageForm = $('#coverage-filters');
    if (coverageForm.length) {
        const results = $('#coverage-results'), feedback = $('#coverage-feedback');
        let timer, pending, generation = 0, currentUrl = window.location.href;
        coverageForm.find('[data-coverage-search]').hide();
        results.find('.bib-assignment-form select').prop('disabled', false);
        function cancelSearch() {
            clearTimeout(timer);
            generation++;
            if (pending) { pending.abort(); pending = null; }
        }
        onDispose(cancelSearch);
        function loadCoverage(url) {
            cancelSearch();
            const current = generation;
            results.attr('aria-busy', 'true');
            results.find('.bib-assignment-form select').prop('disabled', true);
            feedback.text('Buscando…');
            pending = read({url, dataType: 'json', headers: {Accept: 'application/json'}})
                .done(function (data) {
                    if (current !== generation) return;
                    currentUrl = url;syncListing(coverageForm,url);
                    results.html(data.html);
                    results.find('.bib-assignment-form select').prop('disabled', false);
                    Object.entries(data.counts).forEach(([key, value]) => {
                        app.find('[data-coverage-count="' + key + '"]').text(value);
                    });
                    $('#coverage-account-warning').toggleClass('d-none', !data.counts.withoutAccount && !data.counts.ambiguousAccounts);
                    const state = $('#coverage-status'), selected = state.val();
                    state.empty().append($('<option>', {value: '', text: 'Todas'}));
                    const statuses = [...new Set([...data.statuses, ...(selected ? [selected] : [])])];
                    statuses.forEach(text => state.append($('<option>', {value: text, text})));
                    state.val(selected);
                    coverageForm.find('[data-coverage-search]').hide();
                    feedback.text(results.find('.bib-result-count').text());
                })
                .fail(function (xhr, status) {
                    if (current !== generation || status === 'abort') return;
                    feedback.text('No se pudieron actualizar los resultados. Se muestran los anteriores. Intentá nuevamente.');
                    coverageForm.find('[data-coverage-search]').show().text('Reintentar');
                })
                .always(function () {
                    if (current === generation) { results.attr('aria-busy', 'false'); pending = null; }
                });
            return pending;
        }
        function searchUrl() { return coverageForm.attr('action') + '?' + coverageForm.serialize(); }
        coverageForm.on('input', '#coverage-q', function () {
            if (assignmentSaving) return;
            cancelSearch();
            results.attr('aria-busy', 'true');
            results.find('.bib-assignment-form select').prop('disabled', true);
            feedback.text('Buscando…');
            timer = setTimeout(() => loadCoverage(searchUrl()), 250);
        });
        coverageForm.on('change', 'select', function () { if (!assignmentSaving) loadCoverage(searchUrl()); });
        coverageForm.on('submit', function (event) { event.preventDefault(); if (!assignmentSaving) loadCoverage(searchUrl()); });
        results.on('click', '.bib-pagination a, [data-bib-sort]', function (event) {
            event.preventDefault(); if (!assignmentSaving) loadCoverage(this.href);
        });
        results.on('submit', '.bib-assignment-form', event => event.preventDefault());
        results.on('change', '.bib-assignment-form select[name="document_id"]', async function () {
            if (assignmentSaving) return;
            const form = $(this).closest('form'), data = form.serialize(), legajo = form.attr('data-assignment-legajo');
            cancelSearch();
            assignmentSaving = true;
            coverageForm.find(':input').prop('disabled', true);
            results.find('.bib-assignment-form select').prop('disabled', true);
            results.attr('aria-busy', 'true');
            form.find('[data-assignment-feedback]').text('Guardando…');
            feedback.text('Guardando asignación del legajo ' + legajo + '…');
            let message, saved = false;
            try {
                await $.ajax({url: form.attr('action'), method: 'POST', data, dataType: 'json', headers: {Accept: 'application/json'}});
                saved = true;
                message = 'Asignación guardada para el legajo ' + legajo + '.';
            } catch (xhr) {
                message = xhr.status === 409 ? 'La asignación cambió en otra sesión. Revisá el valor actual antes de volver a elegir.'
                    : 'No se pudo confirmar el guardado. ' + errorMessage(xhr);
            }
            try {
                // Reload authoritative values and revision even after an uncertain network response.
                await loadCoverage(currentUrl);
                feedback.text(message);
                const updated = results.find('[data-assignment-legajo="' + legajo + '"]');
                updated.find('[data-assignment-feedback]').text(saved ? 'Guardado.' : 'Revisá la asignación actual.');
                updated.find('select')[0]?.focus({preventScroll: true});
            } catch (_) {
                feedback.text(message + ' No se pudo actualizar la tabla. Usá Reintentar para consultar lo guardado.');
                coverageForm.find('[data-coverage-search]').show().text('Reintentar');
            } finally {
                assignmentSaving = false;
                coverageForm.find(':input').prop('disabled', false);
                results.attr('aria-busy', 'false');
            }
        });
    }
    const previewSelect = $('#preview-user');
    if (previewSelect.length) {
        const options = previewSelect.find('option').clone();
        const normalize = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        $('#preview-search').on('input', function () {
            const q = normalize(this.value), selected = previewSelect.val();
            previewSelect.empty().append(options.filter(function () { return normalize(this.textContent).includes(q); }).clone());
            if (selected) previewSelect.val(selected);
            $('#preview-empty').toggleClass('d-none', previewSelect.find('option').length > 0);
        });
    }
    const configElement=document.getElementById('bib-editor-config');if(!configElement)return controller;
    const config=JSON.parse(configElement.textContent), job=config.kind==='descriptivos';let content=clone(config.content);
    function markDirty(){dirty=true;$('#bib-save-status').text('Cambios sin guardar');}
    app.on('input change','#bib-editor input,#bib-editor textarea,#bib-editor select,#bib-editor [contenteditable]',markDirty);
    $('[data-bib-cancel]').on('click',function(e){if(dirty&&!window.confirm('¿Salir sin guardar los cambios?'))e.preventDefault();else dirty=false;});
    $('#bib-discard').on('click',function(){
        if(busy)return;
        const savedDraft=config.version&&!config.newVersion&&!config.upload;
        const message=savedDraft?'¿Descartar los cambios sin guardar y volver al último guardado?':'¿Descartar esta edición y salir sin guardar?';
        if(dirty&&!window.confirm(message))return;
        dirty=false;
        if(savedDraft)window.BibliotecaNavigation.visit(window.location.href,{replace:true});
        else window.BibliotecaNavigation.visit($('[data-bib-cancel]').first().attr('href'));
    });
    $('#bib-delete-draft').on('click',async function(){
        if(busy||!config.version||config.newVersion||config.upload)return;
        if(!window.confirm('¿Eliminar este borrador? Se perderán sus cambios guardados y sin guardar. La versión anterior se conservará. Si es un documento nuevo, se quitará de la biblioteca.'))return;
        busy=true;$('#bib-editor :input').prop('disabled',true);$('#bib-editor [contenteditable]').attr('contenteditable','false');
        try{
            const result=await request(config.url,{action:'delete_draft',revision:config.revision,confirmed:true});
            dirty=false;busy=false;window.BibliotecaNavigation.visit(result.url,{replace:true});
        }catch(xhr){error(errorMessage(xhr));}
        finally{busy=false;$('#bib-editor :input').prop('disabled',false);$('#bib-editor [contenteditable]').attr('contenteditable','true');}
    });
    $('#bib-kind').on('change',function(){if(dirty&&!window.confirm('¿Cambiar de colección sin guardar?')){this.value=config.kind;return;}dirty=false;window.BibliotecaNavigation.visit($(this).data('new-url')+'?kind='+encodeURIComponent(this.value));});
    function smallButton(text,action,label){return $('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text,'aria-label':label||text}).attr('data-edit-action',action);}
    function richTools(jobBlock=false){
        const toolbar=$('<div>',{class:'bib-rich-tools',role:'toolbar','aria-label':'Formato de la sección'});
        const style=$('<select>',{class:'form-select form-select-sm','aria-label':'Estilo del texto'}).attr('data-rich-style','1');
        [['p','Texto normal'],['h3','Título'],['h4','Subtítulo'],['blockquote','Cita destacada']].forEach(([value,label])=>style.append($('<option>',{value,text:label})));
        if(!jobBlock)toolbar.append(style);
        [
            [['bold','Negrita','B'],['italic','Cursiva','I'],['underline','Subrayado','U'],['strikeThrough','Tachado','S']],
            [['insertUnorderedList','Viñetas','• Lista'],['insertOrderedList','Numeración','1. Lista']],
            [['createLink','Insertar enlace','Enlace'],['unlink','Quitar enlace','Quitar enlace'],['table','Agregar tabla','Tabla']],
            [['undo','Deshacer','↶'],['redo','Rehacer','↷'],['removeFormat','Quitar formato','Limpiar formato']]
        ].forEach(commands=>{
            if(jobBlock)commands=commands.filter(([cmd])=>!['insertUnorderedList','insertOrderedList','table'].includes(cmd));
            if(!commands.length)return;
            const group=$('<div>',{class:'bib-rich-tool-group'});
            commands.forEach(([cmd,label,text])=>group.append($('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text,title:label,'aria-label':label}).attr('data-rich-command',cmd).attr('aria-pressed',['bold','italic','underline','strikeThrough'].includes(cmd)?'false':null)));
            toolbar.append(group);
        });
        toolbar.append($('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text:'😊 Emojis','aria-label':'Agregar emoji'}).attr('data-emoji-picker','1'));
        return toolbar;
    }
    function richEditorFor(control){return $(control).closest('.bib-editor-block,.bib-manual-section').find('.bib-rich-editor')[0];}
    function blockElement(original){
        const b=clone(original), root=$('<div>',{class:'bib-editor-block'}).data('block',b), tools=$('<div>',{class:'bib-block-tools'}).appendTo(root);
        if(b.kind==='paragraph'){
            const select=$('<select>',{class:'form-select form-select-sm','aria-label':'Formato del bloque'}).attr('data-block-format','1');
            [['paragraph','Párrafo'],['bullet','Viñeta'],['numbered','Numeración'],['heading','Subtítulo']].forEach(([value,label])=>select.append($('<option>',{value,text:label})));
            select.val(b.heading?'heading':b.list?(b.list.format==='decimal'?'numbered':'bullet'):'paragraph').appendTo(tools);
        }else $('<strong>').text(b.kind==='table'?'Tabla conservada':'Contenido de imagen').appendTo(tools);
        if(b.kind!=='paragraph')tools.append($('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text:'😊 Emojis','aria-label':'Agregar emoji'}).attr('data-emoji-picker','1'));
        tools.append(smallButton('↑','up','Subir bloque'),smallButton('↓','down','Bajar bloque'),smallButton('Quitar','remove','Quitar bloque'));
        if(b.kind==='table'){
            const table=$('<table>',{class:'table bib-editor-table'}),body=$('<tbody>').appendTo(table);
            (b.rows||[]).forEach((row,ri)=>{const tr=$('<tr>').appendTo(body);row.cells.forEach((cell,ci)=>$('<td>').attr('colspan',cell.colspan).append($('<textarea>',{class:'form-control','aria-label':`Fila ${ri+1}, columna ${ci+1}`}).val(cell.text).attr({'data-cell-row':ri,'data-cell-column':ci})).appendTo(tr));});
            $('<div>',{class:'bib-table-wrap'}).append(table).appendTo(root);
        }else if(b.kind==='paragraph'){
            root.attr('data-format',tools.find('[data-block-format]').val());
            const html=b.html??$('<div>').text(b.text).html().replace(/\n/g,'<br>');
            root.append(richTools(true),$('<div>',{class:'bib-rich-editor bib-job-rich-editor',contenteditable:'true',role:'textbox','aria-multiline':'true','aria-label':'Contenido del bloque'}).attr('data-block-rich','1').html(html));
        }else $('<textarea>',{class:'form-control','aria-label':'Contenido del bloque'}).attr('data-block-text','1').val(b.text).appendTo(root);
        return root;
    }
    function groupEditor(blocks){const list=$('<div>',{class:'bib-blocks'});blocks.forEach(b=>list.append(blockElement(b)));return list;}
    function groupControls(){return $('<div>',{class:'bib-actions'}).append(smallButton('+ Agregar texto','add-block'),smallButton('+ Agregar tabla','add-table'));}
    function renderJob(){
        const groups=$('#bib-job-groups').empty();
        Object.entries(config.groups).forEach(([key,label])=>{
            const section=$('<section>',{class:'bib-section'}).attr('data-group',key).append($('<h3>').text(label));
            const body=$('<div>',{class:'bib-reading'}).appendTo(section);
            body.append(groupEditor(content.groups[key]||[]),groupControls());
            if(key==='generic')body.append($('<small>',{class:'text-muted',text:'Propuesta inicial: podés editar, agregar o quitar competencias para este puesto.'}));
            groups.append(section);
        });
        const additional=$('#bib-additional').empty();content.additional.forEach(s=>additional.append(additionalElement(s)));
    }
    function additionalElement(s){
        const section=$('<div>',{class:'bib-additional-section'}).data('section',clone(s));
        const heading=$('<div>',{class:'bib-block-tools'}).append($('<input>',{class:'form-control','aria-label':'Título de sección adicional'}).attr('data-section-title','1').val(s.title),smallButton('Quitar sección','remove-section'));
        return section.append(heading,groupEditor(s.blocks),groupControls());
    }
    function collectBlocks(list){let nextNumber=1;return list.children('.bib-editor-block').map(function(){
        const root=$(this),b=clone(root.data('block'));
        if(b.kind==='table'){
            root.find('textarea[data-cell-row]').each(function(){const cell=b.rows[Number($(this).attr('data-cell-row'))].cells[Number($(this).attr('data-cell-column'))];if(cell.text!==this.value){cell.text=this.value;cell.blocks=[paragraph(this.value)];}});
            b.text=b.rows.map(r=>r.cells.map(c=>c.text).join(' | ')).join('\n');
        }else{if(b.kind==='paragraph'){const editor=root.find('[data-block-rich]')[0];b.html=editor.innerHTML;b.text=editor.innerText;const format=root.find('[data-block-format]').val();delete b.list;delete b.heading;if(format==='heading')b.heading=true;if(['bullet','numbered'].includes(format))b.list={id:'editor',level:0,format:format==='numbered'?'decimal':'bullet',start:1};}else b.text=root.find('[data-block-text]').val();}
        if(b.list?.format==='decimal')b.list.start=nextNumber++;else nextNumber=1;
        return b;
    }).get();}
    function manualElement(s){
        const section=$('<section>',{class:'bib-section bib-manual-section'}).data('section',clone(s));
        const heading=$('<div>',{class:'bib-section-title-edit'}).append($('<input>',{class:'form-control','aria-label':'Título de sección'}).attr('data-manual-title','1').val(s.title),smallButton('↑','section-up','Subir sección'),smallButton('↓','section-down','Bajar sección'),smallButton('Quitar','remove-manual','Quitar sección'));
        const body=$('<div>',{class:'bib-reading'}),toolbar=richTools();
        const editor=$('<div>',{class:'bib-rich-editor',contenteditable:'true',role:'textbox','aria-multiline':'true','aria-label':'Contenido de la sección'}).html(s.html);
        body.append(toolbar,editor);return section.append(heading,body);
    }
    function renderManual(){const root=$('#bib-manual-sections').empty();content.sections.forEach(s=>root.append(manualElement(s)));}
    function collect(){
        const c=clone(content);$('#bib-editor [data-field]').each(function(){const key=$(this).data('field');c[key]=this.value;if(['validFrom','lastReview','approvalRecord'].includes(key)&&!this.value)c[key]=null;if(key==='reviewMonths')c[key]=Number(this.value);});
        if(job){$('#bib-job-groups [data-group]').each(function(){const key=$(this).attr('data-group');c.groups[key]=collectBlocks($(this).find('.bib-blocks').first());});c.additional=$('#bib-additional>.bib-additional-section').map(function(){const root=$(this),s=clone(root.data('section'));s.title=root.find('[data-section-title]').val();s.blocks=collectBlocks(root.children('.bib-blocks'));return s;}).get();}
        else c.sections=$('.bib-manual-section').map(function(){const root=$(this),s=clone(root.data('section'));s.title=root.find('[data-manual-title]').val();s.html=root.find('.bib-rich-editor').html();s.text=root.find('.bib-rich-editor').text();return s;}).get();
        return c;
    }
    app.on('click','[data-edit-action]',function(){
        const button=$(this),action=button.attr('data-edit-action'),block=button.closest('.bib-editor-block');
        if(action==='up')block.prev('.bib-editor-block').before(block);
        if(action==='down')block.next('.bib-editor-block').after(block);
        if(action==='remove')block.remove();
        if(action==='add-block'||action==='add-table'){
            const list=button.parent().prev('.bib-blocks');let b=paragraph();
            if(action==='add-table')b={id:uuid(),kind:'table',text:'',rows:[{cells:[{text:'',colspan:1,blocks:[paragraph()]},{text:'',colspan:1,blocks:[paragraph()]}]},{cells:[{text:'',colspan:1,blocks:[paragraph()]},{text:'',colspan:1,blocks:[paragraph()]}]}]};list.append(blockElement(b));list.children().last().find('textarea,[contenteditable]').first().trigger('focus');
        }
        if(action==='remove-section')button.closest('.bib-additional-section').remove();
        const section=button.closest('.bib-manual-section');
        if(action==='remove-manual')section.remove();if(action==='section-up')section.prev().before(section);if(action==='section-down')section.next().after(section);
        markDirty();
    });
    $('#bib-add-section').on('click',()=>{$('#bib-additional').append(additionalElement({key:'additional-'+uuid(),title:'Nueva sección',blocks:[]}));markDirty();});
    $('#bib-add-manual-section').on('click',()=>{$('#bib-manual-sections').append(manualElement({id:uuid(),title:'Nueva sección',html:'',text:''}));markDirty();});
    app.on('change','[data-block-format]',function(){$(this).closest('.bib-editor-block').attr('data-format',this.value);});
    const selections=new WeakMap();
    function rememberSelection(editor) {
        const selection=window.getSelection();
        if(editor&&selection.rangeCount&&editor.contains(selection.anchorNode)&&editor.contains(selection.focusNode))selections.set(editor,selection.getRangeAt(0).cloneRange());
    }
    function restoreSelection(editor) {
        editor.focus();
        const selection=window.getSelection(),range=selections.get(editor);
        if(range&&editor.contains(range.commonAncestorContainer)){selection.removeAllRanges();selection.addRange(range);}
        else if(!editor.contains(selection.anchorNode)){const end=document.createRange();end.selectNodeContents(editor);end.collapse(false);selection.removeAllRanges();selection.addRange(end);}
    }
    function updateToolbar(editor) {
        $(editor).siblings('.bib-rich-tools').find('[aria-pressed]').each(function(){$(this).attr('aria-pressed',document.queryCommandState($(this).attr('data-rich-command'))?'true':'false');});
    }
    app.on('keyup mouseup input focusout','.bib-rich-editor',function(){rememberSelection(this);updateToolbar(this);});
    app.on('mousedown','[data-rich-command],[data-emoji-picker]',e=>e.preventDefault());
    app.on('click','[data-rich-command]',function(){
        const editor=richEditorFor(this),cmd=$(this).attr('data-rich-command');
        rememberSelection(editor);restoreSelection(editor);
        if(cmd==='table'){
            const answer=window.prompt('Tamaño de la tabla: filas x columnas (máximo 10 x 10).','3 x 2');
            if(answer===null)return;
            const match=answer.trim().match(/^([1-9]|10)\s*[x×]\s*([1-9]|10)$/i);
            if(!match){error('Indicá filas x columnas, por ejemplo 3 x 2, hasta 10 x 10.');return;}
            let html='<table><tbody>';
            for(let row=0;row<Number(match[1]);row++){html+='<tr>';for(let col=0;col<Number(match[2]);col++)html+=row===0?'<th>Columna '+(col+1)+'</th>':'<td>Contenido</td>';html+='</tr>';}
            document.execCommand('insertHTML',false,html+'</tbody></table><p><br></p>');
        }else if(cmd==='createLink'){
            const href=window.prompt('Dirección del enlace (https://… o mailto:…).','https://');
            if(href===null)return;
            if(!/^(https?:\/\/[^\s]+|mailto:[^\s@]+@[^\s@]+)$/i.test(href.trim())){error('Usá una dirección https://, http:// o mailto: válida.');return;}
            if(window.getSelection().isCollapsed){
                const anchor=document.createElement('a');anchor.href=href.trim();anchor.textContent=href.trim();document.execCommand('insertHTML',false,anchor.outerHTML);
            }else document.execCommand('createLink',false,href.trim());
        }else document.execCommand(cmd,false,null);
        rememberSelection(editor);updateToolbar(editor);markDirty();
    });
    app.on('change','[data-rich-style]',function(){
        const editor=richEditorFor(this);
        restoreSelection(editor);document.execCommand('formatBlock',false,this.value);rememberSelection(editor);markDirty();
    });
    app.on('paste','.bib-rich-editor',function(e){e.preventDefault();document.execCommand('insertText',false,e.originalEvent.clipboardData.getData('text/plain'));markDirty();});

    const emojiItems=[
        ['😊','Sonrisa'],['😀','Alegría'],['🙂','Amabilidad'],['🤝','Acuerdo'],['👏','Aplausos'],['👍','Aprobado'],
        ['❤️','Corazón'],['💙','Corazón azul'],['💚','Corazón verde'],['⭐','Estrella'],['✨','Destacado'],['🎯','Objetivo'],
        ['✅','Completado'],['☑️','Verificado'],['❌','No corresponde'],['⚠️','Atención'],['ℹ️','Información'],['📌','Importante'],
        ['📝','Nota'],['📋','Lista'],['📚','Biblioteca'],['📅','Calendario'],['⏰','Horario'],['🔎','Revisar'],
        ['🏥','Hospital'],['🩺','Atención médica'],['💊','Medicación'],['🧼','Higiene'],['🧤','Protección'],['♿','Accesibilidad'],
        ['👥','Equipo'],['💬','Comunicación'],['📞','Teléfono'],['📧','Correo'],['🔒','Confidencial'],['🔄','Actualizar']
    ];
    const emojiDialog=$('<dialog>',{class:'bib-dialog bib-emoji-dialog','aria-labelledby':'bib-emoji-title'});
    emojiDialog.append($('<div>',{class:'bib-page-heading'}).append($('<h2>',{id:'bib-emoji-title',text:'Agregar emoji'}),$('<button>',{type:'button',class:'btn btn-outline-secondary',text:'Cerrar','aria-label':'Cerrar emojis'}).on('click',()=>emojiDialog[0].close())));
    const emojiSearch=$('<input>',{class:'form-control',type:'search','aria-label':'Buscar emoji',placeholder:'Buscar: equipo, atención, nota…'}),emojiGrid=$('<div>',{class:'bib-emoji-grid'});
    emojiDialog.append(emojiSearch,emojiGrid);app.append(emojiDialog);
    let emojiTarget=null,lastTextField=null;
    app.on('focusin','#bib-editor textarea',function(){lastTextField=this;});
    function renderEmojis(){
        const norm=text=>text.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
        emojiGrid.empty();
        emojiItems.filter(([,label])=>norm(label).includes(norm(emojiSearch.val()))).forEach(([emoji,label])=>{
            $('<button>',{type:'button',text:emoji,title:label,'aria-label':label}).on('click',()=>{
                emojiDialog[0].close();
                if(emojiTarget.input){
                    const field=emojiTarget.input;field.focus();field.setSelectionRange(emojiTarget.start,emojiTarget.end);
                    field.setRangeText(emoji,emojiTarget.start,emojiTarget.end,'end');$(field).trigger('input');
                }else{restoreSelection(emojiTarget.editor);document.execCommand('insertText',false,emoji);rememberSelection(emojiTarget.editor);}
                markDirty();
            }).appendTo(emojiGrid);
        });
    }
    emojiSearch.on('input',renderEmojis);
    app.on('click','[data-emoji-picker]',function(){
        const block=$(this).closest('.bib-editor-block');
        if(block.length&&!block.find('.bib-rich-editor').length){
            const field=lastTextField&&block[0].contains(lastTextField)?lastTextField:block.find('textarea')[0];
            if(!field)return;emojiTarget={input:field,start:field.selectionStart,end:field.selectionEnd};
        }else{
            const editor=richEditorFor(this);
            rememberSelection(editor);emojiTarget={editor};
        }
        emojiSearch.val('');renderEmojis();emojiDialog[0].showModal();emojiSearch.trigger('focus');
    });
    function renderBlocks(blocks,bullets=false){
        const root=$('<div>');blocks.forEach(b=>{
            if(b.kind==='table'){
                const table=$('<table>',{class:'table table-bordered'});
                (b.rows||[]).forEach(r=>{const tr=$('<tr>');r.cells.forEach(c=>tr.append($('<td>').attr('colspan',c.colspan).append(c.blocks?.length?renderBlocks(c.blocks):$('<span>').text(c.text))));table.append(tr);});root.append(table);
            }else{
                const value=b.html!==undefined?$('<div>',{class:'bib-rich-block'}).html(b.html):$('<span>').css('white-space','pre-wrap').text(b.text);
                if(b.heading)root.append($('<div>',{class:'bib-rich-block-heading',role:'heading','aria-level':'4'}).append(value));
                else if(b.list||bullets)root.append($(b.list?.format==='decimal'?'<ol>':'<ul>').attr('start',b.list?.start||1).append($('<li>').append(value)));
                else root.append($('<div>',{class:'bib-rich-block'}).append(value));
            }
        });return root;
    }
    $('#bib-preview').on('click',()=>{const c=collect(),root=$('#bib-preview-content').empty().append($('<h2>').text(job?c.name:c.title));if(job){root.append($('<p>').text(c.area+' · '+c.sector));Object.entries(config.groups).forEach(([key,label])=>{if(c.groups[key].length)root.append($('<h3>').text(label),renderBlocks(c.groups[key],['generic','specific','commitment'].includes(key)));});c.additional.forEach(s=>root.append($('<h3>').text(s.title),renderBlocks(s.blocks)));}else c.sections.forEach(s=>root.append($('<h3>').text(s.title),$('<div>',{class:'bib-reading'}).html(s.html)));document.getElementById('bib-preview-dialog').showModal();});
    $('#bib-preview-close').on('click',()=>document.getElementById('bib-preview-dialog').close());
    async function save(publish=false){
        if(busy)return;if(publish&&!window.confirm('¿Guardar y publicar esta versión? La vigente anterior se conservará en el historial.'))return;
        const c=collect(),reason=$('#bib-change-reason').val()||'';busy=true;$('#bib-editor :input').prop('disabled',true);$('#bib-editor [contenteditable]').attr('contenteditable','false');alertBox.addClass('d-none');
        try{
            let data=config.upload?{destination:$('#bib-destination').val(),document:$('#bib-destination-document').val()||null,content:$('#bib-destination').val()==='support'?null:c}:(config.version?{action:config.newVersion?'new_version':'save',revision:config.revision,requestId:config.requestId,reason,content:c}:{kind:config.kind,requestId:config.requestId,content:c});
            const result=await request(config.url,data);dirty=false;
            if(config.upload){busy=false;window.BibliotecaNavigation.visit(result.url);return;}
            config.newVersion=false;config.version=result.version;config.revision=result.revision;config.url=config.actionTemplate.replace('VERSION_PLACEHOLDER',result.version);
            if(!job)c.id=result.documentId;content=c;$('#bib-save-status').text('Borrador guardado · Versión '+result.number);
            $('#bib-editor-title').text('Editar borrador');$('#bib-editor-version').text(' · Versión interna '+result.number);
            $('#bib-save').text('Guardar borrador');$('#bib-discard').text('Descartar cambios');$('#bib-delete-draft').removeClass('d-none');$('#bib-new-version-notice').text('Versión '+result.number+' guardada como borrador. La versión publicada se conserva hasta aprobar la nueva.');
            $('[data-bib-cancel]').attr('href',result.url);$('#bib-saved-link').removeClass('d-none');
            window.BibliotecaNavigation.replaceUrl(result.editUrl);
            if(publish){try{const published=await request(config.url,{action:'publish',revision:config.revision,confirmed:true,reason});busy=false;window.BibliotecaNavigation.visit(published.url);return;}catch(xhr){error(errorMessage(xhr));}}
        }catch(xhr){error(errorMessage(xhr));}
        finally{busy=false;$('#bib-editor :input').prop('disabled',false);$('#bib-editor [contenteditable]').attr('contenteditable','true');}
    }
    $('#bib-editor').on('submit',e=>{e.preventDefault();save(false);});$('#bib-save-publish').on('click',()=>save(true));
    if(job)renderJob();else renderManual();
    return controller;
    }
    window.BibliotecaView = {
        mount() { current?.dispose(); current = createView(); },
        dispose() { current?.dispose(); current = null; },
        canLeave() { return current?.canLeave() ?? true; },
        isPending() { return current?.isPending() ?? false; }
    };
    window.addEventListener('beforeunload', event => {
        if (window.BibliotecaView.isPending()) { event.preventDefault(); event.returnValue = ''; }
    });
})(jQuery);
