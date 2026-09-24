@extends('biblioteca.layout')
@section('library')
@php
 $newVersion=$newVersion??false;
@endphp
<div class="bib-page-heading"><div><h2 id="bib-editor-title">{{ $upload?'Revisar importación':($newVersion?'Editar documento':($entry?'Editar borrador':'Nuevo documento')) }}</h2><p>{{ $collections[$kind]['label'] }}<span id="bib-editor-version">@if($entry) · {{ $newVersion?'Basada en la versión':'Versión interna' }} {{ $entry['version']['number'] }}@endif</span></p></div><a class="btn btn-outline-secondary" data-bib-cancel href="{{ $entry?route('biblioteca.show',['document'=>$entry['document']['id'],'version'=>$entry['version']['id']]):route('biblioteca.manage') }}">Volver</a></div>
@if(!$entry&&!$upload)<div class="bib-kind-picker"><label for="bib-kind">Colección</label><select class="form-select" id="bib-kind" data-new-url="{{ route('biblioteca.new') }}">@foreach($collections as $k=>$c)<option value="{{ $k }}" @selected($kind===$k)>{{ $c['label'] }}</option>@endforeach</select></div>@endif
@if($upload)<div class="alert {{ $upload['error']?'alert-warning':'alert-info' }}"><strong>{{ $upload['name'] }}</strong><br>{{ $upload['error']?:'Revisá los campos propuestos. La incorporación conserva el original y deja la versión pendiente de validación.' }}</div>
<section class="bib-section"><h3>Destino de la incorporación</h3><div class="bib-form-grid bib-reading"><div><label for="bib-destination">Incorporar como</label><select id="bib-destination" class="form-select">@if(!$upload['error'])<option value="new">Nuevo puesto</option><option value="version">Nueva versión de un puesto existente</option><option value="document">Documento de un puesto existente</option>@endif<option value="support">Fuente complementaria</option></select></div><div><label for="bib-destination-document">Puesto existente</label><select id="bib-destination-document" class="form-select"><option value="">Seleccionar</option>@foreach($destinations as $d)<option value="{{ $d['document']['id'] }}">{{ $d['title'] }} · {{ $d['area'] }}</option>@endforeach</select></div></div></section>@endif
@if($newVersion)<div class="alert alert-info" id="bib-new-version-notice">Los cambios se guardarán como una nueva versión en borrador. La versión {{ $entry['version']['number'] }} se conservará en el historial. Publicar requiere una confirmación posterior.</div>@endif
<form id="bib-editor" novalidate>
@if($kind==='descriptivos')
<section class="bib-section"><h3>Identificación del puesto</h3><div class="bib-form-grid bib-reading">@foreach(['name'=>'Nombre del puesto','area'=>'Área','sector'=>'Sector / servicio','reportsTo'=>'Reporta a','supervisor'=>'Supervisor directo','supervises'=>'Supervisa / personal a cargo'] as $field=>$label)<div><label for="bib-{{ $field }}">{{ $label }} @if(in_array($field,['name','area']))<span aria-label="obligatorio">*</span>@endif</label><input class="form-control" id="bib-{{ $field }}" data-field="{{ $field }}" maxlength="4000" value="{{ $content[$field] }}"></div>@endforeach</div></section>
<div id="bib-job-groups"></div>
<section class="bib-section"><h3>Secciones adicionales</h3><div class="bib-reading"><div id="bib-additional"></div><button type="button" class="btn btn-sm btn-outline-primary" id="bib-add-section">+ Agregar sección</button></div></section>
@else
<section class="bib-section"><h3>Identificación y control documental</h3><div class="bib-form-grid bib-reading">
@foreach(['code'=>'Código','title'=>'Título del documento','responsible'=>'Responsable','version'=>'Versión documental','validFrom'=>'Vigencia desde','lastReview'=>'Última revisión','reviewMonths'=>'Revisión cada (meses)','approver'=>'Aprobación','approvalRecord'=>'Instrumento de aprobación'] as $field=>$label)<div><label for="bib-{{ $field }}">{{ $label }}</label><input class="form-control" id="bib-{{ $field }}" data-field="{{ $field }}" value="{{ $content[$field]??'' }}" type="{{ in_array($field,['validFrom','lastReview'])?'date':($field==='reviewMonths'?'number':'text') }}" @if($field==='reviewMonths') min="1" max="120" @endif @readonly(($entry&&$field==='code') || ($kind==='politicas' && in_array($field,['approver','approvalRecord'])))></div>@endforeach
<div class="bib-full"><label for="bib-summary">Resumen</label><textarea class="form-control" id="bib-summary" data-field="summary">{{ $content['summary'] }}</textarea></div>
</div><p class="bib-form-help">{{ $kind==='politicas'?'Guardá el borrador y abrí su ficha para que Gerencia apruebe la versión. La aprobación se registra automáticamente con el usuario y fecha.':'La fecha de vigencia, la aprobación y su instrumento se requieren al publicar. Una fecha futura conserva el documento en borrador.' }}</p></section>
<div id="bib-manual-sections"></div><button type="button" class="btn btn-outline-primary mb-3" id="bib-add-manual-section">+ Agregar sección</button>
@endif
@if($entry)
<section class="bib-section"><h3>Registro del cambio</h3><div class="bib-reading"><label for="bib-change-reason">Motivo o resumen de los cambios (opcional)</label><textarea class="form-control" id="bib-change-reason" maxlength="4000" placeholder="Describí qué se actualizó en esta versión.">{{ $newVersion?'':$entry['version']['change_reason'] }}</textarea><small class="text-muted">Se conserva junto con la fecha y el autor en el historial.</small></div></section>
@endif
<div class="bib-editor-footer"><div class="bib-actions"><button class="btn btn-primary" type="submit" id="bib-save">{{ $upload?'Confirmar incorporación':($newVersion?'Guardar nueva versión':'Guardar borrador') }}</button><button class="btn btn-outline-secondary" type="button" id="bib-discard">{{ $newVersion?'Descartar edición':($entry?'Descartar cambios':($upload?'Descartar revisión':'Descartar nuevo documento')) }}</button><button class="btn btn-outline-danger {{ !$entry || $newVersion || $upload ? 'd-none' : '' }}" type="button" id="bib-delete-draft">Eliminar borrador</button><button class="btn btn-outline-secondary" type="button" id="bib-preview">Vista previa</button>@if(!$upload && $kind!=='politicas')<button class="btn btn-success" type="button" id="bib-save-publish">Guardar y publicar</button>@endif<a class="btn btn-outline-primary d-none" id="bib-saved-link" data-bib-cancel>Ver versión guardada</a></div><span id="bib-save-status" role="status">Los cambios se guardan al confirmar.</span></div>
</form>
<dialog id="bib-preview-dialog" class="bib-dialog"><div class="bib-page-heading"><h2>Vista previa</h2><button type="button" class="btn btn-outline-secondary" id="bib-preview-close">Cerrar vista previa</button></div><div id="bib-preview-content"></div></dialog>
@php
 $editorContent=$content;
 if($kind==='descriptivos'){
 foreach($editorContent['groups'] as &$blocks)$blocks=\App\Services\Biblioteca\Content::sanitizeBlocks($blocks);unset($blocks);
 foreach($editorContent['additional'] as &$section)$section['blocks']=\App\Services\Biblioteca\Content::sanitizeBlocks($section['blocks']);unset($section);
 }
 if($kind!=='descriptivos')foreach($editorContent['sections'] as &$s){$s['html']=\App\Services\Biblioteca\Content::sanitize($s['html']);}unset($s);
 $editorConfig=['kind'=>$kind,'newVersion'=>$newVersion,'content'=>$editorContent,'groups'=>config('biblioteca.groups'),'version'=>$entry['version']['id']??null,'revision'=>$entry['version']['revision']??0,'requestId'=>$requestId,'url'=>$upload?route('biblioteca.upload.confirm',$upload['id']):($entry?route('biblioteca.action',$entry['version']['id']):route('biblioteca.store')),'actionTemplate'=>route('biblioteca.action','VERSION_PLACEHOLDER'),'upload'=>(bool)$upload];
@endphp
<script type="application/json" id="bib-editor-config">@json($editorConfig)</script>
@endsection
