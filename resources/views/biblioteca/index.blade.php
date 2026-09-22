@extends('biblioteca.layout')
@section('library')
@if($signaturePending)<div class="bib-callout"><div><strong>Tu descriptivo de puesto</strong><p>Consultá las funciones de tu puesto y firmá la aceptación de la versión vigente.</p></div><a class="btn btn-primary" href="{{ route('biblioteca.mine') }}">Ver y firmar mi descriptivo</a></div>@endif
<section class="bib-intro"><div><span class="bib-eyebrow">UN PUNTO DE ENCUENTRO</span><h2>Encontrá lo que necesitás para trabajar.</h2><p>Consultá las políticas, los procesos y las responsabilidades del Hospital en un solo lugar.</p></div>
<form action="{{ route('biblioteca.search') }}" class="bib-search" data-live-filter="#index-results" data-live-feedback="#index-feedback"><label for="bib-q">Buscar en toda la biblioteca</label><div class="input-group"><input class="form-control" name="q" id="bib-q" placeholder="Puesto, tema, código o palabra clave"><button class="btn btn-primary" type="submit" data-live-submit><i aria-hidden="true" class="fa-solid fa-magnifying-glass"></i><span class="ms-2">Buscar</span></button></div><small>{{ count($entries) }} documentos disponibles</small></form></section>
<p id="index-feedback" role="status" aria-live="polite"></p><div id="index-results" aria-busy="false"></div><div class="bib-collections">
@foreach($collections as $key=>$collection)
@continue(!app(\App\Services\Biblioteca\Governance::class)->sectionVisible($key))
@php($items=array_values(array_filter($entries,fn($e)=>$e['document']['kind']===$key)))
<section class="bib-collection" style="--bib-color:{{ $collection['color'] }}">
<div class="bib-collection-heading"><i aria-hidden="true" class="fa-solid {{ $collection['icon'] }}"></i><span>{{ count($items) }} documentos</span></div>
<h2><a href="{{ route('biblioteca.catalog',$key) }}">{{ $collection['label'] }}</a></h2><p>{{ $collection['description'] }}</p>
<ul>@forelse(array_slice($items,0,3) as $e)<li><a href="{{ route('biblioteca.show',$e['document']['id']) }}">{{ $e['title'] }}</a></li>@empty<li class="text-muted">Esta colección está lista para incorporar documentos.</li>@endforelse</ul>
<a class="bib-collection-link" href="{{ route('biblioteca.catalog',$key) }}">Explorar colección <i aria-hidden="true" class="fa-solid fa-arrow-right"></i></a>
</section>
@endforeach
</div>
@if($canManage)<div class="bib-callout"><div><strong>Construimos una biblioteca actualizada.</strong><p>Creá borradores, revisá contenidos y publicá versiones desde Administración.</p></div><a class="btn btn-outline-primary" href="{{ route('biblioteca.manage') }}">Gestionar documentos</a></div>@endif
@endsection
