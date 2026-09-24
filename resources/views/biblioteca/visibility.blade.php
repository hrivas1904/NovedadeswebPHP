@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><h2>Aprobaciones y visibilidad</h2><a class="btn btn-outline-secondary" href="{{ route('biblioteca.manage') }}"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Volver</a></div>
<section class="bib-section"><h3>Políticas pendientes de aprobación</h3><div class="bib-reading"><p>Mariano Cardoner debe revisar y aprobar cada versión desde su ficha.</p>
<ul>@forelse($pending as $e)<li><a href="{{ route('biblioteca.show',['document'=>$e['document']['id'],'version'=>$e['version']['id']]) }}">{{ $e['title'] }} · Versión interna {{ $e['version']['number'] }}</a></li>@empty<li>No hay políticas pendientes de aprobación.</li>@endforelse</ul>
</div></section>
<p>Las secciones ocultas no aparecen en el índice ni en el menú. Los administradores conservan el acceso desde Administración.</p>
<section class="bib-section"><h3>Secciones</h3><div class="bib-reading"><p class="text-muted small">Los cambios se guardan automáticamente al activar o desactivar el interruptor.</p><div class="bib-visibility-sections">
@foreach($collections as $kind=>$collection)
@php($key='section:'.$kind)
@include('biblioteca.visibility-row',['label'=>$collection['label'],'key'=>$key,'setting'=>$settings[$key]??null,'default'=>$kind!=='instructivos'])
@endforeach
</div></div></section>
<section class="bib-section"><h3>Visibilidad por documento</h3><div class="bib-reading">
<p>Mostrá u ocultá cada política, procedimiento o descriptivo de forma independiente. Los administradores conservan el acceso a los documentos ocultos.</p>
<p class="text-muted small">Mostrar una política no reemplaza su aprobación de Gerencia. Ocultar un descriptivo impide nuevas consultas y firmas; las constancias ya firmadas se conservan.</p>
<form method="get" action="{{ route('biblioteca.visibility') }}" class="bib-filters" data-live-filter="#visibility-results" data-live-feedback="#visibility-feedback">
<div><label for="visibility-q">Buscar documento</label><input id="visibility-q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nombre, código o área"></div>
<div><label for="visibility-kind">Colección</label><select id="visibility-kind" name="kind" class="form-select"><option value="">Todas</option>@foreach($collections as $kind=>$collection)<option value="{{ $kind }}" @selected(request('kind')===$kind)>{{ $collection['label'] }}</option>@endforeach</select></div>
@include('biblioteca.page-size',['prefix'=>'visibility'])
<button class="btn btn-primary" data-live-submit>Buscar</button>
</form>
<p id="visibility-feedback" role="status" aria-live="polite"></p>
<div id="visibility-results" aria-busy="false">@include('biblioteca.visibility-results')</div>
</div></section>
@endsection
