const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
(async()=>{
 const base=process.env.BIBLIOTECA_TEST_URL||'http://127.0.0.1:8013';
 const root=path.resolve('storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN);
 const out=process.env.BIBLIOTECA_QA_OUTPUT||path.join(root,'qa-unified-editor');
 fs.mkdirSync(out,{recursive:true});
 const fixture=JSON.parse(fs.readFileSync(path.join(root,'unified-editor.json'),'utf8'));
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 await context.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
 const page=await context.newPage(),errors=[];
 page.on('pageerror',error=>errors.push(error.message));
 page.on('dialog',dialog=>dialog.accept());
 await page.route('**/*',route=>{
   const url=new URL(route.request().url());
   if(url.origin===base&&!url.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(url.pathname))return route.abort();
   return route.continue();
 });
 const group=key=>page.locator('[data-group="'+key+'"]');
 const editor=key=>group(key).locator('[data-section-rich]');
 const additional=()=>page.locator('#bib-additional [data-section-rich]');
 const loaded=async()=>{
   await editor('tasks').waitFor();
   assert.equal(await editor('tasks').count(),1);
   assert.equal(await editor('specific').count(),1);
   assert.equal(await editor('contribution').count(),1);
 };
 async function save(){
   const pending=page.waitForResponse(r=>r.request().method()==='POST'&&r.url().endsWith('/accion'));
   await page.locator('#bib-save').click();
   const response=await pending;
   assert(response.ok(),await response.text());
   await page.waitForFunction(()=>!window.BibliotecaView.isPending());
   return {data:await response.json(),sent:response.request().postDataJSON().content};
 }
 function configFromHtml(html){
   const found=html.match(/<script[^>]+id="bib-editor-config"[^>]*>([\s\S]*?)<\/script>/);
   assert(found,'Missing editor config');
   return JSON.parse(found[1]);
 }
 try {
   await page.goto(base+'/biblioteca/versiones/'+fixture.draft+'/editar',{waitUntil:'networkidle'});
   await loaded();
   assert.equal(await editor('tasks').locator('li').count(),3);
   assert.equal(await editor('specific').locator('li').count(),2);
   assert.equal(await editor('internal').locator('ul > li > ul > li').count(),1);
   assert.equal(await editor('contribution').locator(':scope > div, :scope > p').count(),2);
   assert.equal(await editor('tasks').locator('strong,b').count(),1);
   assert.equal(await additional().locator('table').count(),1);
   assert.equal(await additional().locator('em,i').count(),1);
   assert.equal(await page.locator('#bib-sector').evaluate(el=>el.tagName),'SELECT');
   assert.equal(await page.locator('#bib-sector').inputValue(),'Servicio original sin equivalencia');
   const options=await page.locator('#bib-sector option').allTextContents();
   assert.equal(options.filter(s=>s==='Administración QA').length,1);
   assert(options.includes('Enfermería QA'));
   assert(!options.includes('Área inactiva QA'));

   // Opening, previewing and saving without editing must preserve the complete payload.
   await page.locator('#bib-preview').click();
   assert.equal(await page.locator('#bib-preview-content strong,b').filter({hasText:'antecedentes'}).count(),1);
   assert((await page.locator('#bib-preview-content').textContent()).includes('Conciliaciones y archivo'));
   await page.locator('#bib-preview-close').click();
   const unchanged=await save();
   assert.deepEqual(unchanged.sent,fixture.content);
   await page.reload();await loaded();

   await page.locator('#bib-sector').selectOption('Enfermería QA');
   // Keyboard editing adds a new bullet within the same section editor.
   await editor('tasks').locator('li').last().click();
   await editor('tasks').press('End');
   await editor('tasks').press('Enter');
   await page.keyboard.insertText('Informar novedades del cierre.');
   assert.equal(await editor('tasks').locator('li').count(),4);
   await editor('contribution').locator(':scope > div, :scope > p').last().click();
   await editor('contribution').press('End');
   await editor('contribution').press('Enter');
   await page.keyboard.insertText('Nueva contribución institucional.');
   assert((await editor('contribution').innerText()).includes('Nueva contribución institucional.'));

   // Formatting controls target the section selection.
   await editor('specific').locator('li').last().click();
   await editor('specific').press('End');
   await group('specific').getByRole('button',{name:'Agregar emoji',exact:true}).click();
   await page.getByRole('button',{name:'Acuerdo',exact:true}).click();
   assert((await editor('specific').innerText()).includes('🤝'));

   const nested=editor('internal').locator('ul > li > ul > li');
   await nested.click();await nested.press('End');await page.keyboard.insertText(' Revisado.');

   // A real source table remains editable and its other rich cells remain intact.
   const firstCell=additional().locator('table tr').nth(1).locator('td').first();
   await firstCell.click();await firstCell.press('End');await page.keyboard.insertText(' revisada');
   await page.locator('#bib-preview').click();
   const preview=await page.locator('#bib-preview-content').innerText();
   for(const text of ['Informar novedades del cierre.','Nueva contribución institucional.','Administración revisada','Registro de prueba'])assert(preview.includes(text),text);
   assert.equal(await page.locator('#bib-preview-content em,i').filter({hasText:'Registro de prueba'}).count(),1);
   await page.locator('#bib-preview-close').click();

   await group('tasks').scrollIntoViewIfNeeded();
   await page.screenshot({path:path.join(out,'tareas-unificadas-desktop.png')});
   const saved=await save();
   assert.equal(saved.data.version,fixture.draft);
   assert.equal(saved.sent.sector,'Enfermería QA');
   assert.deepEqual(saved.sent.groups.internal.map(b=>b.list.level),[0,1,0]);
   assert.equal(saved.sent.groups.internal[0].text,'Coordinar con Administración.');
   assert.equal(saved.sent.groups.internal[1].text,'Consultar novedades con el equipo. Revisado.');
   const idA=saved.sent.groups.tasks.find(b=>b.text==='Informar novedades del cierre.').id;
   // Insert a new DOM sibling before a just-saved item without remounting the editor.
   // Position-based identities previously reused A's ID for B on this next save.
   await editor('tasks').evaluate(el=>{
     const a=Array.from(el.querySelectorAll('li')).find(li=>li.textContent==='Informar novedades del cierre.');
     const b=document.createElement('li');b.textContent='Controlar documentación antes del cierre.';
     a.before(b);el.dispatchEvent(new InputEvent('input',{bubbles:true,inputType:'insertParagraph'}));
   });
   const inserted=await save();
   const a=inserted.sent.groups.tasks.find(b=>b.text==='Informar novedades del cierre.');
   const b=inserted.sent.groups.tasks.find(b=>b.text==='Controlar documentación antes del cierre.');
   assert.equal(a.id,idA);
   assert.notEqual(b.id,idA);
   assert.equal(new Set(inserted.sent.groups.tasks.map(b=>b.id)).size,inserted.sent.groups.tasks.length);
   await page.reload();await loaded();
   assert.equal(await page.locator('#bib-sector').inputValue(),'Enfermería QA');
   assert.equal(await editor('tasks').locator('li').count(),5);
   assert.equal(await editor('internal').locator('ul > li > ul > li').count(),1);
   assert((await editor('internal').locator('ul > li > ul > li').innerText()).includes('Revisado.'));
   assert.equal(await editor('tasks').locator('strong,b').count(),1);
   assert((await editor('contribution').innerText()).includes('Nueva contribución institucional.'));
   assert((await editor('specific').innerText()).includes('🤝'));
   assert((await additional().innerText()).includes('Administración revisada'));
   assert.equal(await additional().locator('em,i').count(),1);

   const historical=await context.request.get(base+'/biblioteca/versiones/'+fixture.published+'/editar');
   assert(historical.ok());
   assert.deepEqual(configFromHtml(await historical.text()).content,fixture.publishedContent);
   // Showing the document after save must preserve the reading view and source table.
   const show=await context.request.get(base+'/biblioteca/documentos/'+fixture.document+'?version='+fixture.draft);
   assert(show.ok());
   const showHtml=await show.text();
   assert(showHtml.includes('Informar novedades del cierre.'));
   assert(showHtml.includes('Nueva contribución institucional.'));
   assert(showHtml.includes('Administración revisada'));

   await group('tasks').scrollIntoViewIfNeeded();
   await page.screenshot({path:path.join(out,'tareas-unificadas-desktop.png')});
   await page.setViewportSize({width:390,height:844});
   await group('contribution').scrollIntoViewIfNeeded();
   await page.screenshot({path:path.join(out,'contribucion-unificada-mobile.png')});
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
   assert.deepEqual(errors,[]);
   fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'ok',noOpExact:true,tasks:5,stableNewIds:true,nestedLevels:[0,1,0],specific:2,contributionParagraphs:3,tablePreserved:true,sector:'Enfermería QA',historicalUnchanged:true,errors},null,2));
   console.log('OK: editor por sección, no-op exacto, listas, párrafos, formato, emoji, tabla, catálogo, preview, recarga e histórico conservado.');
 } catch(error) {
   await page.screenshot({path:path.join(out,'failure.png'),fullPage:true}).catch(()=>{});
   fs.writeFileSync(path.join(out,'failure.txt'),error.stack+'\n'+await page.locator('body').innerText());
   throw error;
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
