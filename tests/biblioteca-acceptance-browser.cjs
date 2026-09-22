const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const fs=require('fs'),path=require('path'),assert=require('assert');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});const context=await browser.newContext({viewport:{width:1440,height:1000}});const page=await context.newPage();const out=process.env.BIBLIOTECA_QA;fs.mkdirSync(out,{recursive:true});const base=process.env.BIBLIOTECA_TEST_URL||'http://127.0.0.1:8002';const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());
 const run=process.env.BIBLIOTECA_TEST_RUN?'-'+process.env.BIBLIOTECA_TEST_RUN.replace(/[^a-zA-Z0-9_-]/g,''):'';
 const fixture=JSON.parse(fs.readFileSync('storage/framework/testing/biblioteca-acceptance-browser'+run+'/fixture.json','utf8'));
 const persona=async id=>context.addCookies([{name:'bib_test_persona',value:String(id),url:base}]);
 await page.route('**/*',route=>{const u=new URL(route.request().url());if(u.origin===base&&!u.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(u.pathname))return route.abort();return route.continue();});
 try{
  await persona(2);await page.goto(base+'/biblioteca/mi-descriptivo',{waitUntil:'networkidle'});assert.equal(await page.locator('.bib-header a[href$="control-documental"]').count(),0);assert.equal(await page.locator('.bib-nav a[href$="administracion"]').count(),0);await page.locator('input[name=confirmed]').check();await page.getByRole('button',{name:'Firmar aceptación de la versión 1',exact:true}).click();await page.waitForURL('**/constancias/**');const receipt=page.url();assert.match(await page.locator('.bib-app').innerText(),/Aceptación registrada/);await page.screenshot({path:path.join(out,'01-constancia.png')});
  await persona(1);await page.goto(base+'/biblioteca/documentos/'+fixture.base);
  const draft=await page.evaluate(async()=>await jQuery.ajax({url:document.querySelector('[data-version]').dataset.url,method:'POST',contentType:'application/json',dataType:'json',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,Accept:'application/json'},data:JSON.stringify({action:'draft'})}));
  await page.goto(draft.editUrl);await page.locator('[data-group="tasks"] textarea').fill('Revisar documentos y confirmar su vigencia.');await page.getByRole('button',{name:'Guardar y publicar',exact:true}).click();await page.waitForURL('**/documentos/**');
  await persona(2);await page.goto(base+'/biblioteca/mi-descriptivo');assert.match(await page.locator('.bib-app').innerText(),/Tenés una aceptación pendiente/);await page.getByRole('button',{name:'Firmar aceptación de la versión 2',exact:true}).waitFor();await page.setViewportSize({width:390,height:900});assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));await page.screenshot({path:path.join(out,'02-pendiente-movil.png')});await page.locator('input[name=confirmed]').check();await page.getByRole('button',{name:'Firmar aceptación de la versión 2',exact:true}).click();await page.waitForURL('**/constancias/**');await page.goto(receipt);assert.doesNotMatch(await page.locator('.bib-app').innerText(),/confirmar su vigencia/);
  await persona(3);assert.equal((await page.goto(receipt)).status(),404);assert.equal((await page.goto(base+'/biblioteca/control-documental')).status(),403);assert.equal((await page.goto(base+'/biblioteca/administracion')).status(),403);
  await persona(1);await page.setViewportSize({width:1440,height:1000});await page.goto(base+'/biblioteca/administracion/categorias-y-firmas');assert.match(await page.locator('.bib-app').innerText(),/Sin descriptivo asignado/);assert.match(await page.locator('.bib-app').innerText(),/Firmado/);
  assert.equal(await page.getByText('Cobertura de todas las categorías',{exact:true}).count(),0);assert.equal(await page.getByRole('button',{name:'Guardar asignación',exact:true}).count(),0);
  const form=page.locator('form[action$="/employee/1001"]');await form.locator('select').selectOption(fixture.individual);
  await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));
  assert.match(await form.locator('xpath=..').locator('xpath=..').innerText(),/Pendiente de firma/);assert.equal(await page.locator('strong[data-coverage-count="pending"]').innerText(),'1');assert.equal(await page.locator('strong[data-coverage-count="signed"]').innerText(),'0');
  await page.reload();assert.equal(await form.locator('select').inputValue(),fixture.individual);
  await page.locator('#coverage-q').fill('telefonista');await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('1 colaboradores encontrados'));
  assert.match(await page.locator('#coverage-results').innerText(),/Facturación de prueba/);assert.doesNotMatch(await page.locator('#coverage-results').innerText(),/Otra persona de prueba/);
  await form.locator('select').selectOption('');await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));assert.equal(await page.locator('#coverage-q').inputValue(),'telefonista');
  await form.locator('select').selectOption(fixture.individual);await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));
  // A validation failure must restore the persisted value after the server refresh.
  await page.route('**/asignaciones/employee/1001',route=>route.fulfill({status:422,contentType:'application/json',body:JSON.stringify({message:'Documento de prueba rechazado.'})}),{times:1});
  await form.locator('select').selectOption(fixture.base);await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('No se pudo confirmar'));
  assert.equal(await form.locator('select').inputValue(),fixture.individual);
  // A second administrator changes the same row; the stale tab must not overwrite it.
  const other=await context.newPage();await other.goto(base+'/biblioteca/administracion/categorias-y-firmas');await other.locator('form[action$="/employee/1001"] select').selectOption(fixture.base);
  await other.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));await other.close();
  await form.locator('select').selectOption('');await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('otra sesión'));
  assert.equal(await form.locator('select').inputValue(),fixture.base);
  await form.locator('select').selectOption(fixture.individual);await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));
  // Failed refresh keeps stale controls disabled until the user retries.
  await page.route('**/administracion/categorias-y-firmas*',route=>route.request().headers().accept?.includes('application/json')?route.fulfill({status:503,body:'Unavailable'}):route.continue(),{times:1});
  await form.locator('select').selectOption('');await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('No se pudo actualizar la tabla'));
  assert(await form.locator('select').isDisabled());await page.getByRole('button',{name:'Reintentar',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('1 colaboradores encontrados'));
  assert.equal(await form.locator('select').inputValue(),'');await form.locator('select').selectOption(fixture.individual);await page.waitForFunction(()=>document.querySelector('#coverage-feedback').textContent.includes('Asignación guardada'));
  await page.screenshot({path:path.join(out,'03-cobertura.png')});await page.setViewportSize({width:390,height:900});assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));await page.screenshot({path:path.join(out,'04-asignacion-movil.png')});
  await persona(2);await page.goto(base+'/biblioteca/mi-descriptivo');assert.match(await page.locator('.bib-app').innerText(),/Auxiliar de prueba/);await page.locator('input[name=confirmed]').waitFor();assert.deepEqual(errors,[]);fs.writeFileSync(path.join(out,'resultado.json'),JSON.stringify({status:'ok',errors,checks:['firma propia','nueva publicación','constancia histórica','privacidad','permisos','guardado automático','persistencia tras recargar','filtros por rol y servicio','contadores actualizados','rechazo de validación','conflicto entre dos sesiones','recuperación tras fallo de actualización','móvil']},null,2));
 }catch(e){await page.screenshot({path:path.join(out,'fallo.png')});fs.writeFileSync(path.join(out,'fallo.txt'),e.stack+'\n'+await page.locator('body').innerText());throw e;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
