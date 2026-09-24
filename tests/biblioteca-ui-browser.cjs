const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const fs=require('fs'),path=require('path'),assert=require('assert');
(async()=>{
 const base=process.env.BIBLIOTECA_TEST_URL||'http://127.0.0.1:8002';
 const root=path.join(__dirname,'../storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN);
 const fixtures=JSON.parse(fs.readFileSync(path.join(root,'versions.json'),'utf8'));
 const out=path.join(root,'qa-ui');fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 await context.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
 const page=await context.newPage(),errors=[];let confirm=true,posts=0;
 page.on('pageerror',e=>errors.push(e.message));
 page.on('request',r=>{if(r.method()==='POST')posts++;});
 page.on('dialog',d=>confirm?d.accept(d.type()==='prompt'?'3 x 2':undefined):d.dismiss());
 await page.route('**/*',route=>{const u=new URL(route.request().url());if(u.origin===base&&!u.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(u.pathname))return route.abort();return route.continue();});
 const go=url=>page.goto(base+url,{waitUntil:'domcontentloaded'});
 const selectText=async locator=>locator.evaluate(el=>{
   const range=document.createRange();range.selectNodeContents(el);
   const sel=window.getSelection();sel.removeAllRanges();sel.addRange(range);
   el.closest('[contenteditable]').dispatchEvent(new MouseEvent('mouseup',{bubbles:true}));
 });
 const idle=async selector=>page.waitForFunction(sel=>document.querySelector(sel).getAttribute('aria-busy')==='false',selector);
 async function pageSize(id,result,size,expected){
    await page.locator(id).selectOption(String(size));await idle(result);
    await page.waitForFunction(({result,expected})=>document.querySelectorAll(result+' tbody tr').length===expected,{result,expected});
 }
 try {
  await go('/biblioteca/documentos/'+fixtures.politicas.document_id);
  const actions=page.locator('.bib-document-actions');
  assert.equal(await page.locator('.bib-management-bar').count(),0);
  for(const label of ['Editar y crear nueva versión','Duplicar','PDF','Word'])assert.equal(await actions.getByRole('link',{name:label,exact:true}).count(),1);
  assert.equal(await actions.getByRole('button',{name:'Retirar versión',exact:true}).count(),1);
  const positions=await actions.locator('.btn').evaluateAll(nodes=>nodes.map(n=>Math.round(n.getBoundingClientRect().top)));
  assert(Math.max(...positions)-Math.min(...positions)<4,'Las acciones deben compartir una fila en escritorio.');
  await page.screenshot({path:path.join(out,'01-ficha.png')});
  const beforeDiscard=posts;
  await actions.getByRole('link',{name:'Editar y crear nueva versión',exact:true}).click();
  await page.locator('#bib-title').fill('Cambio para descartar');
  confirm=false;await page.getByRole('button',{name:'Descartar edición',exact:true}).click();
  assert.equal(await page.locator('#bib-title').inputValue(),'Cambio para descartar');
  confirm=true;await page.getByRole('button',{name:'Descartar edición',exact:true}).click();
  await page.waitForURL('**/documentos/**');assert.equal(posts,beforeDiscard);

  // Formatting, emojis and tables survive a real save and reload.
  await page.getByRole('link',{name:'Editar y crear nueva versión',exact:true}).click();
  const editor=page.locator('.bib-rich-editor').first();await editor.fill('Texto con formato');
  await selectText(editor);
  await page.getByRole('button',{name:'Negrita',exact:true}).first().click();
  await page.getByRole('button',{name:'Subrayado',exact:true}).first().click();
  await page.getByRole('button',{name:'Tachado',exact:true}).first().click();
  await editor.press('Control+End');await editor.press('Enter');
  await page.getByRole('button',{name:'Agregar emoji',exact:true}).first().click();
  await page.getByRole('searchbox',{name:'Buscar emoji'}).fill('equipo');
  await page.getByRole('button',{name:'Equipo',exact:true}).click();
  await page.getByRole('button',{name:'Agregar tabla',exact:true}).first().click();
  assert.equal(await editor.locator('table tr').count(),3);
  await page.getByRole('button',{name:'Guardar nueva versión',exact:true}).click();
  await page.locator('#bib-save-status').filter({hasText:'Borrador guardado'}).waitFor();
  await page.reload();await page.locator('#bib-save').waitFor();
  const html=await page.locator('.bib-rich-editor').first().innerHTML();
  assert(/<(b|strong)[ >]/.test(html)&&/<u[ >]/.test(html)&&/<(strike|s)[ >]/.test(html),html);
  assert(html.includes('👥'));assert.equal(await page.locator('.bib-rich-editor table tr').count(),3);
  await page.locator('.bib-rich-editor').first().fill('Cambio sin guardar');
  const savedPosts=posts;
  await page.getByRole('button',{name:'Descartar cambios',exact:true}).click();
  await page.locator('.bib-rich-editor table').waitFor();assert.equal(posts,savedPosts);
  assert((await page.locator('.bib-rich-editor').first().innerHTML()).includes('Texto con formato'));
  await page.locator('.bib-rich-tools').first().scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'02-formato.png')});

  // Generic competencies start populated and can be customized.
  await go('/biblioteca/administracion/nuevo?kind=descriptivos');
  assert.equal(await page.locator('[data-group="generic"] [data-block-rich]').count(),5);
  await page.locator('#bib-name').fill('Puesto con competencias editadas');
  await page.locator('#bib-area').fill('Calidad');
  const generic=page.locator('[data-group="generic"] [data-block-rich]').first();
  await generic.fill('Competencia personalizada ');
  await page.locator('[data-group="generic"] [data-emoji-picker]').first().click();
  await page.getByRole('button',{name:'Acuerdo',exact:true}).click();
  assert((await generic.innerText()).includes('🤝'));
  await page.getByRole('button',{name:'Guardar borrador',exact:true}).click();
  await page.locator('#bib-save-status').filter({hasText:'Borrador guardado'}).waitFor();
  await page.reload();await page.locator('[data-group="generic"] [data-block-rich]').first().waitFor();
  assert.equal(await page.locator('[data-group="generic"] [data-block-rich]').first().innerText(),'Competencia personalizada 🤝');
  await page.locator('[data-group="generic"]').scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'03-competencias.png')});
  await go('/biblioteca/administracion/nuevo?kind=descriptivos');
  await page.locator('#bib-name').fill('Documento que se descarta');
  const beforeNewDiscard=posts;
  await page.getByRole('button',{name:'Descartar nuevo documento',exact:true}).click();
  await page.waitForURL('**/biblioteca/administracion');assert.equal(posts,beforeNewDiscard);
  for(const url of ['/biblioteca/administracion/visibilidad','/biblioteca/administracion/importar']){
    await go(url);await page.getByRole('link',{name:'Volver',exact:true}).click();await page.waitForURL('**/biblioteca/administracion');
  }

  await go('/biblioteca/control-documental');
  assert.equal(await page.locator('#catalog-per-page').inputValue(),'25');
  assert.equal(await page.locator('#catalog-results tbody tr').count(),25);
  for(const size of [50,100,150,25])await pageSize('#catalog-per-page','#catalog-results',size,size);
  await page.locator('#q').fill('Prueba');await page.waitForFunction(()=>document.querySelector('#catalog-feedback').textContent.includes('versiones'));await idle('#catalog-results');
  await page.locator('#catalog-results th').filter({hasText:'Documento'}).locator('a').click();await idle('#catalog-results');
  assert.equal(await page.locator('#catalog-results .bib-document-link').first().textContent(),'Prueba 155');
  await page.getByRole('link',{name:'Siguiente',exact:true}).click();await idle('#catalog-results');
  assert.equal(await page.locator('#catalog-results .bib-document-link').first().textContent(),'Prueba 130');
  await pageSize('#catalog-per-page','#catalog-results',50,50);
  assert.equal(await page.locator('#catalog-results .bib-document-link').first().textContent(),'Prueba 155');
  for(const label of ['Área / responsable','Versión y estado','Próxima revisión']){
    await page.locator('#catalog-results th').filter({hasText:label}).locator('a').click();await idle('#catalog-results');
    assert.equal(await page.locator('#catalog-results th').filter({hasText:label}).getAttribute('aria-sort'),'ascending');
  }
  await page.locator('#q').fill('QA-PROCEDIMIENTOS');await page.waitForFunction(()=>document.querySelector('#catalog-feedback').textContent.includes('1 versiones'));
  assert.equal(await page.locator('#catalog-results tbody tr').count(),1);
  await page.locator('#q').fill('');await page.waitForFunction(()=>document.querySelectorAll('#catalog-results tbody tr').length===50);
  await page.screenshot({path:path.join(out,'04-listado.png')});
  for(const url of ['/biblioteca/administracion','/biblioteca/buscar','/biblioteca/coleccion/descriptivos']){
    await go(url);assert.equal(await page.locator('#catalog-per-page').inputValue(),'25');
  }
  await go('/biblioteca');await page.locator('#bib-q').fill('Prueba');await page.waitForFunction(()=>document.querySelectorAll('#index-results tbody tr').length===25);
  await pageSize('#index-per-page','#index-results',150,150);
  await go('/biblioteca/administracion/categorias-y-firmas');
  assert.equal(await page.locator('#coverage-results tbody tr').count(),25);
  for(const size of [50,100,150,25])await pageSize('#coverage-per-page','#coverage-results',size,size);
  await page.locator('#coverage-results th').filter({hasText:'Colaborador'}).locator('a').click();await idle('#coverage-results');
  assert((await page.locator('#coverage-results tbody tr').first().textContent()).includes('Otra persona de prueba'));
  await go('/biblioteca/administracion/firmas');
  for(const size of [50,100,150,25])await pageSize('#acceptance-per-page','#acceptance-results',size,size);
  await page.locator('#acceptance-results th').filter({hasText:'Versión'}).locator('a').click();await idle('#acceptance-results');
  await page.locator('#acceptance-results th').filter({hasText:'Versión'}).locator('a').click();await idle('#acceptance-results');
  assert.equal((await page.locator('#acceptance-results tbody tr').first().locator('td').nth(2).textContent()).trim(),'155');
  for(const width of [390,768]){
    await page.setViewportSize({width,height:900});
    await go('/biblioteca/documentos/'+fixtures.politicas.document_id);
    assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));
    await page.screenshot({path:path.join(out,'05-ficha-'+width+'.png')});
    await page.getByRole('link',{name:'Editar y crear nueva versión',exact:true}).click();
    await page.locator('.bib-rich-tools').first().scrollIntoViewIfNeeded();
    assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));
    await page.screenshot({path:path.join(out,'06-editor-'+width+'.png')});
  }
  assert.deepEqual(errors,[]);fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'ok',errors},null,2));
  console.log('OK: acciones compactas, descartes sin escrituras, formato persistente, emojis, competencias, regreso, tamaños, orden global y pantallas móviles.');
 }catch(error){await page.screenshot({path:path.join(out,'failure.png')});fs.writeFileSync(path.join(out,'failure.txt'),error.stack+'\n'+await page.locator('body').innerText());throw error;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
