(() => {
'use strict';
const module = document.getElementById('org-module');
if (!module?.querySelector('#embedded-data')) return;
const $ = s => module.querySelector(s);
const $$ = s => [...module.querySelectorAll(s)];
const canEdit = module.dataset.canEdit === '1';
const base = module.dataset.base;
const catalogos = JSON.parse($('#org-catalogos')?.textContent || '{}');
const VIEW_STATE_KEY = 'rrhh-organigrama-v1-' + module.dataset.user;
let data = JSON.parse($('#embedded-data').textContent);
let saving = false;
let selectedId=null;
let searchTerm='';
let currentView='horizontal';
let horizontalLayout=null;
let treeLayout=null;
let zoomByView={horizontal:1,tree:1};
let expandedByView={horizontal:new Set(),vertical:new Set(),tree:new Set()};


function nodeMap(){return new Map(data.nodes.map(n=>[n.id,n]))}
function parentsOf(id){return data.edges.filter(e=>e.childId===id).sort((a,b)=>a.order-b.order)}
function childrenOf(id){return data.edges.filter(e=>e.parentId===id).sort((a,b)=>a.order-b.order)}
function relationLabel(rel){return rel==='support'?'Apoyo / asesoramiento':rel==='shared'?'Dependencia compartida':''}
function sanitize(text=''){return String(text).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function personHtml(person, className=''){
  const name=String(person??'').trim();
  return name?`<span${className?` class="${className}"`:''}>${sanitize(name)}</span>`:'';
}
function showToast(message,error=false){const t=$('#toast');t.textContent=message;t.classList.toggle('error',error);t.classList.add('show');clearTimeout(showToast.timer);showToast.timer=setTimeout(()=>t.classList.remove('show'),2400)}
function updateCounts(){const total=data.nodes.length;$('#positionCount').textContent=total;$('#totalCount').textContent=total}

function initViewState(){
  const parentIds=new Set(data.edges.map(e=>e.parentId));
  const rootChildren=childrenOf(data.rootId).map(e=>e.childId);
  expandedByView.horizontal=new Set(data.nodes.filter(n=>n.expanded).map(n=>n.id));
  expandedByView.horizontal.add(data.rootId);
  expandedByView.vertical=new Set([data.rootId,...rootChildren]);
  expandedByView.tree=new Set([...parentIds,data.rootId]);
  try{
    const saved=JSON.parse(localStorage.getItem(VIEW_STATE_KEY)||'null');
    if(saved){
      if(['horizontal','vertical','tree'].includes(saved.currentView))currentView=saved.currentView;
      if(saved.zoom)for(const view of ['horizontal','tree'])if(Number.isFinite(saved.zoom[view]))zoomByView[view]=Math.max(.02,Math.min(1.6,saved.zoom[view]));
      for(const view of ['horizontal','vertical','tree']){
        if(Array.isArray(saved.expanded?.[view]))expandedByView[view]=new Set(saved.expanded[view].filter(id=>data.nodes.some(n=>n.id===id)));
      }
    }
  }catch(e){console.warn('No se pudo recuperar el estado visual',e)}
  const queryView=new URLSearchParams(location.search).get('view');
  if(['horizontal','vertical','tree'].includes(queryView))currentView=queryView;
}
function saveViewState(){
  try{localStorage.setItem(VIEW_STATE_KEY,JSON.stringify({currentView,zoom:zoomByView,expanded:Object.fromEntries(Object.entries(expandedByView).map(([k,v])=>[k,[...v]]))}))}catch(e){console.warn('No se pudo guardar el estado visual',e)}
}
function reconcileViewState(){
  const ids=new Set(data.nodes.map(n=>n.id));
  for(const view of Object.keys(expandedByView))expandedByView[view]=new Set([...expandedByView[view]].filter(id=>ids.has(id)));
  expandedByView.horizontal.add(data.rootId);expandedByView.vertical.add(data.rootId);expandedByView.tree.add(data.rootId);
  for(const e of data.edges)if(childrenOf(e.parentId).length)expandedByView.tree.add(e.parentId);
}
async function persist(method, path, payload) {
  if (!canEdit || saving) return;
  saving = true;
  $$('#panelConfig button').forEach(b => b.disabled = true);
  try {
    const response = await fetch(base + path, {
      method, credentials:'same-origin', headers:{'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body:JSON.stringify({...payload, revision:data.revision})
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'No se pudieron guardar los cambios.');
    data = result.data; selectedId = result.selectedId;
    reconcileViewState(); updateCounts(); renderPositionList(); renderEditor(); saveViewState();
    showToast('Cambios guardados');
  } catch (error) { showToast(error.message, true); }
  finally { saving = false; $$('#panelConfig button').forEach(b => b.disabled = false); const del=$('#deletePosition'); if(del) del.disabled=selectedId===data.rootId; }
}
function buildTree(view=currentView){
  const nodes=nodeMap(), seen=new Set();let seq=0;
  const walk=(nodeId,depth,path,edge=null,branch='root')=>{
    if(path.includes(nodeId)||seen.has(nodeId))return null;seen.add(nodeId);
    const node=nodes.get(nodeId);if(!node)return null;
    let nextBranch=branch;
    if(depth===1){nextBranch=nodeId==='director-medico'?'medical':nodeId==='gerente-general'?'admin':nodeId}
    const inst={instanceId:`i${seq++}`,nodeId,node,depth,edge,branch:nextBranch,children:[],span:0,x:0,y:0};
    if(expandedByView[view].has(nodeId)){
      for(const childEdge of childrenOf(nodeId)){
        const child=walk(childEdge.childId,depth+1,[...path,nodeId],childEdge,nextBranch);
        if(child)inst.children.push(child);
      }
    }
    return inst;
  };
  return walk(data.rootId,0,[]);
}
function flattenTree(root){const out=[];const visit=n=>{if(!n)return;out.push(n);n.children.forEach(visit)};visit(root);return out}
function hierarchyDepths(){const depth=new Map([[data.rootId,0]]),q=[data.rootId];while(q.length){const p=q.shift();for(const e of childrenOf(p)){const d=(depth.get(p)||0)+1;if(!depth.has(e.childId)||d<depth.get(e.childId)){depth.set(e.childId,d);q.push(e.childId)}}}return depth}

function calculateHorizontalLayout(){
  const root=buildTree('horizontal');const CARD_W=270,CARD_H=136,GAP_X=105,GAP_Y=24,PAD_X=80,PAD_Y=70;
  const instances=flattenTree(root),visible=new Set(instances.map(n=>n.nodeId)),depths=new Map([[data.rootId,0]]);
  // La profundidad máxima mantiene un hijo a la derecha de TODOS sus superiores visibles.
  for(let pass=0;pass<instances.length;pass++){
    let changed=false;
    for(const edge of data.edges){
      if(!visible.has(edge.childId)||!expandedByView.horizontal.has(edge.parentId)||!depths.has(edge.parentId))continue;
      const depth=depths.get(edge.parentId)+1;
      if(depth>(depths.get(edge.childId)??-1)){depths.set(edge.childId,depth);changed=true;}
    }
    if(!changed)break;
  }
  instances.forEach(n=>n.depth=depths.get(n.nodeId)??n.depth);
  const measure=n=>{if(!n.children.length){n.span=CARD_H;return n.span}const total=n.children.reduce((s,c)=>s+measure(c),0)+GAP_Y*(n.children.length-1);n.span=Math.max(CARD_H,total);return n.span};
  measure(root);let maxDepth=0;
  const place=(n,top)=>{maxDepth=Math.max(maxDepth,n.depth);n.x=PAD_X+n.depth*(CARD_W+GAP_X);n.y=top+(n.span-CARD_H)/2;let ct=top;for(const c of n.children){place(c,ct);ct+=c.span+GAP_Y}};
  place(root,PAD_Y);return {root,instances:flattenTree(root),width:PAD_X*2+(maxDepth+1)*CARD_W+maxDepth*GAP_X,height:PAD_Y*2+root.span,CARD_W,CARD_H};
}
function renderHorizontal({preserveScroll=true}={}){
  const vp=$('#chartViewport');const prev={left:vp.scrollLeft,top:vp.scrollTop};horizontalLayout=calculateHorizontalLayout();
  const {instances,width,height,CARD_W,CARD_H}=horizontalLayout;const nodes=$('#nodeLayer'),svg=$('#connectorLayer'),canvas=$('#chartCanvas');
  canvas.style.width=width+'px';canvas.style.height=height+'px';nodes.style.width=width+'px';nodes.style.height=height+'px';svg.setAttribute('width',width);svg.setAttribute('height',height);svg.setAttribute('viewBox',`0 0 ${width} ${height}`);
  const term=searchTerm.trim().toLocaleLowerCase('es');
  nodes.innerHTML=instances.map(inst=>{
    const n=inst.node,count=childrenOf(n.id).length,rel=inst.edge?.relation||'direct';const hit=term&&(`${n.title} ${n.person||''}`).toLocaleLowerCase('es').includes(term);
    const cls=['org-card',inst.depth===0?'root':'',inst.depth===1?'executive':'',rel==='support'?'support':'',rel==='shared'?'shared':''].filter(Boolean).join(' ');
    const relPill=rel==='direct'?'':`<span class="relation-pill ${rel}">${rel==='support'?'Apoyo':'Compartida'}</span>`;
    const open=expandedByView.horizontal.has(n.id);const toggle=count?`<button class="expand-toggle" data-toggle="${sanitize(n.id)}" aria-label="${open?'Contraer':'Desplegar'} ${sanitize(n.title)}" title="${open?'Contraer':'Desplegar'}">${open?'−':'+'}</button>`:'';
    return `<article class="${cls}${hit?' search-hit':''}" data-node="${sanitize(n.id)}" style="left:${inst.x}px;top:${inst.y}px" tabindex="0"><div class="card-top"><div class="card-copy"><div class="role">${sanitize(n.title)}</div>${personHtml(n.person,'person')}</div>${toggle}</div><div class="card-foot"><div style="display:flex;gap:5px;align-items:center"><span class="level-pill">Nivel ${inst.depth+1}</span>${relPill}</div>${count?`<span class="child-count">${count} ${count===1?'dependencia':'dependencias'}</span>`:''}</div></article>`;
  }).join('');
  const byId=new Map(instances.map(i=>[i.nodeId,i]));
  svg.innerHTML=data.edges.filter(e=>byId.has(e.parentId)&&byId.has(e.childId)&&expandedByView.horizontal.has(e.parentId)).map(e=>{
    const p=byId.get(e.parentId),c=byId.get(e.childId),x1=p.x+CARD_W,y1=p.y+CARD_H/2,x2=c.x,y2=c.y+CARD_H/2,dx=Math.max(40,(x2-x1)*.48);
    return `<path class="connector ${e.relation}" d="M ${x1} ${y1} C ${x1+dx} ${y1}, ${x2-dx} ${y2}, ${x2} ${y2}"/>`;
  }).join('');
  applyZoom();$('#visibleCount').textContent=new Set(instances.map(i=>i.nodeId)).size;if(preserveScroll){vp.scrollLeft=prev.left;vp.scrollTop=prev.top}
}

function verticalNodeHtml(inst){
  const n=inst.node,count=childrenOf(n.id).length,rel=inst.edge?.relation||'direct',open=expandedByView.vertical.has(n.id),term=searchTerm.trim().toLocaleLowerCase('es'),hit=term&&(`${n.title} ${n.person||''}`).toLocaleLowerCase('es').includes(term);
  const relation=rel==='direct'?'':`<small>${sanitize(relationLabel(rel))}</small>`;const classes=`vertical-item relation-${rel}`;
  if(!count)return `<li class="${classes}"><div class="vertical-card${hit?' vertical-search-hit':''}" data-node="${sanitize(n.id)}"><div class="vertical-copy"><strong>${sanitize(n.title)}</strong>${personHtml(n.person)}${relation}</div></div></li>`;
  const children=inst.children.map(verticalNodeHtml).join('');
  const links=childrenOf(n.id).filter(e=>!inst.children.some(c=>c.nodeId===e.childId)).map(e=>`<button type="button" data-jump="${sanitize(e.childId)}">↗ ${sanitize(nodeMap().get(e.childId)?.title)} · ${sanitize(relationLabel(e.relation)||'Dependencia directa')}</button>`).join('');
  return `<li class="${classes}"><details data-details-node="${sanitize(n.id)}" ${open?'open':''}><summary class="vertical-summary${hit?' vertical-search-hit':''}" data-node="${sanitize(n.id)}"><div class="vertical-copy"><strong>${sanitize(n.title)}</strong>${personHtml(n.person)}${relation}</div><span class="vertical-count">${count}</span></summary><ul>${children}</ul><div class="vertical-links">${links}</div></details></li>`;
}
function renderVertical(){
  const root=buildTree('vertical');$('#verticalTree').innerHTML=`<ul class="vertical-tree">${verticalNodeHtml(root)}</ul>`;
  requestAnimationFrame(updateVerticalVisible);
}
function updateVerticalVisible(){const nodes=$$('#verticalTree [data-node]').filter(el=>el.getClientRects().length>0);$('#visibleCount').textContent=new Set(nodes.map(el=>el.dataset.node)).size}

function branchTone(branch,depth=1){if(branch==='medical')return depth%2===1?'green':'blue';if(branch==='admin')return depth%2===1?'blue':'green';return depth%2===1?'blue':'green'}
function executiveBranch(id){return id==='director-medico'?'medical':id==='gerente-general'?'admin':id}
function instCardHtml(node,{branch='admin',depth=1,relation='direct',kind='normal',compact=false}={}){
  const count=childrenOf(node.id).length,open=expandedByView.tree.has(node.id),term=searchTerm.trim().toLocaleLowerCase('es'),hit=term&&(`${node.title} ${node.person||''}`).toLocaleLowerCase('es').includes(term);
  const tone=kind==='root'?'root':branchTone(branch,depth);const relClass=relation!=='direct'?` relation-${relation}`:'';const relationTag=relation==='direct'?'':`<span class="inst-relation">${relation==='support'?'Apoyo':'Compartida'}</span>`;const toggle=count?`<button class="inst-toggle" data-toggle="${sanitize(node.id)}" aria-label="${open?'Contraer':'Desplegar'} ${sanitize(node.title)}">${open?'−':'+'}</button>`:'';
  return `<div class="inst-card ${tone}${compact?' compact':''}${relClass}${hit?' search-hit':''}" data-node="${sanitize(node.id)}" data-branch="${sanitize(branch)}" tabindex="0"><div class="inst-role">${sanitize(node.title)}</div>${personHtml(node.person,'inst-person')}${relationTag}${toggle}</div>`;
}
function renderBranchNode(nodeId,branch,depth,edge,rendered){
  const node=nodeMap().get(nodeId);if(!node||rendered.has(nodeId))return '';rendered.add(nodeId);const relation=edge?.relation||'direct';const directChildren=expandedByView.tree.has(nodeId)?childrenOf(nodeId):[];
  const children=directChildren.map(e=>renderBranchNode(e.childId,branch,depth+1,e,rendered)).filter(Boolean).join('');
  // Hermanos en una sola fila: una fila inferior sugería una dependencia inexistente.
  return `<div class="inst-node-wrap">${instCardHtml(node,{branch,depth,relation,compact:depth>=3})}${children?`<div class="inst-children">${children}</div>`:''}</div>`;
}
function executiveSequence(executiveEdges){
  const execIds=executiveEdges.map(e=>e.childId);const lateralMap=new Map();
  execIds.forEach((id,index)=>(expandedByView.tree.has(id)?childrenOf(id):[]).filter(e=>e.relation!=='direct'&&!execIds.includes(e.childId)).forEach(e=>{const item=lateralMap.get(e.childId)||{id:e.childId,parents:[],relations:[]};item.parents.push(index);item.relations.push(e.relation);lateralMap.set(e.childId,item)}));
  const lateral=[...lateralMap.values()];const left=lateral.filter(x=>x.parents.length===1&&x.parents[0]===0&&x.relations[0]==='support');const shared=lateral.filter(x=>x.parents.length>1||x.relations.includes('shared'));const right=lateral.filter(x=>!left.includes(x)&&!shared.includes(x));const seq=[];
  left.forEach(x=>seq.push({type:'lateral',...x}));if(executiveEdges[0])seq.push({type:'executive',edge:executiveEdges[0],id:executiveEdges[0].childId,index:0});shared.forEach(x=>seq.push({type:'lateral',...x}));if(executiveEdges[1])seq.push({type:'executive',edge:executiveEdges[1],id:executiveEdges[1].childId,index:1});right.forEach(x=>seq.push({type:'lateral',...x}));for(let i=2;i<executiveEdges.length;i++)seq.push({type:'executive',edge:executiveEdges[i],id:executiveEdges[i].childId,index:i});
  return seq;
}
function drawInstitutionalConnectors(){
  const content=$('#treeContent'),svg=$('#institutionalConnectors');
  if(!content||!svg||!content.offsetWidth)return;
  const base=content.getBoundingClientRect(),scale=base.width/content.offsetWidth;
  if(!scale)return;
  const width=content.scrollWidth,height=content.scrollHeight;
  svg.setAttribute('width',width);svg.setAttribute('height',height);svg.setAttribute('viewBox',`0 0 ${width} ${height}`);
  const rect=el=>{const r=el.getBoundingClientRect();return {x:(r.left-base.left)/scale,y:(r.top-base.top)/scale,w:r.width/scale,h:r.height/scale}};
  const paths=[];
  for(const edge of data.edges){
    const parent=content.querySelector(`[data-node="${CSS.escape(edge.parentId)}"]`),child=content.querySelector(`[data-node="${CSS.escape(edge.childId)}"]`);
    if(!parent||!child||!expandedByView.tree.has(edge.parentId))continue;
    const p=rect(parent),c=rect(child),x1=p.x+p.w/2,x2=c.x+c.w/2;
    let d;
    if(c.y>=p.y+p.h+10){
      const y1=p.y+p.h,mid=y1+Math.min(14,(c.y-y1)/2);
      d=`M ${x1} ${y1} V ${mid} H ${x2} V ${c.y}`;
    }else{
      // Apoyos/compartidas laterales viajan por encima de la fila, nunca a través de otra tarjeta.
      const rail=Math.min(p.y,c.y)-14;
      d=`M ${x1} ${p.y} V ${rail} H ${x2} V ${c.y}`;
    }
    paths.push(`<path class="inst-path ${edge.relation}" data-parent="${sanitize(edge.parentId)}" data-child="${sanitize(edge.childId)}" data-branch="${sanitize(parent.dataset.branch)}" d="${d}"><title>${sanitize(nodeMap().get(edge.parentId).title)} → ${sanitize(nodeMap().get(edge.childId).title)}</title></path>`);
  }
  svg.innerHTML=paths.join('');
}
function renderTree({preserveScroll=true}={}){
  const vp=$('#treeViewport'),prev={left:vp.scrollLeft,top:vp.scrollTop},nodes=nodeMap(),root=nodes.get(data.rootId);
  const executiveEdges=expandedByView.tree.has(data.rootId)?childrenOf(data.rootId):[];
  const sequence=executiveSequence(executiveEdges);
  // Reservar primero los nodos superiores evita copias cuando otra rama también los alcanza.
  const rendered=new Set([data.rootId,...sequence.map(item=>item.id)]);
  const shared=sequence.filter(item=>item.type==='lateral'&&(item.parents.length>1||item.relations.includes('shared')));
  const local=sequence.filter(item=>item.type==='lateral'&&!shared.includes(item));
  const columns=[];
  executiveEdges.forEach((edge,index)=>{
    columns.push({type:'executive',id:edge.childId,edge,index});
    if(index===0)columns.push(...shared);
  });
  // Los apoyos conservan sus descendientes y se ubican junto a SU superior.
  const supportHtml=item=>{
    const branch=executiveBranch(executiveEdges[item.parents[0]].childId);
    const descendants=expandedByView.tree.has(item.id)?childrenOf(item.id).map(e=>renderBranchNode(e.childId,branch,2,e,rendered)).join(''):'';
    return `<div class="inst-node-wrap">${instCardHtml(nodes.get(item.id),{branch,depth:1,relation:'support',compact:true})}${descendants?`<div class="inst-children">${descendants}</div>`:''}</div>`;
  };
  const headers=columns.map((item,index)=>{
    const branch=item.type==='executive'?executiveBranch(item.id):'shared';
    const card=instCardHtml(nodes.get(item.id),{branch,depth:1,relation:item.type==='executive'?item.edge.relation:'shared',compact:item.type!=='executive'});
    if(item.type!=='executive')return `<div class="inst-shared-head" style="grid-column:${index+1};grid-row:1">${card}</div>`;
    const supports=local.filter(s=>s.parents[0]===item.index).map(supportHtml).join('');
    const onLeft=item.index===0;
    return `<div class="inst-executive-head" data-executive-head="${sanitize(item.id)}" style="grid-column:${index+1};grid-row:1"><div class="inst-supports left">${onLeft?supports:''}</div>${card}<div class="inst-supports right">${onLeft?'':supports}</div></div>`;
  }).join('');
  // Una misma columna mide la cabecera y toda su rama: el superior queda centrado
  // sobre sus hijos, y las barras de dos superiores nunca se fusionan por desalineación.
  const groups=new Map();
  for(const item of [...shared,...columns.filter(c=>c.type==='executive')]){
    const branch=item.type==='executive'?executiveBranch(item.id):'shared';
    const children=expandedByView.tree.has(item.id)?childrenOf(item.id):[];
    groups.set(item.id,children.map(e=>renderBranchNode(e.childId,branch,2,e,rendered)).join(''));
  }
  const groupsHtml=columns.map((item,index)=>{
    const branches=groups.get(item.id);
    return branches?`<div class="inst-executive-group" data-executive="${sanitize(item.id)}" style="grid-column:${index+1};grid-row:2">${branches}</div>`:'';
  }).join('');
  $('#treeCanvas').style.transform='scale(1)';$('#treeContent').innerHTML=`<svg class="institutional-connectors" id="institutionalConnectors" aria-hidden="true"></svg><div class="institutional-layout"><div class="inst-root-row">${instCardHtml(root,{kind:'root',depth:0,branch:'root'})}</div>${headers?`<div class="inst-executives-grid">${headers}${groupsHtml}</div>`:''}</div>`;
  requestAnimationFrame(()=>{drawInstitutionalConnectors();const content=$('#treeContent'),rawWidth=content.scrollWidth,rawHeight=content.scrollHeight;treeLayout={width:rawWidth,height:rawHeight};$('#treeCanvas').style.width=rawWidth+'px';$('#treeCanvas').style.height=rawHeight+'px';applyZoom();if(preserveScroll){vp.scrollLeft=prev.left;vp.scrollTop=prev.top}$('#visibleCount').textContent=rendered.size});
}

function renderActiveView(options={}){
  if(currentView==='horizontal')renderHorizontal(options);else if(currentView==='vertical')renderVertical();else renderTree(options);
}
function activeViewport(){return currentView==='horizontal'?$('#chartViewport'):currentView==='tree'?$('#treeViewport'):$('#verticalScroll')}
function activeLayout(){return currentView==='horizontal'?horizontalLayout:treeLayout}
function applyZoom(){
  if(currentView==='vertical'){$$('.diagram-control').forEach(el=>el.hidden=true);return}
  $$('.diagram-control').forEach(el=>el.hidden=false);const zoom=zoomByView[currentView];const layout=activeLayout();if(!layout)return;
  const stage=currentView==='horizontal'?$('#chartStage'):$('#treeStage'),canvas=currentView==='horizontal'?$('#chartCanvas'):$('#treeCanvas');stage.style.width=(layout.width*zoom)+'px';stage.style.height=(layout.height*zoom)+'px';canvas.style.transform=`scale(${zoom})`;$('#zoomReadout').textContent=Math.round(zoom*100)+'%';
}
function setZoom(next,anchor=null){
  if(currentView==='vertical')return;const vp=activeViewport(),old=zoomByView[currentView],min=.02,max=1.6;next=Math.max(min,Math.min(max,next));if(Math.abs(next-old)<.001)return;
  const ax=anchor?.x??vp.clientWidth/2,ay=anchor?.y??vp.clientHeight/2,contentX=(vp.scrollLeft+ax)/old,contentY=(vp.scrollTop+ay)/old;zoomByView[currentView]=next;applyZoom();vp.scrollLeft=contentX*next-ax;vp.scrollTop=contentY*next-ay;saveViewState();
}
function centerOnNode(nodeId){
  if(currentView==='vertical'){const el=$(`#verticalTree [data-node="${CSS.escape(nodeId)}"]`);el?.scrollIntoView({behavior:'smooth',block:'center'});return}
  const vp=activeViewport(),zoom=zoomByView[currentView];if(currentView==='horizontal'){const inst=horizontalLayout?.instances.find(i=>i.nodeId===nodeId);if(!inst)return;vp.scrollTo({left:(inst.x+horizontalLayout.CARD_W/2)*zoom-vp.clientWidth/2,top:(inst.y+horizontalLayout.CARD_H/2)*zoom-vp.clientHeight/2,behavior:'smooth'});return}
  const el=$(`#treeContent [data-node="${CSS.escape(nodeId)}"]`);if(!el)return;const contentRect=$('#treeContent').getBoundingClientRect(),elRect=el.getBoundingClientRect();const x=(elRect.left-contentRect.left)/zoom+elRect.width/(2*zoom),y=(elRect.top-contentRect.top)/zoom+elRect.height/(2*zoom);vp.scrollTo({left:x*zoom-vp.clientWidth/2,top:y*zoom-vp.clientHeight/2,behavior:'smooth'});
}
function fitView(){
  if(currentView==='vertical')return;
  const vp=activeViewport(),layout=activeLayout();if(!layout)return;
  zoomByView[currentView]=Math.max(.02, Math.min(1.15,(vp.clientWidth-34)/layout.width,(vp.clientHeight-34)/layout.height));
  applyZoom();saveViewState();vp.scrollTo({left:0,top:0});
}
function toggleNode(id){
  const set=expandedByView[currentView];if(set.has(id))set.delete(id);else set.add(id);saveViewState();renderActiveView();setTimeout(()=>centerOnNode(id),80);
}
function setExpanded(mode){
  const set=expandedByView[currentView];set.clear();set.add(data.rootId);if(mode==='all')for(const n of data.nodes)if(childrenOf(n.id).length)set.add(n.id);saveViewState();renderActiveView({preserveScroll:false});setTimeout(()=>currentView==='vertical'?null:mode==='all'?fitView():centerOnNode(data.rootId),100);
}
function expandAncestors(id,view=currentView,seen=new Set()){
  if(seen.has(id))return;seen.add(id);for(const e of parentsOf(id)){expandedByView[view].add(e.parentId);expandAncestors(e.parentId,view,seen)}
}
function runSearch(){
  searchTerm=$('#chartSearch').value.trim();if(!searchTerm){renderActiveView();return}const lower=searchTerm.toLocaleLowerCase('es');const found=data.nodes.find(n=>(`${n.title} ${n.person||''}`).toLocaleLowerCase('es').includes(lower));if(!found){showToast('No se encontraron coincidencias',true);return}
  expandAncestors(found.id);renderActiveView({preserveScroll:false});setTimeout(()=>centerOnNode(found.id),140);
}

function sizeViewport(){
  const viewport=activeViewport();
  if(viewport&&!$('#panelChart').hidden)module.style.setProperty('--org-viewport-height',Math.max(280,window.innerHeight-viewport.getBoundingClientRect().top-42)+'px');
}
function switchView(view,{initial=false}={}){
  if(!['horizontal','vertical','tree'].includes(view))return;currentView=view;$$('.view-option').forEach(b=>{b.classList.toggle('active',b.dataset.view===view);b.setAttribute('aria-pressed',String(b.dataset.view===view))});
  $('#horizontalSurface').hidden=view!=='horizontal';$('#verticalSurface').hidden=view!=='vertical';$('#treeSurface').hidden=view!=='tree';
  $$('.diagram-control').forEach(el=>el.hidden=view==='vertical');sizeViewport();
  saveViewState();requestAnimationFrame(()=>{renderActiveView({preserveScroll:false});setTimeout(()=>{if(view==='tree')fitView();else if(view==='horizontal'&&!initial)centerOnNode(data.rootId)},120)});
}

function openTab(name){
  const chart=name==='chart';$('#panelChart').hidden=!chart;if($('#panelConfig'))$('#panelConfig').hidden=chart;$('#tabChart').hidden=chart;if($('#tabConfig')){$('#tabConfig').hidden=!chart;$('#tabConfig').setAttribute('aria-expanded',String(!chart))}
  if(chart)requestAnimationFrame(()=>switchView(currentView,{initial:true}));else{renderPositionList();if(!selectedId)selectedId=data.rootId;renderEditor()}
}
function renderPositionList(){
  const term=$('#configSearch').value.trim().toLocaleLowerCase('es'),depths=hierarchyDepths();const ordered=[...data.nodes].sort((a,b)=>(depths.get(a.id)??99)-(depths.get(b.id)??99)||a.title.localeCompare(b.title,'es'));
  $('#positionList').innerHTML=ordered.filter(n=>(`${n.title} ${n.person||''}`).toLocaleLowerCase('es').includes(term)).map(n=>`<button class="position-item ${selectedId===n.id?'active':''}" data-select="${sanitize(n.id)}"><span><strong>${sanitize(n.title)}</strong>${personHtml(n.person)}</span><em>N${(depths.get(n.id)??0)+1}</em></button>`).join('')||'<div class="empty-editor">No hay coincidencias.</div>';
}
function parentOptions(excludeId,selected){return data.nodes.filter(n=>n.id!==excludeId).sort((a,b)=>a.title.localeCompare(b.title,'es')).map(n=>`<option value="${sanitize(n.id)}" ${n.id===selected?'selected':''}>${sanitize(n.title)} · ${sanitize(n.id)}</option>`).join('')}
function renderEditor(){
  const n=data.nodes.find(n=>n.id===selectedId);if(!n){$('#editorPanel').innerHTML='<div class="empty-editor">Seleccioná una posición para comenzar.</div>';return}const parentEdges=parentsOf(n.id);
  $('#editorPanel').innerHTML=`<div class="panel-head"><h3>Editar posición</h3><p>Identificador interno: ${sanitize(n.id)}</p></div><div class="editor-body"><div class="form-grid"><div class="field full"><label for="editTitle">Nombre de la posición</label><input id="editTitle" value="${sanitize(n.title)}"></div><div class="field full"><label for="editPerson">Responsable/s</label><input id="editPerson" value="${sanitize(n.person||'')}" placeholder="Sin nombre indicado"><span class="field-help">Texto visible. Separá varios nombres con “·” y vinculá los colaboradores debajo.</span></div>${catalogFields(n)}</div><div class="dependencies"><div class="dependencies-head"><div><h4>Dependencias jerárquicas</h4><div class="field-help">Una posición puede depender de más de un responsable.</div></div>${n.id===data.rootId?'':'<button class="tool-button" id="addDependency">＋ Agregar</button>'}</div><div id="dependencyRows">${n.id===data.rootId?'<div class="field-help">El Directorio es la raíz de la estructura.</div>':parentEdges.map((e,i)=>dependencyRowHtml(n,e,i)).join('')||'<div class="field-help">Sin dependencia asignada. Agregá al menos una.</div>'}</div></div><div class="editor-footer"><span class="save-state">Los cambios se guardan en el sistema.</span><div style="display:flex;gap:8px"><button class="tool-button danger" id="deletePosition" ${n.id===data.rootId?'disabled title="No se puede eliminar el Directorio"':''}>Eliminar</button><button class="tool-button active" id="savePosition">Guardar cambios</button></div></div></div>`;
}
function dependencyRowHtml(n,e,i){return `<div class="dependency-row" data-edge-index="${i}"><select class="dep-parent" aria-label="Posición superior">${parentOptions(n.id,e.parentId)}</select><select class="dep-relation" aria-label="Tipo de vínculo"><option value="direct" ${e.relation==='direct'?'selected':''}>Directa</option><option value="support" ${e.relation==='support'?'selected':''}>Apoyo</option><option value="shared" ${e.relation==='shared'?'selected':''}>Compartida</option></select><input class="dep-order" type="number" min="0" max="100000" value="${e.order??0}" aria-label="Orden entre hermanos"><button class="remove-dependency" type="button" aria-label="Quitar dependencia">×</button></div>`}
function addDependencyRow(){const n=data.nodes.find(n=>n.id===selectedId);if(!n)return;const wrap=$('#dependencyRows');if(wrap.querySelector('.field-help'))wrap.innerHTML='';const available=data.nodes.find(p=>p.id!==n.id&&!parentsOf(n.id).some(e=>e.parentId===p.id)&&!wouldCreateCycle(n.id,p.id));if(!available){showToast('No hay una dependencia válida disponible',true);return}wrap.insertAdjacentHTML('beforeend',dependencyRowHtml(n,{parentId:available.id,relation:'direct'},wrap.children.length))}
function wouldCreateCycle(childId,parentId){if(childId===parentId)return true;const stack=[childId],seen=new Set();while(stack.length){const cur=stack.pop();if(cur===parentId)return true;if(seen.has(cur))continue;seen.add(cur);for(const e of childrenOf(cur))stack.push(e.childId)}return false}
function collectEditorEdges(){const rows=$$('#dependencyRows .dependency-row'),seen=new Set(),out=[];for(let i=0;i<rows.length;i++){const parentId=rows[i].querySelector('.dep-parent').value,relation=rows[i].querySelector('.dep-relation').value;if(seen.has(parentId))throw new Error('La misma dependencia está repetida.');if(wouldCreateCycle(selectedId,parentId))throw new Error('La dependencia generaría un circuito en el organigrama.');seen.add(parentId);out.push({parentId,childId:selectedId,relation,order:Number(rows[i].querySelector('.dep-order').value)})}return out}
function catalogFields(n){
  const options=(items,value)=>'<option value="">Sin vincular</option>'+(items||[]).map(item=>`<option value="${item.id}" ${String(value)===String(item.id)?'selected':''}>${sanitize(item.nombre)} · ${item.id}</option>`).join('');
  return `<div class="field"><label for="editRole">Rol laboral existente</label><select id="editRole">${options(catalogos.roles,n.rol_id)}</select></div><div class="field"><label for="editArea">Área existente</label><select id="editArea">${options(catalogos.areas,n.area_id)}</select></div><div class="field full"><label for="editEmployees">Colaboradores vinculados</label><select id="editEmployees" multiple>${(catalogos.colaboradores||[]).map(p=>`<option value="${p.id}" ${n.legajos.includes(Number(p.id))?'selected':''}>${sanitize(p.nombre)} · Legajo ${p.id}</option>`).join('')}</select><span class="field-help">Podés vincular varias personas. Ctrl o Cmd permite selección múltiple.</span></div>`;
}
function saveEditor(){
  const n=data.nodes.find(n=>n.id===selectedId);if(!n)return;
  try {
    const parents=n.id===data.rootId?[]:collectEditorEdges();
    persist('PUT','/posiciones/'+encodeURIComponent(n.id), {title:$('#editTitle').value.trim(),person:$('#editPerson').value.trim(),parents,
      rol_id:$('#editRole').value||null,area_id:$('#editArea').value||null,legajos:[...$('#editEmployees').selectedOptions].map(o=>Number(o.value))});
  } catch(error) { showToast(error.message,true); }
}
function addPosition(){
  const parent=selectedId||data.rootId;
  persist('POST','/posiciones',{title:'Nueva posición',person:'',rol_id:null,area_id:null,legajos:[],parents:[{parentId:parent,relation:'direct',order:Math.max(-1,...childrenOf(parent).map(e=>e.order))+1}]});
}
function deletePosition(){
  const n=data.nodes.find(n=>n.id===selectedId);if(!n||n.id===data.rootId)return;
  if(childrenOf(n.id).length){showToast('Primero reasigná las posiciones que dependen de ésta',true);return;}
  if(confirm('¿Eliminar “'+n.title+'”?'))persist('DELETE','/posiciones/'+encodeURIComponent(n.id),{});
}


function setupPan(vp){let pan=null;vp.addEventListener('pointerdown',e=>{if(e.button!==0||e.target.closest('button,input,select,summary'))return;pan={x:e.clientX,y:e.clientY,left:vp.scrollLeft,top:vp.scrollTop};vp.setPointerCapture(e.pointerId);vp.classList.add('dragging')});vp.addEventListener('pointermove',e=>{if(!pan)return;vp.scrollLeft=pan.left-(e.clientX-pan.x);vp.scrollTop=pan.top-(e.clientY-pan.y)});const stop=()=>{pan=null;vp.classList.remove('dragging')};vp.addEventListener('pointerup',stop);vp.addEventListener('pointercancel',stop);vp.addEventListener('wheel',e=>{if(e.ctrlKey&&currentView!=='vertical'){e.preventDefault();const r=vp.getBoundingClientRect();setZoom(zoomByView[currentView]+(e.deltaY<0?.1:-.1),{x:e.clientX-r.left,y:e.clientY-r.top})}},{passive:false})}

$('#tabChart').addEventListener('click',()=>openTab('chart'));$('#tabConfig')?.addEventListener('click',()=>openTab('config'));
$$('.view-option').forEach(b=>b.addEventListener('click',()=>switchView(b.dataset.view)));
$('#expandAll').addEventListener('click',()=>setExpanded('all'));$('#collapseAll').addEventListener('click',()=>setExpanded('root'));$('#centerRoot').addEventListener('click',()=>centerOnNode(data.rootId));
$('#zoomOut').addEventListener('click',()=>setZoom(zoomByView[currentView]-.1));$('#zoomIn').addEventListener('click',()=>setZoom(zoomByView[currentView]+.1));$('#fitWidth').addEventListener('click',fitView);
$('#chartSearch').addEventListener('keydown',e=>{if(e.key==='Enter')runSearch()});$('#chartSearch').addEventListener('input',e=>{searchTerm=e.target.value;if(!searchTerm)renderActiveView()});
$('#nodeLayer').addEventListener('click',e=>{const b=e.target.closest('[data-toggle]');if(b)toggleNode(b.dataset.toggle)});$('#nodeLayer').addEventListener('keydown',e=>{if((e.key==='Enter'||e.key===' ')&&e.target.closest('.org-card')){e.preventDefault();const id=e.target.closest('.org-card').dataset.node;if(childrenOf(id).length)toggleNode(id)}});
$('#treeContent').addEventListener('click',e=>{const b=e.target.closest('[data-toggle]');if(b){e.stopPropagation();toggleNode(b.dataset.toggle)}});$('#treeContent').addEventListener('keydown',e=>{if((e.key==='Enter'||e.key===' ')&&e.target.closest('.inst-card')){e.preventDefault();const id=e.target.closest('.inst-card').dataset.node;if(childrenOf(id).length)toggleNode(id)}});
$('#configSearch')?.addEventListener('input',renderPositionList);$('#positionList')?.addEventListener('click',e=>{const b=e.target.closest('[data-select]');if(!b)return;selectedId=b.dataset.select;renderPositionList();renderEditor()});
$('#addPosition')?.addEventListener('click',addPosition);
$('#editorPanel')?.addEventListener('click',e=>{if(e.target.id==='addDependency')addDependencyRow();if(e.target.classList.contains('remove-dependency')){e.target.closest('.dependency-row').remove();if(!$('#dependencyRows').children.length)$('#dependencyRows').innerHTML='<div class="field-help">Sin dependencia asignada. Agregá al menos una.</div>'}if(e.target.id==='savePosition')saveEditor();if(e.target.id==='deletePosition')deletePosition()});
setupPan($('#chartViewport'));setupPan($('#treeViewport'));
let resizeTimer;
window.addEventListener('resize',()=>{clearTimeout(resizeTimer);resizeTimer=setTimeout(()=>{sizeViewport();if(currentView==='tree'){drawInstitutionalConnectors();applyZoom()}else if(currentView!=='vertical')applyZoom()},120)});

$('#verticalTree').addEventListener('click',e=>{
  const link=e.target.closest('[data-jump]');
  if(link){expandAncestors(link.dataset.jump);saveViewState();renderVertical();centerOnNode(link.dataset.jump);return;}
  const summary=e.target.closest('summary');
  if(summary){e.preventDefault();const id=summary.dataset.node,set=expandedByView.vertical;if(set.has(id))set.delete(id);else set.add(id);saveViewState();renderVertical();}
});
initViewState();updateCounts();switchView(currentView,{initial:true});
})();
