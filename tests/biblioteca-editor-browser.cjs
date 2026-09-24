
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const assert=require('assert'),fs=require('fs'),path=require('path');
(async()=>{
 const base='http://127.0.0.1:8002',root=path.resolve('storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN),out=path.join(root,'qa-editor');
 fs.mkdirSync(out,{recursive:true});
 const fixtures=JSON.parse(fs.readFileSync(path.join(root,'versions.json'),'utf8'));
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 await context.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
 const page=await context.newPage(),errors=[];let accept=true,posts=0,documents=0;
 page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>accept?d.accept():d.dismiss());
 page.on('request',r=>{if(r.method()==='POST')posts++;if(r.isNavigationRequest()&&r.frame()===page.mainFrame())documents++;});
 await page.route('**/*',r=>{const u=new URL(r.request().url());return u.origin===base&&!u.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(u.pathname)?r.abort():r.continue();});
 async function changed(action){const n=await page.evaluate(()=>window.viewLoads);await action();await page.waitForFunction(n=>window.viewLoads>n,n);}
 async function saved(action){const response=page.waitForResponse(r=>r.request().method()==='POST'&&/\/(accion|documentos)$/.test(new URL(r.url()).pathname));await action();const r=await response;assert(r.ok(),await r.text());await page.waitForFunction(()=>!window.BibliotecaView.isPending());return r.json();}
 const block=()=>page.locator('[data-group=generic] .bib-editor-block').nth(1),field=()=>block().locator('[data-block-rich]');
 try{
  await page.goto(base+'/biblioteca/versiones/'+fixtures.descriptivos.id+'/editar',{waitUntil:'networkidle'});
  await page.evaluate(()=>{window.viewLoads=0;window.header=document.querySelector('.bib-header');document.querySelector('.bib-app').addEventListener('biblioteca:loaded',()=>window.viewLoads++);});
  assert.equal(await page.locator('#bib-delete-draft').isVisible(),false);
  const first=await page.locator('[data-group=generic] [data-block-rich]').first().innerHTML();
  await field().fill('Competencia con formato');
  await field().press('Control+a');await block().getByRole('button',{name:'Negrita',exact:true}).click();
  assert((await field().innerHTML()).match(/<(b|strong)>/));assert.equal(await page.locator('[data-group=generic] [data-block-rich]').first().innerHTML(),first);
  await field().press('ArrowRight');await block().getByRole('button',{name:'Agregar emoji',exact:true}).click();
  await page.getByRole('button',{name:'Acuerdo',exact:true}).click();assert((await field().innerText()).includes('🤝'));
  await block().scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'editor-desktop.png')});
  await page.locator('#bib-preview').click();assert((await page.locator('#bib-preview-content').innerHTML()).includes('<b>Competencia con formato'));
  await page.locator('#bib-preview-close').click();
  const draft=await saved(()=>page.locator('#bib-save').click());assert(await page.locator('#bib-delete-draft').isVisible());
  await field().fill('Cambios sin guardar');
  await changed(()=>page.locator('#bib-discard').click());
  assert((await field().innerHTML()).includes('<b>Competencia con formato'));assert(await page.locator('#bib-delete-draft').isVisible());
  await page.setViewportSize({width:390,height:844});await block().scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'editor-mobile.png')});
  assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));await page.setViewportSize({width:1440,height:1000});
  accept=false;const before=posts;await page.locator('#bib-delete-draft').click();assert.equal(posts,before);accept=true;
  await changed(()=>page.locator('#bib-delete-draft').click());
  assert(page.url().includes(fixtures.descriptivos.id));assert.equal((await context.request.get(base+'/biblioteca/versiones/'+draft.version+'/editar')).status(),404);
  assert.equal(documents,1);assert(await page.evaluate(()=>window.header===document.querySelector('.bib-header')));
  console.log('Formato en segundo bloque, emoji, preview, guardado, descarte y eliminación: OK.');
  await changed(()=>page.evaluate(url=>window.BibliotecaNavigation.visit(url),base+'/biblioteca/administracion/nuevo?kind=descriptivos'));
  await page.locator('#bib-name').fill('Documento cancelado QA');await page.locator('#bib-area').fill('Calidad');
  const created=await saved(()=>page.locator('#bib-save').click());await changed(()=>page.locator('#bib-delete-draft').click());
  assert(page.url().endsWith('/biblioteca/administracion'));assert.equal((await context.request.get(base+'/biblioteca/documentos/'+created.documentId)).status(),404);
  await changed(()=>page.evaluate(url=>window.BibliotecaNavigation.visit(url),base+'/biblioteca/administracion/visibilidad'));
  assert.equal(await page.locator('#visibility-results tbody tr').count(),25);
  await page.locator('#visibility-per-page').selectOption('50');
  await page.waitForFunction(()=>document.querySelectorAll('#visibility-results tbody tr').length===50);
  await page.locator('#visibility-kind').selectOption('procedimientos');
  await page.waitForFunction(()=>document.querySelector('#visibility-feedback').textContent==='5 documentos');
  const row=page.locator('#visibility-results tr').filter({has:page.locator('input[name=key][value="document:'+fixtures.procedimientos.document_id+'"]')});
  await row.locator('[data-visibility-toggle]').uncheck();await page.waitForFunction(()=>!window.BibliotecaView.isPending());
  const reader=await browser.newContext();await reader.addCookies([{name:'bib_test_persona',value:'2',url:base}]);
  assert.equal((await reader.request.get(base+'/biblioteca/documentos/'+fixtures.procedimientos.document_id)).status(),404);
  await row.locator('[data-visibility-toggle]').check();await page.waitForFunction(()=>!window.BibliotecaView.isPending());
  assert.equal((await reader.request.get(base+'/biblioteca/documentos/'+fixtures.procedimientos.document_id)).status(),200);
  await page.locator('#visibility-kind').selectOption('descriptivos');await page.waitForFunction(()=>document.querySelector('#visibility-feedback').textContent==='41 documentos');
  const jobRow=page.locator('#visibility-results tr').filter({has:page.locator('input[name=key][value="document:'+fixtures.descriptivos.document_id+'"]')});
  await jobRow.locator('[data-visibility-toggle]').uncheck();await page.waitForFunction(()=>!window.BibliotecaView.isPending());
  assert.equal((await reader.request.get(base+'/biblioteca/documentos/'+fixtures.descriptivos.document_id)).status(),404);
  assert((await (await reader.request.get(base+'/biblioteca/mi-descriptivo')).text()).includes('temporalmente oculto'));
  await jobRow.locator('[data-visibility-toggle]').check();await page.waitForFunction(()=>!window.BibliotecaView.isPending());
  await page.locator('#visibility-results').scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'visibilidad.png')});
  assert.equal(documents,1);assert.deepEqual(errors,[]);await reader.close();
  console.log('Borrador nuevo, visibilidad individual, filtros y navegación dinámica: OK.');
 }catch(e){await page.screenshot({path:path.join(out,'failure.png')}).catch(()=>{});throw e;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
