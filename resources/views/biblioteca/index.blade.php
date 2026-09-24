@extends('biblioteca.layout')
@section('library')
@php
    $visibleCollections = array_filter($collections, fn($key) => app(\App\Services\Biblioteca\Governance::class)->sectionVisible($key), ARRAY_FILTER_USE_KEY);
@endphp
<form id="bib-home-search" class="bib-home-search" role="search" action="{{ route('biblioteca.search') }}" data-home-search data-live-filter="#index-results" data-live-feedback="#index-feedback">
    <input type="hidden" name="visible_sections" value="1">
    <section class="bib-home-hero" aria-labelledby="bib-home-title">
        <div class="bib-home-hero-top">
            <span class="bib-home-kicker"><i class="fa-solid fa-book-open" aria-hidden="true"></i> EL CONOCIMIENTO QUE NOS ACOMPAÑA</span>
            <span class="bib-home-total">{{ count($entries) }} {{ count($entries) === 1 ? 'documento disponible' : 'documentos disponibles' }}</span>
        </div>
        <h2 id="bib-home-title">¿Qué necesitás consultar?</h2>
        <p>Políticas, procesos y responsabilidades. Encontrá la información para tu día a día.</p>
        <label class="visually-hidden" for="bib-q">Buscar en toda la biblioteca</label>
        <div class="bib-home-searchbox">
            <i class="fa-solid fa-magnifying-glass bib-home-search-icon" aria-hidden="true"></i>
            <input type="search" name="q" id="bib-q" placeholder="Tema, puesto o código…" autocomplete="off" aria-controls="index-results">
            <button type="button" class="bib-home-clear" data-home-clear aria-label="Limpiar búsqueda" hidden><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            <button class="btn bib-home-submit" type="submit" data-live-submit>Buscar <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
        <fieldset class="bib-home-scopes">
            <legend class="visually-hidden">Buscar en una colección</legend>
            <label><input type="radio" name="kind" value="" checked><span>Toda la biblioteca</span></label>
            @foreach($visibleCollections as $key => $collection)
                <label><input type="radio" name="kind" value="{{ $key }}"><span><i class="fa-solid {{ $collection['icon'] }}" aria-hidden="true"></i> {{ $collection['label'] }}</span></label>
            @endforeach
        </fieldset>
    </section>
    <div class="bib-home-results-head" data-home-results hidden>
        <div><h2>Resultados de búsqueda</h2><p id="index-feedback" role="status" aria-live="polite"></p></div>
        <div class="bib-home-result-tools">
            @include('biblioteca.page-size', ['prefix' => 'index'])
            <button class="bib-home-text-button" type="button" data-home-clear>Volver a las colecciones <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </div>
</form>
<div id="index-results" class="bib-home-results" aria-busy="false" hidden></div>
<template id="index-empty">
    <div class="bib-home-empty">
        <span class="bib-home-empty-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
        <h3>No encontramos documentos</h3>
        <p>Probá con otro término o cambiá la colección en la que estás buscando.</p>
        <button type="button" class="btn btn-outline-secondary" data-home-clear>Limpiar búsqueda</button>
    </div>
</template>

<div data-home-browse>
    <section class="bib-home-browse" aria-labelledby="bib-home-collections-title">
        <div class="bib-home-section-heading">
            <div><h2 id="bib-home-collections-title">Explorá por colección</h2><p>Un acceso directo a cada tipo de documento.</p></div>
            <a class="bib-home-all" href="{{ route('biblioteca.search', ['visible_sections' => 1]) }}" data-home-all>Ver todos los documentos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="bib-home-collections">
            @foreach($visibleCollections as $key => $collection)
                @php
                    $count = count(array_filter($entries, fn($e) => $e['document']['kind'] === $key));
                @endphp
                <a class="bib-home-collection" href="{{ route('biblioteca.catalog', $key) }}" style="--bib-color:{{ $collection['color'] }}">
                    <div class="bib-home-card-top">
                        <span class="bib-home-card-icon"><i class="fa-solid {{ $collection['icon'] }}" aria-hidden="true"></i></span>
                        <span class="bib-home-card-count"><strong>{{ $count }}</strong> {{ $count === 1 ? 'documento' : 'documentos' }}</span>
                    </div>
                    <h3>{{ $collection['label'] }}</h3>
                    <p>{{ $collection['description'] }}</p>
                    <span class="bib-home-card-link">Explorar colección <span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span></span>
                </a>
            @endforeach
        </div>
    </section>
    <section class="bib-home-personal {{ $signaturePending ? 'bib-home-personal-pending' : '' }}" aria-labelledby="bib-home-personal-title">
        <span class="bib-home-personal-icon"><i class="fa-regular fa-id-card" aria-hidden="true"></i></span>
        <div>
            <div class="bib-home-personal-heading"><h2 id="bib-home-personal-title">Mi descriptivo de puesto</h2>@if($signaturePending)<span class="bib-home-pending-badge">Firma pendiente</span>@endif</div>
            <p>{{ $signaturePending ? 'Tu versión vigente está lista para consultar y firmar.' : 'Consultá las funciones, competencias y responsabilidades de tu puesto.' }}</p>
        </div>
        <a class="btn {{ $signaturePending ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('biblioteca.mine') }}">{{ $signaturePending ? 'Ver y firmar mi descriptivo' : 'Ir a mi descriptivo' }} <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i></a>
    </section>
    @if($canManage)
        <aside class="bib-home-management" aria-label="Gestión de la biblioteca">
            <span><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Administración de la biblioteca</span>
            <a href="{{ route('biblioteca.manage') }}">Gestionar documentos <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
        </aside>
    @endif
</div>
@endsection
