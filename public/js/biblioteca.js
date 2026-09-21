/* Biblioteca institucional: Blade forms enhanced with the application's jQuery. */
(function ($) {
    'use strict';
    const app = $('.bib-app'); if (!app.length) return;
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
    app.on('click','[data-bib-print]',()=>window.print());
    app.on('click','[data-bib-action]',async function () {
        if (busy) return; const bar=$(this).closest('[data-version]'), action=$(this).data('bib-action');
        let reason='';
        if(action==='publish'&&!window.confirm('¿Publicar esta versión? La vigente anterior quedará en el historial.'))return;
        if(action==='retire'){reason=window.prompt('Motivo del retiro. La versión se conservará en el historial.');if(!reason?.trim())return;}
        busy=true;bar.find('button').prop('disabled',true);
        try {const result=await request(bar.data('url'),{action,revision:Number(bar.data('revision')),reason,confirmed:action==='publish'});window.location.href=action==='draft'?result.editUrl:result.url;}
        catch(xhr){error(errorMessage(xhr));busy=false;bar.find('button').prop('disabled',false);}
    });
    $('#bib-review').on('submit',async function(e){
        e.preventDefault();if(busy)return;
        const action=e.originalEvent.submitter?.value || 'save';
        if(action!=='save'&&!window.confirm(action==='publish'?'¿Validar y publicar este descriptivo como vigente?':'¿Confirmar que verificaste nombre, área y contenido?'))return;
        const data=Object.fromEntries(new FormData(this));data.action=action;data.revision=Number(data.revision);data.documentRevision=Number(data.documentRevision);data.confirmed=action!=='save';
        busy=true;$(this).find('button').prop('disabled',true);
        try{const result=await request($(this).data('url'),data);window.location.href=result.url;}
        catch(xhr){error(errorMessage(xhr));busy=false;$(this).find('button').prop('disabled',false);}
    });
    const coverageForm = $('#coverage-filters');
    if (coverageForm.length) {
        const results = $('#coverage-results'), feedback = $('#coverage-feedback');
        let timer, pending, generation = 0;
        coverageForm.find('[data-coverage-search]').hide();
        function cancelSearch() {
            clearTimeout(timer);
            generation++;
            if (pending) { pending.abort(); pending = null; }
        }
        function loadCoverage(url) {
            cancelSearch();
            const current = generation;
            results.attr('aria-busy', 'true');
            feedback.text('Buscando…');
            pending = $.ajax({url, dataType: 'json', headers: {Accept: 'application/json'}})
                .done(function (data) {
                    if (current !== generation) return;
                    results.html(data.html);
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
        }
        function searchUrl() { return coverageForm.attr('action') + '?' + coverageForm.serialize(); }
        coverageForm.on('input', '#coverage-q', function () {
            cancelSearch();
            results.attr('aria-busy', 'true');
            feedback.text('Buscando…');
            timer = setTimeout(() => loadCoverage(searchUrl()), 250);
        });
        coverageForm.on('change', '#coverage-status', () => loadCoverage(searchUrl()));
        coverageForm.on('submit', function (event) { event.preventDefault(); loadCoverage(searchUrl()); });
        results.on('click', '.bib-pagination a', function (event) {
            event.preventDefault(); loadCoverage(this.href);
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
    const configElement=document.getElementById('bib-editor-config');if(!configElement)return;
    const config=JSON.parse(configElement.textContent), job=config.kind==='descriptivos';let content=clone(config.content);
    function markDirty(){dirty=true;$('#bib-save-status').text('Cambios sin guardar');}
    app.on('input change','#bib-editor input,#bib-editor textarea,#bib-editor select,#bib-editor [contenteditable]',markDirty);
    window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
    $('[data-bib-cancel]').on('click',function(e){if(dirty&&!window.confirm('¿Salir sin guardar los cambios?'))e.preventDefault();else dirty=false;});
    $('#bib-kind').on('change',function(){if(dirty&&!window.confirm('¿Cambiar de colección sin guardar?')){this.value=config.kind;return;}dirty=false;window.location.href=$(this).data('new-url')+'?kind='+encodeURIComponent(this.value);});
    function smallButton(text,action,label){return $('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text,'aria-label':label||text}).attr('data-edit-action',action);}
    function blockElement(original){
        const b=clone(original), root=$('<div>',{class:'bib-editor-block'}).data('block',b), tools=$('<div>',{class:'bib-block-tools'}).appendTo(root);
        if(b.kind==='paragraph'){
            const select=$('<select>',{class:'form-select form-select-sm','aria-label':'Formato del bloque'}).attr('data-block-format','1');
            [['paragraph','Párrafo'],['bullet','Viñeta'],['heading','Subtítulo']].forEach(([value,label])=>select.append($('<option>',{value,text:label})));
            select.val(b.heading?'heading':b.list?'bullet':'paragraph').appendTo(tools);
        }else $('<strong>').text(b.kind==='table'?'Tabla conservada':'Contenido de imagen').appendTo(tools);
        tools.append(smallButton('↑','up','Subir bloque'),smallButton('↓','down','Bajar bloque'),smallButton('Quitar','remove','Quitar bloque'));
        if(b.kind==='table'){
            const table=$('<table>',{class:'table bib-editor-table'}),body=$('<tbody>').appendTo(table);
            (b.rows||[]).forEach((row,ri)=>{const tr=$('<tr>').appendTo(body);row.cells.forEach((cell,ci)=>$('<td>').attr('colspan',cell.colspan).append($('<textarea>',{class:'form-control','aria-label':`Fila ${ri+1}, columna ${ci+1}`}).val(cell.text).attr({'data-cell-row':ri,'data-cell-column':ci})).appendTo(tr));});
            $('<div>',{class:'bib-table-wrap'}).append(table).appendTo(root);
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
            if(key==='generic'){const ul=$('<ul>');content.groups.generic.forEach(b=>ul.append($('<li>').text(b.text)));body.append(ul,$('<small>',{class:'text-muted',text:'Competencias institucionales comunes a todos los puestos.'}));}
            else body.append(groupEditor(content.groups[key]||[]),groupControls());groups.append(section);
        });
        const additional=$('#bib-additional').empty();content.additional.forEach(s=>additional.append(additionalElement(s)));
    }
    function additionalElement(s){
        const section=$('<div>',{class:'bib-additional-section'}).data('section',clone(s));
        const heading=$('<div>',{class:'bib-block-tools'}).append($('<input>',{class:'form-control','aria-label':'Título de sección adicional'}).attr('data-section-title','1').val(s.title),smallButton('Quitar sección','remove-section'));
        return section.append(heading,groupEditor(s.blocks),groupControls());
    }
    function collectBlocks(list){return list.children('.bib-editor-block').map(function(){
        const root=$(this),b=clone(root.data('block'));
        if(b.kind==='table'){
            root.find('textarea[data-cell-row]').each(function(){const cell=b.rows[Number($(this).attr('data-cell-row'))].cells[Number($(this).attr('data-cell-column'))];if(cell.text!==this.value){cell.text=this.value;cell.blocks=[paragraph(this.value)];}});
            b.text=b.rows.map(r=>r.cells.map(c=>c.text).join(' | ')).join('\n');
        }else{b.text=root.find('[data-block-text]').val();if(b.kind==='paragraph'){const format=root.find('[data-block-format]').val();delete b.list;delete b.heading;if(format==='heading')b.heading=true;if(format==='bullet')b.list={id:'editor',level:0,format:'bullet',start:1};}}
        return b;
    }).get();}
    function manualElement(s){
        const section=$('<section>',{class:'bib-section bib-manual-section'}).data('section',clone(s));
        const heading=$('<div>',{class:'bib-section-title-edit'}).append($('<input>',{class:'form-control','aria-label':'Título de sección'}).attr('data-manual-title','1').val(s.title),smallButton('↑','section-up','Subir sección'),smallButton('↓','section-down','Bajar sección'),smallButton('Quitar','remove-manual','Quitar sección'));
        const body=$('<div>',{class:'bib-reading'}),toolbar=$('<div>',{class:'bib-rich-tools'});
        [['bold','Negrita'],['italic','Cursiva'],['insertUnorderedList','Viñetas'],['insertOrderedList','Numeración'],['table','Agregar tabla']].forEach(([cmd,label])=>toolbar.append($('<button>',{type:'button',class:'btn btn-sm btn-outline-secondary',text:label}).attr('data-rich-command',cmd)));
        const editor=$('<div>',{class:'bib-rich-editor',contenteditable:'true',role:'textbox','aria-multiline':'true','aria-label':'Contenido de la sección'}).html(s.html);
        body.append(toolbar,editor);return section.append(heading,body);
    }
    function renderManual(){const root=$('#bib-manual-sections').empty();content.sections.forEach(s=>root.append(manualElement(s)));}
    function collect(){
        const c=clone(content);$('#bib-editor [data-field]').each(function(){const key=$(this).data('field');c[key]=this.value;if(['validFrom','lastReview','approvalRecord'].includes(key)&&!this.value)c[key]=null;if(key==='reviewMonths')c[key]=Number(this.value);});
        if(job){$('#bib-job-groups [data-group]').each(function(){const key=$(this).attr('data-group');if(key!=='generic')c.groups[key]=collectBlocks($(this).find('.bib-blocks').first());});c.additional=$('#bib-additional>.bib-additional-section').map(function(){const root=$(this),s=clone(root.data('section'));s.title=root.find('[data-section-title]').val();s.blocks=collectBlocks(root.children('.bib-blocks'));return s;}).get();}
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
            if(action==='add-table')b={id:uuid(),kind:'table',text:'',rows:[{cells:[{text:'',colspan:1,blocks:[paragraph()]},{text:'',colspan:1,blocks:[paragraph()]}]},{cells:[{text:'',colspan:1,blocks:[paragraph()]},{text:'',colspan:1,blocks:[paragraph()]}]}]};list.append(blockElement(b));list.children().last().find('textarea').first().trigger('focus');
        }
        if(action==='remove-section')button.closest('.bib-additional-section').remove();
        const section=button.closest('.bib-manual-section');
        if(action==='remove-manual')section.remove();if(action==='section-up')section.prev().before(section);if(action==='section-down')section.next().after(section);
        markDirty();
    });
    $('#bib-add-section').on('click',()=>{$('#bib-additional').append(additionalElement({key:'additional-'+uuid(),title:'Nueva sección',blocks:[]}));markDirty();});
    $('#bib-add-manual-section').on('click',()=>{$('#bib-manual-sections').append(manualElement({id:uuid(),title:'Nueva sección',html:'',text:''}));markDirty();});
    app.on('mousedown','[data-rich-command]',e=>e.preventDefault());
    app.on('click','[data-rich-command]',function(){const editor=$(this).closest('.bib-reading').find('.bib-rich-editor')[0];if(!editor.contains(window.getSelection()?.anchorNode))editor.focus();const cmd=$(this).attr('data-rich-command');if(cmd==='table')document.execCommand('insertHTML',false,'<table><tbody><tr><th>Columna 1</th><th>Columna 2</th></tr><tr><td>Contenido</td><td>Contenido</td></tr></tbody></table><p><br></p>');else document.execCommand(cmd,false,null);markDirty();});
    app.on('paste','.bib-rich-editor',function(e){e.preventDefault();document.execCommand('insertText',false,e.originalEvent.clipboardData.getData('text/plain'));markDirty();});
    function renderBlocks(blocks,bullets=false){const root=$('<div>');blocks.forEach(b=>{if(b.kind==='table'){const table=$('<table>',{class:'table table-bordered'});(b.rows||[]).forEach(r=>{const tr=$('<tr>');r.cells.forEach(c=>tr.append($('<td>').attr('colspan',c.colspan).text(c.text)));table.append(tr);});root.append(table);}else if(b.heading)root.append($('<h4>').text(b.text));else if(b.list||bullets)root.append($('<ul>').append($('<li>').text(b.text)));else root.append($('<p>').text(b.text));});return root;}
    $('#bib-preview').on('click',()=>{const c=collect(),root=$('#bib-preview-content').empty().append($('<h2>').text(job?c.name:c.title));if(job){root.append($('<p>').text(c.area+' · '+c.sector));Object.entries(config.groups).forEach(([key,label])=>{if(c.groups[key].length)root.append($('<h3>').text(label),renderBlocks(c.groups[key],['generic','specific','commitment'].includes(key)));});c.additional.forEach(s=>root.append($('<h3>').text(s.title),renderBlocks(s.blocks)));}else c.sections.forEach(s=>root.append($('<h3>').text(s.title),$('<div>',{class:'bib-reading'}).html(s.html)));document.getElementById('bib-preview-dialog').showModal();});
    $('#bib-preview-close').on('click',()=>document.getElementById('bib-preview-dialog').close());
    async function save(publish=false){
        if(busy)return;if(publish&&!window.confirm('¿Guardar y publicar esta versión? La vigente anterior se conservará en el historial.'))return;
        const c=collect();busy=true;$('#bib-editor button').prop('disabled',true);alertBox.addClass('d-none');
        try{
            let data=config.upload?{destination:$('#bib-destination').val(),document:$('#bib-destination-document').val()||null,content:$('#bib-destination').val()==='support'?null:c}:(config.version?{action:'save',revision:config.revision,content:c}:{kind:config.kind,requestId:config.requestId,content:c});
            const result=await request(config.url,data);dirty=false;
            if(config.upload){window.location.href=result.url;return;}
            config.version=result.version;config.revision=result.revision;config.url=config.actionTemplate.replace('VERSION_PLACEHOLDER',result.version);
            if(!job)c.id=result.documentId;content=c;$('#bib-save-status').text('Borrador guardado');
            window.history.replaceState({},'',result.editUrl);
            if(publish){try{const published=await request(config.url,{action:'publish',revision:config.revision,confirmed:true,reason:''});window.location.href=published.url;return;}catch(xhr){error(errorMessage(xhr));}}
        }catch(xhr){error(errorMessage(xhr));}
        finally{busy=false;$('#bib-editor button').prop('disabled',false);}
    }
    $('#bib-editor').on('submit',e=>{e.preventDefault();save(false);});$('#bib-save-publish').on('click',()=>save(true));
    if(job)renderJob();else renderManual();
})(jQuery);
