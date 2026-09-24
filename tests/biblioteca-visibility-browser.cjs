
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const assert=require('assert'),fs=require('fs'),path=require('path');
(async()=>{
 const base='http://127.0.0.1:8002',url=base+'/biblioteca/administracion/visibilidad';
 const root=path.resolve('storage/framework/testing/biblioteca-acceptance-browser-'+process.env.BIBLIOTECA_TEST_RUN),out=path.join(root,'qa-switch');fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({channel:'chrome',headless:true}),ctx=await browser.newContext({viewport:{width:1440,height:1050},serviceWorkers:'block'});
 await ctx.addCookies([{name:'bib_test_persona',value:'1',url:base}]);const page=await ctx.newPage(),errors=[];let documents=0,posts=0;
 page.on('pageerror',e=>errors.push(e.message));page.on('request',r=>{if(r.method()==='POST')posts++;if(r.isNavigationRequest()&&r.frame()===page.mainFrame())documents++;});
 await page.route('**/*',r=>{const u=new URL(r.request().url());return u.origin===base&&!u.pathname.startsWith('/biblioteca')&&!/^\/(css|js|img|images|fonts|favicon)/.test(u.pathname)?r.abort():r.continue();});
 const form=key=>page.locator('[data-visibility-control]').filter({has:page.locator('input[name=key][value="'+key+'"]')});
 async function idle(){await page.waitForFunction(()=>!window.BibliotecaView.isPending());}
 async function toggle(control){await control.locator('[data-visibility-toggle]').click();await idle();}
 try{
  await page.goto(url,{waitUntil:'networkidle'});await page.evaluate(()=>{window.originalNav=document.querySelector('.bib-nav');window.originalView=document.querySelector('#bib-view').firstElementChild;});
  assert.equal(await page.locator('#visibility-results tbody tr').count(),25);assert.equal(await page.getByRole('button',{name:'Guardar',exact:true}).count(),0);
  const section=form('section:instructivos');
  await section.locator('[role=switch]').focus();await page.keyboard.press('Space');await idle();
  assert(await section.locator('[role=switch]').isChecked());assert.equal(await page.locator('.bib-nav').getByRole('link',{name:'Instructivos',exact:true}).count(),1);
  await toggle(section);assert.equal(await page.locator('.bib-nav').getByRole('link',{name:'Instructivos',exact:true}).count(),0);
  assert(await page.evaluate(()=>window.originalNav===document.querySelector('.bib-nav')&&window.originalView===document.querySelector('#bib-view').firstElementChild));
  await page.locator('.bib-visibility-sections').scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'sections-desktop.png')});
  const doc=page.locator('#visibility-results [data-visibility-control]').first(),key=await doc.locator('[name=key]').inputValue();
  await toggle(doc);assert.equal(await doc.locator('[data-visibility-label]').innerText(),'Oculto');
  const state=await ctx.request.get(url+'/estado?key='+encodeURIComponent(key));assert.equal((await state.json()).visible,false);
  await toggle(doc);
  await page.route('**/biblioteca/administracion/visibilidad',r=>r.request().method()==='POST'?r.abort():r.continue());
  await toggle(doc);assert(await doc.locator('[role=switch]').isChecked());assert((await doc.locator('[role=status]').innerText()).includes('No se guardó'));
  await page.unroute('**/biblioteca/administracion/visibilidad');
  // The write succeeds but its response is lost: recover via GET without another write.
  await page.route('**/biblioteca/administracion/visibilidad',async r=>{if(r.request().method()==='POST'){await r.fetch();await r.abort();}else await r.continue();});
  const n=posts;await toggle(doc);assert.equal(posts,n+1);assert.equal(await doc.locator('[role=switch]').isChecked(),false);assert.equal(await doc.locator('[role=status]').innerText(),'Guardado');
  await page.unroute('**/biblioteca/administracion/visibilidad');await toggle(doc);
  // Stale revision from another administrator.
  const csrf=await page.locator('meta[name=csrf-token]').getAttribute('content');
  const revision=Number(await doc.locator('[name=revision]').inputValue());
  const concurrent=await ctx.request.post(url,{headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'},data:{key,visible:false,revision}});assert(concurrent.ok());
  await toggle(doc);assert.equal(await doc.locator('[role=switch]').isChecked(),false);assert((await doc.locator('[role=status]').innerText()).includes('Otra persona'));
  await toggle(doc);
  await page.route('**/biblioteca/administracion/visibilidad',r=>r.request().method()==='POST'?r.abort():r.continue());
  await page.route('**/biblioteca/administracion/visibilidad/estado?*',r=>r.abort());
  await toggle(doc);assert(await doc.locator('[role=switch]').isDisabled());assert(await doc.locator('[data-visibility-retry]').isVisible());
  await page.unroute('**/biblioteca/administracion/visibilidad');await page.unroute('**/biblioteca/administracion/visibilidad/estado?*');
  const beforeVerify=posts;await doc.locator('[data-visibility-retry]').click();await idle();assert.equal(posts,beforeVerify);assert(await doc.locator('[role=switch]').isEnabled());
  await page.locator('#visibility-results').scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'documents-desktop.png')});
  await page.locator('#visibility-kind').selectOption('procedimientos');await page.waitForFunction(()=>document.querySelector('#visibility-feedback').textContent==='5 documentos');
  await toggle(page.locator('#visibility-results [data-visibility-control]').first());await toggle(page.locator('#visibility-results [data-visibility-control]').first());
  assert.equal(documents,1);
  await page.setViewportSize({width:390,height:844});await page.locator('.bib-visibility-sections').scrollIntoViewIfNeeded();await page.screenshot({path:path.join(out,'sections-mobile.png')});
  assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
  assert.deepEqual(errors,[]);console.log('PASS: autosave, keyboard, unchanged view/menu nodes, individual documents, filters, rejected/lost responses, conflict recovery and mobile layout.');
 }catch(e){await page.screenshot({path:path.join(out,'failure.png')}).catch(()=>{});throw e;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
