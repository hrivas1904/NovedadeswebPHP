const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const fs=require('fs'),path=require('path'),assert=require('assert');
(async()=>{
 const base=process.env.BIBLIOTECA_TEST_URL||'http://127.0.0.1:8002';
 const root=path.join(__dirname,'../storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN);
 const out=path.join(root,'qa-home');fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1080},serviceWorkers:'block'});
 await context.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
 const page=await context.newPage(),errors=[];let posts=0;
 page.on('pageerror',e=>errors.push(e.message));
 page.on('request',r=>{if(r.method()==='POST')posts++;});
 await page.route('**/*',route=>{const u=new URL(route.request().url());if(u.origin===base&&!u.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(u.pathname))return route.abort();return route.continue();});
 const go=()=>page.goto(base+'/biblioteca',{waitUntil:'networkidle'});
 const results=page.locator('#index-results');
 const idle=()=>page.waitForFunction(()=>document.querySelector('#index-results').getAttribute('aria-busy')==='false');
 const search=async text=>{
   const response=page.waitForResponse(r=>r.url().includes('/biblioteca/buscar?')&&r.request().resourceType()==='xhr');
   await page.locator('#bib-q').fill(text);await response;await idle();
 };
 async function noOverflow(){
   assert(await page.locator('.bib-home').evaluate(el=>el.scrollWidth<=el.clientWidth+1),'El índice no debe desbordar horizontalmente.');
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),'La página no debe desbordar horizontalmente.');
 }
 try{
  await go();
  await page.locator('[data-home-submit], [data-live-submit]').waitFor();
  assert.equal(await page.locator('.bib-home-collection').count(),3);
  assert.equal(await page.locator('.bib-home-total').innerText(),'51 documentos disponibles');
  assert.deepEqual(await page.locator('.bib-home-card-count strong').allTextContents(),['5','5','41']);
  assert.equal(await results.isVisible(),false);
  assert.equal(await page.locator('.bib-home').getByRole('link',{name:'Ver como otro usuario',exact:true}).count(),1);
  assert.equal(await page.getByRole('link',{name:'Ver y firmar mi descriptivo',exact:true}).count(),0);
  assert.equal(await page.locator('[name=kind][value=instructivos]').count(),0);
  const cards=await page.locator('.bib-home-collection').evaluateAll(els=>els.map(e=>Math.round(e.getBoundingClientRect().top)));
  assert(Math.max(...cards)-Math.min(...cards)<2,'Las tres colecciones comparten fila en escritorio.');
  await noOverflow();
  await page.screenshot({path:path.join(out,'01-indice-escritorio.png')});
  // Actual live search, empty state, keyboard clearing and collection filtering.
  await search('Puesto QA');
  assert.equal(await results.locator('tbody tr').count(),25);
  assert.equal(await page.locator('[data-home-browse]').isVisible(),false);
  assert.equal(await page.locator('#index-per-page').inputValue(),'25');
  for(const size of ['50','100','150']){
    const response=page.waitForResponse(r=>r.url().includes('/biblioteca/buscar?')&&r.request().resourceType()==='xhr');
    await page.locator('#index-per-page').selectOption(size);await response;await idle();
    assert.equal(await results.locator('tbody tr').count(),39);
  }
  await results.locator('th').filter({hasText:'Documento'}).locator('a').click();await idle();
  assert.equal(await results.locator('.bib-document-link').first().innerText(),'Puesto QA 39');
  await page.screenshot({path:path.join(out,'02-resultados.png')});
  await search('sin-coincidencias-zzzz');
  assert.equal(await page.getByRole('heading',{name:'No encontramos documentos',exact:true}).count(),1);
  await page.getByRole('button',{name:'Limpiar búsqueda',exact:true}).last().click();
  assert.equal(await results.isVisible(),false);
  assert.equal(await page.locator('[data-home-browse]').isVisible(),true);
  assert.equal(await page.locator('#bib-q').inputValue(),'');
  await page.locator('[name=kind][value=procedimientos]').check({force:true});await idle();
  await page.waitForFunction(()=>document.querySelector('#index-feedback').textContent==='5 documentos');
  assert.equal(await results.locator('.bib-document-link').count(),5);
  assert((await results.locator('.bib-type').allTextContents()).every(t=>t.startsWith('Procedimientos')));
  await page.locator('#bib-q').press('Escape');
  assert.equal(await results.isVisible(),false);
  assert.equal(await page.locator('[name=kind][value=""]').isChecked(),true);
  // A failed request offers a retry without leaving the page.
  await page.route('**/biblioteca/buscar?**',route=>route.abort());
  await page.locator('#bib-q').fill('Puesto QA');
  await page.getByRole('button',{name:'Reintentar',exact:true}).waitFor();
  assert((await page.locator('#index-feedback').innerText()).includes('No se pudo completar'));
  await page.unroute('**/biblioteca/buscar?**');
  await page.getByRole('button',{name:'Reintentar',exact:true}).click();await idle();
  assert.equal(await results.locator('.bib-document-link').count(),39);
  await page.locator('#bib-q').fill('');
  assert.equal(await results.isVisible(),false);
  // Clear while a response is in flight: late results must not replace the home.
  let release;const gate=new Promise(resolve=>release=resolve);
  await page.route('**/biblioteca/buscar?**',async route=>{await gate;await route.continue().catch(()=>{});});
  const request=page.waitForRequest(r=>r.url().includes('/biblioteca/buscar?'));
  await page.locator('#bib-q').fill('Puesto QA');await request;
  await page.locator('.bib-home-clear').click();release();
  await page.unroute('**/biblioteca/buscar?**');
  await page.waitForLoadState('networkidle');
  assert.equal(await results.isVisible(),false);
  assert.equal(await page.locator('[data-home-browse]').isVisible(),true);
  // Results remain paginated and sortable; collection links remain normal links.
  await page.locator('[data-home-all]').click();await idle();
  assert.equal(await results.locator('.bib-document-link').count(),51);
  assert(!(await results.locator('.bib-type').allTextContents()).some(t=>t.startsWith('Instructivos')));
  await page.locator('#index-per-page').selectOption('25');await idle();
  assert.equal(await results.locator('tbody tr').count(),25);
  await results.getByRole('link',{name:'Siguiente',exact:true}).click();await idle();
  assert((await results.locator('.bib-pagination').innerText()).includes('Página 2'));
  await page.locator('[data-home-clear]').first().click();
  const destination=await page.locator('.bib-home-collection').first().getAttribute('href');
  await page.locator('.bib-home-collection').first().click();await page.waitForURL(destination);
  await go();
  for(const width of [1024,768,390,320]){
    await page.setViewportSize({width,height:900});
    await noOverflow();
    await page.screenshot({path:path.join(out,'03-indice-'+width+'.png')});
  }
  await page.setViewportSize({width:390,height:900});
  await page.locator('[name=kind][value=descriptivos]').check({force:true});await idle();
  await page.waitForFunction(()=>document.querySelector('#index-feedback').textContent==='41 documentos');
  await noOverflow();
  assert(await page.locator('.bib-home-results .bib-table-wrap').evaluate(el=>el.scrollWidth<=el.clientWidth+1),'Los resultados móviles se leen sin desplazamiento horizontal.');
  await page.screenshot({path:path.join(out,'04-resultados-movil.png')});
  await page.locator('.bib-home-text-button').click();
  // Employee view must retain the pending signature and hide admin-only actions.
  await context.addCookies([{name:'bib_test_persona',value:'2',url:base}]);
  await page.setViewportSize({width:1440,height:1080});
  await go();
  assert.equal(await page.locator('.bib-home-management').count(),0);
  assert.equal(await page.getByRole('link',{name:'Control documental',exact:true}).count(),0);
  assert.equal(await page.locator('.bib-home').getByRole('link',{name:'Ver como otro usuario',exact:true}).count(),0);
  assert.equal(await page.getByRole('link',{name:'Ver y firmar mi descriptivo',exact:true}).count(),1);
  assert.equal(await page.locator('.bib-home-pending-badge').innerText(),'Firma pendiente');
  await page.screenshot({path:path.join(out,'05-indice-colaborador.png')});
  await page.locator('[name=kind][value=politicas]').check({force:true});await idle();
  await page.getByRole('heading',{name:'No encontramos documentos',exact:true}).waitFor();
  // With no JavaScript, ordinary collection links and the search submit still work.
  const fallback=await browser.newContext({javaScriptEnabled:false,viewport:{width:1440,height:1080}});
  await fallback.addCookies([{name:'bib_test_persona',value:'1',url:base}]);
  const plain=await fallback.newPage();
  await plain.goto(base+'/biblioteca');
  await plain.locator('#bib-q').fill('Puesto QA');
  await plain.getByRole('button',{name:'Buscar',exact:true}).click();
  await plain.waitForURL('**/biblioteca/buscar?**');
  assert.equal(await plain.locator('.bib-document-link').count(),25);
  await fallback.close();
  assert.equal(posts,0,'La consulta del índice no debe escribir documentos.');
  assert.deepEqual(errors,[]);
  console.log('PASS: índice, búsqueda, filtros, tamaños, orden, paginación, vacíos, reintento, cancelación, permisos, firma pendiente, sin JS y pantallas 1440/1024/768/390/320. Sin escrituras.');
 }catch(e){
   await page.screenshot({path:path.join(out,'fallo.png')});
   throw e;
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
