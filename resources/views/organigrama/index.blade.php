@extends('layouts.app')
@section('title', 'Organigrama')
@section('content')
<link rel="stylesheet" href="{{ asset('css/organigrama.css') }}">
<div id="org-module" data-base="{{ route('rrhh.organigrama.index') }}" data-user="{{ auth()->id() }}" data-can-edit="{{ Gate::allows('organigrama.editar') ? '1' : '0' }}">
@if(!$disponible || empty($datos['nodes']))
    <div class="org-main"><h1>Organigrama</h1><p>La estructura institucional todavía no está disponible.</p></div>
@else
    <div class="org-main">
  <header class="org-heading">
    <h1 class="eyebrow">Nuestra organización</h1>
    <div class="org-heading-actions">
    <span class="org-total"><strong id="positionCount">{{ count($datos['nodes']) }}</strong> posiciones</span>
    <button class="tool-button" id="tabChart" aria-controls="panelChart" hidden>← Volver al gráfico</button>
    @can('organigrama.editar')
    <button class="tool-button" id="tabConfig" aria-controls="panelConfig" aria-expanded="false">Configuración</button>
    @endcan
    </div>
  </header>

  <section class="panel" id="panelChart" aria-label="Gráfico institucional">
    <div class="toolbar" aria-label="Controles del organigrama">
      <div class="view-picker" role="group" aria-label="Cambiar tipo de vista">
        <button class="view-option active" data-view="horizontal" aria-label="Vista horizontal por jerarquías" aria-pressed="true" title="Vista horizontal por jerarquías">Horizontal</button>
        <button class="view-option" data-view="vertical" aria-label="Vista vertical" aria-pressed="false" title="Vista vertical">Vertical</button>
        <button class="view-option" data-view="tree" aria-label="Vista árbol" aria-pressed="false" title="Vista árbol">Árbol</button>
      </div>
      <label class="search-wrap" aria-label="Buscar posición o persona">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.4-3.4"></path></svg>
        <input id="chartSearch" type="search" placeholder="Buscar posición o persona…" aria-label="Buscar posición o persona">
      </label>
      <div class="org-control-group" role="group" aria-label="Desplegar y contraer ramas">
      <button class="tool-button icon" id="expandAll" title="Desplegar todo" aria-label="Desplegar todo">⊞</button>
      <button class="tool-button icon" id="collapseAll" title="Contraer todo" aria-label="Contraer todo">⊟</button>
      </div>
      <div class="org-control-group" role="group" aria-label="Encuadre y zoom">
      <button class="tool-button icon diagram-control" id="centerRoot" title="Centrar en Directorio" aria-label="Centrar en Directorio">◎</button>
      <button class="tool-button icon diagram-control" id="zoomOut" aria-label="Alejar">−</button><span class="zoom-readout diagram-control" id="zoomReadout">100%</span><button class="tool-button icon diagram-control" id="zoomIn" aria-label="Acercar">＋</button>
      <button class="tool-button diagram-control" id="fitWidth" title="Ajustar a pantalla">Ajustar</button>
      </div>
    </div>
    <div class="chart-shell">
      <section class="org-surface" id="horizontalSurface">
        <div class="diagram-viewport" id="chartViewport" tabindex="0" aria-label="Organigrama horizontal desplazable">
          <div class="chart-stage" id="chartStage"><div class="chart-canvas" id="chartCanvas"><svg class="connector-layer" id="connectorLayer" aria-hidden="true"></svg><div class="node-layer" id="nodeLayer"></div></div></div>
        </div>
      </section>
      <section class="org-surface" id="verticalSurface" hidden>
        <div class="vertical-scroll" id="verticalScroll" tabindex="0" aria-label="Organigrama vertical desplegable"><div id="verticalTree"></div></div>
      </section>
      <section class="org-surface" id="treeSurface" hidden>
        <div class="diagram-viewport" id="treeViewport" tabindex="0" aria-label="Organigrama en árbol desplazable">
          <div class="tree-stage" id="treeStage"><div class="tree-canvas" id="treeCanvas"><div class="tree-content" id="treeContent"></div></div></div>
        </div>
      </section>
      <div class="chart-note"><span><strong id="visibleCount">0</strong> de <strong id="totalCount">{{ count($datos['nodes']) }}</strong> posiciones visibles</span><div class="legend" aria-label="Referencias"><span class="legend-item"><i class="legend-line"></i>Directa</span><span class="legend-item"><i class="legend-line support"></i>Apoyo</span><span class="legend-item"><i class="legend-line shared"></i>Compartida</span></div></div>
    </div>
  </section>

  @can('organigrama.editar')
  <section class="panel" id="panelConfig" aria-labelledby="tabConfig" hidden>
    <div class="config-intro">
      <div><h2>Configuración de la estructura</h2><p>Editá nombres, posiciones y dependencias. Los cambios se aplican automáticamente a las tres vistas.</p></div>
      <div class="config-actions"><button class="tool-button" id="addPosition">＋ Nueva posición</button></div>
    </div>
    <div class="config-grid">
      <aside class="config-list-panel">
        <div class="panel-head"><h3>Posiciones</h3><p>Seleccioná una posición para editarla.</p></div>
        <div class="config-search"><input id="configSearch" type="search" placeholder="Buscar en la estructura…"></div>
        <div class="position-list" id="positionList"></div>
      </aside>
      <section class="editor-panel" id="editorPanel"><div class="empty-editor">Seleccioná una posición para comenzar.</div></section>
    </div>
  </section>

@endcan</div>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script type="application/json" id="embedded-data">{!! json_encode($datos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @can('organigrama.editar')
    <script type="application/json" id="org-catalogos">{!! json_encode($catalogos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    @endcan
@endif
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/organigrama.js') }}" defer></script>
@endpush
