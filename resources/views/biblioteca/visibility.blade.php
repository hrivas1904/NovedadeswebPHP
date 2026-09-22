@extends('biblioteca.layout')
@section('library')
<h2>Aprobaciones y visibilidad</h2>
<section class="bib-section"><h3>Políticas pendientes de aprobación</h3><div class="bib-reading"><p>Mariano Cardoner debe revisar y aprobar cada versión desde su ficha.</p>
<ul>@forelse($pending as $e)<li><a href="{{ route('biblioteca.show',['document'=>$e['document']['id'],'version'=>$e['version']['id']]) }}">{{ $e['title'] }} · Versión interna {{ $e['version']['number'] }}</a></li>@empty<li>No hay políticas pendientes de aprobación.</li>@endforelse</ul>
</div></section>
<p>Las secciones ocultas no aparecen en el índice ni en el menú. Los administradores conservan el acceso desde Administración.</p>
<section class="bib-section"><h3>Secciones</h3><div class="bib-reading">
@foreach($collections as $kind=>$collection)
@php($key='section:'.$kind)
@include('biblioteca.visibility-row',['label'=>$collection['label'],'key'=>$key,'setting'=>$settings[$key]??null,'default'=>$kind!=='instructivos'])
@endforeach
</div></section>
<section class="bib-section"><h3>Políticas</h3><div class="bib-reading"><p>Mostrar una política no reemplaza su aprobación. Los colaboradores sólo pueden consultar la versión vigente aprobada por Gerencia.</p>
@foreach($policies as $e)
@php($key='document:'.$e['document']['id'])
@include('biblioteca.visibility-row',['label'=>$e['title'],'key'=>$key,'setting'=>$settings[$key]??null,'default'=>true])
@endforeach
</div></section>
@endsection
