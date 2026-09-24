@extends('biblioteca.layout')
@section('library')
<a class="bib-back" href="{{ route('biblioteca.catalog',$entry['document']['kind']) }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Volver a {{ $collections[$entry['document']['kind']]['label'] }}</span></a>
<div class="bib-page-heading bib-document-heading"><div><span class="bib-eyebrow">{{ $entry['document']['code']?:'DESCRIPTIVO DE PUESTO' }}</span><h2>{{ $entry['title'] }}</h2><p><span class="bib-badge {{ $entry['version']['current_slot']?'current':'' }}">{{ $entry['state'] }}</span> · Versión interna {{ $entry['version']['number'] }} · {{ $entry['version']['review_state'] }}</p></div><div class="bib-actions bib-document-actions" data-version="{{ $entry['version']['id'] }}" data-revision="{{ $entry['version']['revision'] }}" data-url="{{ route('biblioteca.action',$entry['version']['id']) }}">
@if($canManage)
@if($entry['version']['state']==='Borrador'&&!$entry['version']['published_at'])<a class="btn btn-primary" href="{{ route('biblioteca.edit',$entry['version']['id']) }}">Editar borrador</a>@if($entry['document']['kind']!=='politicas')<button class="btn btn-success" data-bib-action="publish">Publicar versión</button>@endif
@else<a class="btn btn-primary" href="{{ route('biblioteca.edit',$entry['version']['id']) }}">Editar y crear nueva versión</a>@endif
@if($entry['job'])<a class="btn btn-outline-primary" href="{{ route('biblioteca.review',$entry['version']['id']) }}">Revisar descriptivo</a>@endif
<a class="btn btn-outline-secondary" href="{{ route('biblioteca.new',['kind'=>$entry['document']['kind'],'duplicate'=>$entry['version']['id']]) }}">Duplicar</a>
@if($entry['version']['state']!=='Histórico / retirado')<button class="btn btn-outline-danger" data-bib-action="retire">Retirar versión</button>@endif
@endif
<a class="btn btn-outline-secondary" href="{{ route('biblioteca.export',[$entry['version']['id'],'pdf']) }}">PDF</a><a class="btn btn-outline-secondary" href="{{ route('biblioteca.export',[$entry['version']['id'],'docx']) }}">Word</a><button class="btn btn-outline-secondary" type="button" data-bib-print>Imprimir</button></div></div>
@if($entry['situation'])<div class="alert alert-warning">{{ $entry['situation'] }}</div>@endif
@if($entry['version']['state']==='Borrador')<div class="alert alert-info">Este es un borrador. Guardarlo no reemplaza la versión publicada.</div>@endif

@if($canManage && $entry['document']['kind']==='politicas')
<div class="bib-callout"><div><strong>Aprobación de Gerencia</strong>
@if(!$entry['version']['published_at'] && $entry['version']['state']!=='Histórico / retirado')
<p>Esta versión requiere la aprobación de Mariano Cardoner. Guardar un borrador no lo hace visible a los colaboradores.</p>
@if(\App\Services\Biblioteca\Governance::canApprove(auth()->user()))
<form method="post" action="{{ route('biblioteca.policy.approve',$entry['version']['id']) }}">@csrf
<input type="hidden" name="revision" value="{{ $entry['version']['revision'] }}">
<label class="bib-check"><input type="checkbox" name="confirmed" value="1" required> Revisé el contenido y apruebo esta versión como Gerente.</label>
<label class="d-block">Observación (opcional)<textarea class="form-control" name="reason" maxlength="4000"></textarea></label>
<button class="btn btn-success mt-2">Aprobar y publicar política</button></form>
@endif
@else<p>{{ $entry['content']['approvalRecord']??'Sin aprobación registrada en la biblioteca. Creá un borrador para aprobar una nueva versión.' }}</p>@endif
</div><a class="btn btn-outline-primary" href="{{ route('biblioteca.visibility') }}">Mostrar u ocultar</a></div>
@endif
@if($canManage)
@php($drafts=array_filter($history,fn($v)=>$v['state']==='Borrador'&&!$v['published_at']&&$v['id']!==$entry['version']['id']))
@if($drafts)<div class="alert alert-info"><strong>Borradores en preparación</strong><p class="mb-2">Podés continuar una versión ya guardada.</p><div class="bib-actions">@foreach($drafts as $draft)<a class="btn btn-outline-primary" href="{{ route('biblioteca.edit',$draft['id']) }}">Continuar borrador · versión {{ $draft['number'] }}</a>@endforeach</div></div>@endif
@endif
@include('biblioteca.reading')
@if($canManage && $findings->count())<details class="bib-section bib-details"><summary>Hallazgos de esta lectura ({{ $findings->count() }})</summary><div class="bib-reading">@foreach($findings as $f)@php($finding=\App\Services\Biblioteca\Content::decode($f->payload_json))<div class="bib-finding"><strong>{{ $finding['title'] }}</strong><span class="bib-badge ms-2">{{ $f->status }}</span><p>{{ $finding['detail'] }}</p>@if($f->resolution)<small>{{ $f->resolution }}</small>@endif</div>@endforeach</div></details>@endif
<details class="bib-section bib-details" id="bib-version-history"><summary>Versiones e historial ({{ count($history) }})</summary><div class="bib-reading"><div class="bib-table-wrap"><table class="table"><thead><tr><th>Versión</th><th>Estado</th><th>Última actualización</th>@if($canManage)<th>Autor y cambios</th>@endif<th>Acción</th></tr></thead><tbody>
@foreach($history as $v)<tr><td><strong>{{ $v['number'] }}</strong>@if($v['based_on'])@php($baseVersion=collect($history)->firstWhere('id',$v['based_on']))<small class="d-block">Basada en {{ $baseVersion['number']??'versión anterior' }}</small>@endif</td><td>{{ $v['state'] }}@if($v['id']===$entry['version']['id'])<small class="d-block">En pantalla</small>@endif</td><td>{{ \Carbon\CarbonImmutable::parse($v['updated_at'])->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }}@if($v['published_at'])<small class="d-block">Publicada: {{ \Carbon\CarbonImmutable::parse($v['published_at'])->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }}</small>@endif</td>@if($canManage)<td>{{ $v['created_by'] }}<small class="d-block">{{ $v['change_reason']?:'Sin motivo registrado' }}</small></td>@endif<td><div class="bib-actions"><a href="{{ route('biblioteca.show',['document'=>$entry['document']['id'],'version'=>$v['id']]) }}">Consultar</a>@if($canManage)<a href="{{ route('biblioteca.edit',$v['id']) }}">{{ $v['state']==='Borrador'&&!$v['published_at']?'Continuar borrador':'Editar como nueva versión' }}</a>@endif</div></td></tr>@endforeach
</tbody></table></div>
@if(!$entry['job']&&!empty($entry['content']['history']))<h4>Antecedentes declarados en el documento</h4><ul>@foreach($entry['content']['history'] as $h)<li>{{ $h['version'] }} · {{ $h['date']?:'Sin fecha' }} · {{ $h['detail'] }}</li>@endforeach</ul>@endif</div></details>
@if($canManage && $sources->count())<details class="bib-section bib-details"><summary>Documentos fuente y extracción ({{ $sources->count() }})</summary><div class="bib-reading">@foreach($sources as $s)<div class="bib-source"><div><strong>{{ $s->name }}</strong><small class="d-block">{{ $s->format }} · {{ $s->read_state }}</small></div><div class="bib-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('biblioteca.source',$s->id) }}">Ver fuente</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('biblioteca.source.file',$s->id) }}">Descargar original</a></div></div>@endforeach</div></details>@endif
@if($canManage)<details class="bib-section bib-details"><summary>Registro de acciones ({{ count($events) }})</summary><div class="bib-reading bib-event-list">@forelse($events as $event)<details class="bib-event"><summary>{{ substr($event['happened_at'],0,19) }} · {{ str_replace('_',' ',$event['action']) }} · {{ $event['actor'] }}</summary><div class="bib-event-data"><strong>Antes</strong><pre>{{ json_encode(\App\Services\Biblioteca\Content::decode($event['before_json']), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre><strong>Después</strong><pre>{{ json_encode(\App\Services\Biblioteca\Content::decode($event['after_json']), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div></details>@empty<p>Sin acciones registradas.</p>@endforelse</div></details>@endif
@endsection
