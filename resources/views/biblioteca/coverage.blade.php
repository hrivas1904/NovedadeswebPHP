@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Categorías, asignaciones y firmas</h2><p>Vinculá cada categoría con su descriptivo y revisá la aceptación de los colaboradores.</p></div><a class="btn btn-outline-primary" href="{{ route('biblioteca.acceptance.register') }}">Registro de aceptaciones</a></div>
<div class="bib-coverage-counts">
@foreach(['activeCategories'=>'Categorías activas','missing'=>'Sin descriptivo vinculado','unpublished'=>'Sin versión vigente','covered'=>'Categorías con vigente','employees'=>'Colaboradores activos','signed'=>'Con firma vigente','pending'=>'Pendientes de firma','withoutAccount'=>'Sin cuenta activa','ambiguousAccounts'=>'Legajos con varias cuentas'] as $key=>$label)<div><strong>{{ $counts[$key] }}</strong><span>{{ $label }}</span></div>@endforeach
</div>
@if($counts['withoutAccount'] || $counts['ambiguousAccounts'])<div class="alert alert-warning">Para firmar, cada colaborador necesita una única cuenta activa con su legajo correcto. Se detectaron {{ $counts['withoutAccount'] }} colaboradores sin cuenta activa y {{ $counts['ambiguousAccounts'] }} legajos con varias cuentas. <a href="{{ route('rrhh.administrarUsuarios') }}">Revisar usuarios</a></div>@endif
<p class="bib-result-count">«Sin descriptivo vinculado» requiere revisar si existe un documento adecuado o si falta confeccionarlo. Vincular un borrador no habilita la firma: primero debe publicarse una versión validada.</p>
<section class="bib-section"><h3>Cobertura de todas las categorías</h3><div class="bib-reading">
<p>El vínculo se confirma manualmente. Las categorías inactivas también se muestran para completar la revisión del catálogo.</p>
<div class="bib-table-wrap"><table class="table bib-table"><thead><tr><th>Categoría</th><th>Colaboradores activos</th><th>Cobertura</th><th>Descriptivo base</th></tr></thead><tbody>
@foreach($categories as $row)
@php($category=$row['category'])
<tr><td>{{ $category->NOMBRE }} @if(!(int)$category->estado)<span class="bib-badge">Inactiva</span>@endif</td><td>{{ $row['employees'] }}</td><td>{{ $row['status'] }}@if($row['document'])<a class="d-block" href="{{ route('biblioteca.show',$row['document']->id) }}">Consultar documento</a>@endif</td><td>
<form method="post" action="{{ route('biblioteca.assign',['scope'=>'category','id'=>$category->ID_CATEG]) }}" class="bib-assignment-form">@csrf
<input type="hidden" name="revision" value="{{ $row['mapping']?->revision ?? 0 }}">
<select name="document_id" class="form-select" aria-label="Descriptivo de {{ $category->NOMBRE }}"><option value="">Sin descriptivo vinculado</option>@foreach($documents as $d)<option value="{{ $d->id }}" @selected($row['mapping']?->document_id===$d->id)>{{ $d->canonical_name?:$d->title }} · {{ $d->normalized_area?:$d->area }}</option>@endforeach</select>
<button class="btn btn-outline-primary" type="submit">Guardar vínculo</button>
</form>
@if(!$row['document'])<a class="small d-inline-block mt-2" href="{{ route('biblioteca.new',['kind'=>'descriptivos']) }}">Crear un descriptivo si no existe</a>@endif
</td></tr>
@endforeach
</tbody></table></div></div></section>
<section class="bib-section"><h3>Colaboradores y aceptación de la versión vigente</h3><div class="bib-reading">
<p>La asignación individual reemplaza al descriptivo base de la categoría. Al seleccionar «Usar categoría» se restablece ese vínculo.</p>
<form method="get" action="{{ route('biblioteca.coverage') }}" class="bib-filters" id="coverage-filters"><div><label for="coverage-q">Buscar colaborador, legajo o categoría</label><input id="coverage-q" class="form-control" name="q" value="{{ $q }}"></div><div><label for="coverage-status">Situación</label><select id="coverage-status" class="form-select" name="status"><option value="">Todas</option>@foreach($statuses as $status)<option @selected($state===$status)>{{ $status }}</option>@endforeach</select></div><button class="btn btn-primary" data-coverage-search>Buscar</button></form>
<p id="coverage-feedback" class="small" role="status" aria-live="polite"></p>
<div id="coverage-results" aria-busy="false">@include('biblioteca.coverage-employees')</div>
</div></section>
@endsection
