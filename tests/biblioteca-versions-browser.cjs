const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const fs=require('fs'),path=require('path'),assert=require('assert');
(async()=>{
 const base=process.env.BIBLIOTECA_TEST_URL||'http://127.0.0.1:8002';
 const root=path.join(__dirname,'../storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN);
 const fixtures=JSON.parse(fs.readFileSync(path.join(root,'versions.json'),'utf8'));
 const out=path.join(root,'qa');fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
 await context.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
 const page=await context.newPage(),errors=[],savedVersions={};
 page.on('pageerror',e=>errors.push(e.message));
 page.on('dialog',d=>d.accept());
 // Block unrelated application polling while exercising the library's real routes.
 await page.route('**/*',route=>{
   const url=new URL(route.request().url());
   if(url.origin===base&&!url.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(url.pathname))return route.abort();
   return route.continue();
 });
 try {
  for(const [kind,original] of Object.entries(fixtures)) {
   await page.goto(base+'/biblioteca/documentos/'+original.document_id);
   await page.getByRole('link',{name:'Editar y crear nueva versión',exact:true}).click();
   await page.getByRole('button',{name:'Guardar nueva versión',exact:true}).waitFor();
   await page.getByRole('link',{name:'Volver',exact:true}).click();
   // Opening and cancelling the editor must leave the version history unchanged.
   assert.match(await page.locator('#bib-version-history summary').textContent(),/\(1\)/);
   await page.goto(base+'/biblioteca/coleccion/'+kind);
   await page.locator('#q').fill(kind==='descriptivos'?'Analista de prueba':'Documento QA '+kind);
   await page.waitForFunction(()=>document.querySelector('#catalog-feedback').textContent.includes('1 documentos'));
   await page.getByRole('link',{name:'Editar',exact:true}).click();
   await page.getByRole('button',{name:'Guardar nueva versión',exact:true}).waitFor();
   const updated='Contenido actualizado de '+kind+'.';
   if(kind==='descriptivos')await page.locator('[data-group="tasks"] textarea').first().fill(updated);
   else await page.locator('.bib-rich-editor p').first().fill(updated);
   await page.locator('#bib-change-reason').fill('Actualizar '+kind+' para la prueba.');
   await page.getByRole('button',{name:'Vista previa',exact:true}).click();
   assert((await page.locator('#bib-preview-content').textContent()).includes(updated));
   await page.getByRole('button',{name:'Cerrar vista previa',exact:true}).click();
   const response=page.waitForResponse(r=>r.request().method()==='POST'&&r.url().includes('/accion'));
   await page.getByRole('button',{name:'Guardar nueva versión',exact:true}).click();
   const saved=await (await response).json();savedVersions[kind]=saved;
   assert.notEqual(saved.version,original.id);assert.equal(saved.number,2);
   await page.waitForURL('**/versiones/'+saved.version+'/editar');
   await page.locator('#bib-save-status').filter({hasText:'Borrador guardado'}).waitFor();
   assert.equal(await page.locator('#bib-save').textContent(),'Guardar borrador');
   assert((await page.locator('#bib-saved-link').getAttribute('href')).includes(saved.version));
   await page.reload();await page.locator('#bib-save').waitFor();
   assert.equal(await page.locator('#bib-change-reason').inputValue(),'Actualizar '+kind+' para la prueba.');
   if(kind==='descriptivos')assert.equal(await page.locator('[data-group="tasks"] textarea').first().inputValue(),updated);
   else {
     assert((await page.locator('.bib-rich-editor').textContent()).includes(updated));
     assert.equal(await page.locator('.bib-rich-editor table').count(),1);
   }
   await page.getByRole('link',{name:'Volver',exact:true}).click();
   assert(new URL(page.url()).searchParams.get('version')===saved.version);
   await page.locator('#bib-version-history summary').click();
   assert.match(await page.locator('#bib-version-history').textContent(),/Actualizar/);
   assert.match(await page.locator('#bib-version-history summary').textContent(),/\(2\)/);
   if(kind==='procedimientos')await page.screenshot({path:path.join(out,'historial.png'),fullPage:true});
   await page.goto(base+'/biblioteca/documentos/'+original.document_id+'?version='+original.id);
   assert(!(await page.locator('section[id^="seccion-"]').allTextContents()).join(' ').includes(updated));
   await page.getByRole('link',{name:'Continuar borrador · versión 2',exact:true}).click();
   if(kind==='politicas') {
      assert.equal(await page.locator('#bib-save-publish').count(),0);
      assert.equal(await page.locator('#bib-approvalRecord').inputValue(),'');
   } else {
      if(kind!=='descriptivos') {
       await page.locator('#bib-approver').fill('Dirección');
       await page.locator('#bib-approvalRecord').fill('Acta de la nueva versión');
      }
      await page.getByRole('button',{name:'Guardar y publicar',exact:true}).click();
      await page.waitForURL('**/biblioteca/documentos/**');
      await page.locator('.bib-page-heading .bib-badge.current').waitFor();
      await page.locator('#bib-version-history summary').click();
      assert.match(await page.locator('#bib-version-history').textContent(),/Histórico \/ retirado/);
   }
  }
  const target=savedVersions.procedimientos;
  await page.goto(base+'/biblioteca/versiones/'+target.version+'/editar');
  for(const width of [390,768,1440]) {
    await page.setViewportSize({width,height:900});
    const size=await page.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth}));
    assert(size.scroll<=size.width+2,JSON.stringify(size));
    await page.screenshot({path:path.join(out,'editor-'+width+'.png'),fullPage:true});
  }
  assert.deepEqual(errors,[]);
  fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'ok',collections:Object.keys(fixtures),savedVersions,errors},null,2));
  process.stdout.write('OK: editar, cancelar, guardar, recargar, continuar borrador, publicar e historial en cuatro colecciones; pantallas 390, 768 y 1440 px.\n');
 } catch(e) {
  await page.screenshot({path:path.join(out,'failure.png'),fullPage:true});
  fs.writeFileSync(path.join(out,'failure.txt'),e.stack+'\n'+await page.locator('body').innerText());
  throw e;
 } finally {await browser.close();}
})().catch(e=>{process.stderr.write(e.stack+'\n');process.exitCode=1;});
