const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

(async () => {
  const base = process.env.ORGANIGRAMA_TEST_URL || 'http://127.0.0.1:8013';
  const out = path.join(__dirname, '../storage/framework/testing/organigrama-browser/qa');
  fs.mkdirSync(out, {recursive:true});
  const browser = await chromium.launch({channel:'chrome', headless:true});
  const context = await browser.newContext({viewport:{width:1440,height:1000},serviceWorkers:'block'});
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.stack));
  page.on('dialog', dialog => dialog.accept());
  // Keep the real layout/assets; suppress unrelated application background requests.
  await page.route('**/*', route => {
    const url = new URL(route.request().url());
    if(url.origin === base && !url.pathname.startsWith('/rrhh/organigrama') && !/^\/(css|js|img|images|fonts|favicon)/.test(url.pathname)) return route.abort();
    return route.continue();
  });
  const loaded = () => page.waitForFunction(() => document.querySelector('#nodeLayer [data-node]'));
  const view = async name => {
    await page.locator(`[data-view="${name}"]`).click();
    await page.waitForTimeout(300);
  };
  const unique = async selector => {
    const ids = await page.locator(selector).evaluateAll(nodes => nodes.map(n=>n.dataset.node));
    assert.equal(ids.length,65);
    assert.equal(new Set(ids).size,65);
    if(!selector.startsWith('#verticalTree')) {
      const overlap=await page.locator(selector).evaluateAll(nodes=>{
        const rects=nodes.map(n=>n.getBoundingClientRect());
        return rects.some((a,i)=>rects.slice(i+1).some(b=>Math.min(a.right,b.right)-Math.max(a.left,b.left)>1&&Math.min(a.bottom,b.bottom)-Math.max(a.top,b.top)>1));
      });
      assert.equal(overlap,false,'Position cards must not overlap');
    }
  };
  const cleanConnectors = async () => {
    const collisions=await page.evaluate(()=>{
      const cards=[...document.querySelectorAll('#treeContent [data-node]')].map(n=>({id:n.dataset.node,rect:n.getBoundingClientRect()}));
      const found=[];
      for(const path of document.querySelectorAll('#institutionalConnectors .inst-path')){
        const matrix=path.getScreenCTM(),length=path.getTotalLength();
        for(let distance=0;distance<=length;distance+=3){
          const p=path.getPointAtLength(distance),point=new DOMPoint(p.x,p.y).matrixTransform(matrix);
          const hit=cards.find(c=>c.id!==path.dataset.parent&&c.id!==path.dataset.child&&point.x>c.rect.left+1&&point.x<c.rect.right-1&&point.y>c.rect.top+1&&point.y<c.rect.bottom-1);
          if(hit){found.push({parent:path.dataset.parent,child:path.dataset.child,crossed:hit.id});break;}
        }
      }
      return found;
    });
    assert.deepEqual(collisions,[],'Connections must never travel through an unrelated position');
  };
  const separatedExecutiveBranches = async () => {
    const geometry=await page.evaluate(()=>{
      const center=el=>{const r=el.getBoundingClientRect();return (r.left+r.right)/2;};
      return ['director-medico','gerente-general'].map(id=>{
        const card=document.querySelector(`#treeContent [data-node="${id}"]`);
        const group=document.querySelector(`[data-executive="${id}"]`);
        if(!group)return null;
        const children=[...document.querySelectorAll(`#institutionalConnectors .inst-path.direct[data-parent="${id}"]`)].map(p=>document.querySelector(`#treeContent [data-node="${p.dataset.child}"]`));
        const centers=[center(card),...children.map(center)];
        return {id,offset:Math.abs(center(card)-center(group)),left:Math.min(...centers),right:Math.max(...centers)};
      }).filter(Boolean);
    });
    for(const g of geometry)assert.ok(g.offset<1,`${g.id} must be centered above its own branch`);
    if(geometry.length===2)assert.ok(geometry[0].right<geometry[1].left,'Medical and administrative buses must not overlap');
  };
  try {
    await page.goto(base+'/rrhh/organigrama',{waitUntil:'networkidle'});
    await loaded();
    const headerHeight=await page.evaluate(()=>document.querySelector('#chartViewport').getBoundingClientRect().top-document.querySelector('#org-module').getBoundingClientRect().top);
    assert.ok(headerHeight<120,'Compact header should leave most of the screen for the diagram');
    await page.locator('#expandAll').click();
    await page.waitForTimeout(350);
    await unique('#nodeLayer [data-node]');
    assert.ok(!(await page.locator('#nodeLayer').textContent()).includes('Sin nombre indicado'));
    assert.equal(await page.locator('#connectorLayer path').count(),65);
    await page.screenshot({path:path.join(out,'horizontal.png'),fullPage:true});
    for(const name of ['vertical','tree']) {
      await view(name);
      await page.locator('#expandAll').click();
      await page.waitForTimeout(350);
      await unique(name==='vertical'?'#verticalTree [data-node]':'#treeContent [data-node]');
      const surface=name==='tree'?'#treeContent':'#verticalTree';
      assert.ok(!(await page.locator(surface).textContent()).includes('Sin nombre indicado'));
      assert.equal(await page.locator(surface+' [data-node="emision-de-ordenes"] .inst-person').count(),0);
      if(name==='tree') {
        assert.equal(await page.locator('#institutionalConnectors path').count(),65);
        await cleanConnectors();
        await separatedExecutiveBranches();
        for(const id of ['jefatura-de-quirofano','jefatura-de-farmacia-y-compras']) {
          assert.equal(await page.locator(`#treeContent [data-node="${id}"]`).evaluate(el=>el.closest('[data-executive]').dataset.executive),'director-medico');
        }
        for(const id of ['director-medico','gerente-general']) {
          await page.locator(`#treeContent [data-toggle="${id}"]`).click();
          await page.waitForTimeout(250);
          assert.equal(await page.locator(`[data-executive="${id}"]`).count(),0);
          await separatedExecutiveBranches();
          await cleanConnectors();
          await page.locator(`#treeContent [data-toggle="${id}"]`).click();
          await page.waitForTimeout(250);
          await unique('#treeContent [data-node]');
          await separatedExecutiveBranches();
        }
        await page.locator('#fitWidth').click();
      }
      await page.screenshot({path:path.join(out,name+'.png'),fullPage:true});
    }
    await view('vertical');
    await page.locator('#collapseAll').click();
    await page.locator('summary[data-node="director-medico"]').click();
    assert.equal(await page.locator('#verticalTree [data-node="jefatura-de-enfermeria"]').count(),1);
    await page.locator('summary[data-node="director-medico"]').click();
    assert.equal(await page.locator('#verticalTree [data-node="jefatura-de-enfermeria"]').count(),0);
    await page.locator('#chartSearch').fill('Marcio');
    await page.locator('#chartSearch').press('Enter');
    await page.waitForTimeout(250);
    assert.equal(await page.locator('#verticalTree [data-node="cadete-2"]').count(),1);
    await page.locator('#chartSearch').fill('');

    await page.locator('#tabConfig').click();
    await page.locator('[data-select="secretaria-de-gerencia-y-direccion"]').click();
    await page.locator('#addPosition').click();
    await page.waitForFunction(()=>document.querySelector('#positionCount').textContent==='66');
    await page.locator('#editTitle').fill('QA posición compartida');
    await page.locator('#editPerson').fill('Responsable QA');
    await page.locator('#editRole').selectOption('2');
    await page.locator('#editEmployees').selectOption(['100']);
    await page.locator('#addDependency').click();
    await page.locator('.dep-parent').nth(1).selectOption('gerente-general');
    await page.locator('.dep-relation').nth(1).selectOption('shared');
    await page.locator('.dep-order').nth(1).fill('19');
    const saved = page.waitForResponse(r=>r.request().method()==='PUT' && r.url().includes('/posiciones/'));
    await page.locator('#savePosition').click();
    assert.equal((await saved).status(),200);
    await page.waitForTimeout(150);
    await page.reload({waitUntil:'networkidle'});
    await page.locator('#tabConfig').click();
    await page.locator('#configSearch').fill('QA posición');
    await page.locator('#positionList .position-item').click();
    assert.equal(await page.locator('#editTitle').inputValue(),'QA posición compartida');
    assert.equal(await page.locator('.dependency-row').count(),2);
    assert.equal(await page.locator('.dep-order').nth(1).inputValue(),'19');
    await page.locator('#tabChart').click();
    for (const name of ['horizontal','vertical','tree']) {
      await view(name);
      await page.locator('#expandAll').click();
      await page.waitForTimeout(250);
      const surface = name==='horizontal'?'#nodeLayer':name==='vertical'?'#verticalTree':'#treeContent';
      assert.equal(await page.locator(surface+' [data-node]').filter({hasText:'QA posición compartida'}).count(),1);
    }
    await page.locator('#tabConfig').click();
    const deleted = page.waitForResponse(r=>r.request().method()==='DELETE');
    await page.locator('#deletePosition').click();
    assert.equal((await deleted).status(),200);
    await page.waitForFunction(()=>document.querySelector('#positionCount').textContent==='65');
    await page.locator('#tabChart').click();
    for(const name of ['horizontal','tree']) {
      await view(name);
      await page.locator('#zoomIn').click();
      const before=await page.locator('#zoomReadout').textContent();
      await page.locator('#zoomIn').click();
      assert.notEqual(await page.locator('#zoomReadout').textContent(),before);
      await page.locator('#centerRoot').click();
      for(let i=0;i<6;i++)await page.locator('#zoomIn').click();
      const vp=page.locator(name==='horizontal'?'#chartViewport':'#treeViewport');
      await vp.scrollIntoViewIfNeeded();
      await vp.evaluate(el=>el.scrollLeft=0);
      const rect=await vp.boundingBox();
      await page.mouse.move(rect.x+250,rect.y+15);
      await page.mouse.down();
      await page.mouse.move(rect.x+50,rect.y+15,{steps:8});
      await page.mouse.up();
      assert.ok(await vp.evaluate(el=>el.scrollLeft>100),'Dragging the background must pan the diagram');
      await page.locator('#fitWidth').click();
    }
    await page.setViewportSize({width:390,height:844});
    for(const name of ['horizontal','vertical','tree']) {
      await view(name);
      await page.screenshot({path:path.join(out,`${name}-mobile.png`),fullPage:true});
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+2),'Mobile page must not overflow horizontally');
    }
    await context.addCookies([{name:'org_test_persona',value:'2',url:base}]);
    await page.reload({waitUntil:'networkidle'});
    assert.equal(await page.locator('#tabConfig').count(),0);
    assert.equal(await page.locator('#panelConfig').count(),0);
    const prefs=await page.evaluate(()=>Object.entries(localStorage).filter(([key])=>key.startsWith('rrhh-organigrama-')).map(([,v])=>JSON.parse(v)));
    assert.ok(prefs.every(p=>!p.nodes&&!p.edges),'Only visual preferences may persist in localStorage');
    // The existing shell depends on a CDN. Record only its known missing-jQuery
    // errors separately; any module error or other exception fails this test.
    const shellErrors=errors.filter(e=>e.startsWith('ReferenceError: $ is not defined') && /(?:js\/home\/alertas\.js|js\/sideBar\.js|rrhh\/organigrama:\d+:34)/.test(e));
    assert.deepEqual(errors.filter(e=>!shellErrors.includes(e)),[]);
    fs.writeFileSync(path.join(out,'result.json'), JSON.stringify({ok:true,views:3,positions:65,edges:65,mobile:true,crud:true,moduleErrors:[],shellErrors},null,2));
    console.log('OK: tres vistas, 65 nodos únicos, 65 vínculos, búsqueda, expansión, CRUD persistente, permisos y móvil.');
  } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
