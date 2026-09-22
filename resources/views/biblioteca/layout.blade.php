@extends('layouts.app')
@section('title', 'Biblioteca Institucional | HP3C')
@section('content')
<link rel="stylesheet" href="{{ asset('css/biblioteca.css') }}">
<div class="bib-app">
@php($preview=request()->attributes->get('biblioteca_preview'))
@if($preview)
<div class="alert alert-warning bib-preview-banner" role="status"><strong>Estás viendo como {{ $preview['target']->name }} ({{ $preview['target']->username }})</strong><p class="mb-2">Sólo consulta de la biblioteca · Tu cuenta: {{ $preview['actor']->username }}. No se permiten firmas ni cambios.</p><form method="post" action="{{ route('biblioteca.preview.stop') }}">@csrf<button class="btn btn-dark btn-sm">Volver a mi usuario</button> <a href="{{ route('biblioteca.preview') }}" class="btn btn-outline-dark btn-sm">Cambiar usuario</a></form></div>
@elseif(\App\Services\Biblioteca\UserPreview::allowed(auth()->user()))
<div class="bib-actions mb-3"><a class="btn btn-outline-primary" href="{{ route('biblioteca.preview') }}">Ver como otro usuario</a>@if(session('biblioteca_preview'))<form method="post" action="{{ route('biblioteca.preview.stop') }}">@csrf<button class="btn btn-outline-secondary">Finalizar consulta y volver a mi usuario</button></form>@endif</div>
@endif
@php($canManage=$canManage && !$preview)

    <header class="bib-header">
        <div><a class="bib-eyebrow" href="{{ route('biblioteca.index') }}">HOSPITAL PRIVADO TRES CERRITOS</a><h1>Biblioteca Institucional</h1><p>Documentos que acompañan nuestro trabajo.</p></div>
        @if($canManage)<a class="btn btn-outline-secondary btn-sm" href="{{ route('biblioteca.control') }}"><i aria-hidden="true" class="fa-solid fa-clipboard-check me-1"></i> Control documental</a>@endif
    </header>
    <nav class="bib-nav" aria-label="Secciones de la biblioteca">
        <a href="{{ route('biblioteca.index') }}" class="{{ request()->routeIs('biblioteca.index')?'active':'' }}">Índice general</a>
        @foreach($collections as $key=>$collection)@continue(!app(\App\Services\Biblioteca\Governance::class)->sectionVisible($key))<a style="--bib-color:{{ $collection['color'] }}" class="{{ (request()->route('kind')===$key || ($entry['document']['kind']??null)===$key)?'active':'' }}" href="{{ route('biblioteca.catalog',$key) }}"><i aria-hidden="true" class="fa-solid {{ $collection['icon'] }}"></i> {{ $collection['label'] }}</a>@endforeach
        <a href="{{ route('biblioteca.mine') }}" class="{{ request()->routeIs('biblioteca.mine','biblioteca.receipt')?'active':'' }}">Mi descriptivo</a>
        @if($canManage)<a href="{{ route('biblioteca.coverage') }}" class="{{ request()->routeIs('biblioteca.coverage','biblioteca.acceptance.register')?'active':'' }}">Descriptivos y firmas</a>@endif
        @if($canManage)<a href="{{ route('biblioteca.manage') }}" class="{{ request()->routeIs('biblioteca.manage')?'active':'' }}"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i> Administración</a>@endif
    </nav>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="bib-alert alert d-none" role="alert" tabindex="-1"></div>
    @yield('library')
</div>
@endsection
@push('scripts')<script src="{{ asset('js/biblioteca.js') }}"></script>@endpush
