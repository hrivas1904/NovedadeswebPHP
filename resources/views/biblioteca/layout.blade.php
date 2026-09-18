@extends('layouts.app')
@section('title', 'Biblioteca Institucional | HP3C')
@section('content')
<link rel="stylesheet" href="{{ asset('css/biblioteca.css') }}">
<div class="bib-app">
    <header class="bib-header">
        <div><a class="bib-eyebrow" href="{{ route('biblioteca.index') }}">HOSPITAL PRIVADO TRES CERRITOS</a><h1>Biblioteca Institucional</h1><p>Documentos que acompañan nuestro trabajo.</p></div>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('biblioteca.control') }}"><i aria-hidden="true" class="fa-solid fa-clipboard-check me-1"></i> Control documental</a>
    </header>
    <nav class="bib-nav" aria-label="Secciones de la biblioteca">
        <a href="{{ route('biblioteca.index') }}" class="{{ request()->routeIs('biblioteca.index')?'active':'' }}">Índice general</a>
        @foreach($collections as $key=>$collection)<a style="--bib-color:{{ $collection['color'] }}" class="{{ (request()->route('kind')===$key || ($entry['document']['kind']??null)===$key)?'active':'' }}" href="{{ route('biblioteca.catalog',$key) }}"><i aria-hidden="true" class="fa-solid {{ $collection['icon'] }}"></i> {{ $collection['label'] }}</a>@endforeach
        @if($canManage)<a href="{{ route('biblioteca.manage') }}" class="{{ request()->routeIs('biblioteca.manage')?'active':'' }}"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i> Administración</a>@endif
    </nav>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="bib-alert alert d-none" role="alert" tabindex="-1"></div>
    @yield('library')
</div>
@endsection
@push('scripts')<script src="{{ asset('js/biblioteca.js') }}"></script>@endpush
