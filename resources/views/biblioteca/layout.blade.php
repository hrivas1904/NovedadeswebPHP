@extends(request()->header('X-Biblioteca-Navigation') === '1' ? 'biblioteca.fragment' : 'layouts.app')
@section('title', 'Biblioteca Institucional | HP3C')
@section('content')
<link rel="stylesheet" href="{{ asset('css/biblioteca.css').'?v='.filemtime(public_path('css/biblioteca.css')) }}">
<div class="bib-app {{ request()->routeIs('biblioteca.index') ? 'bib-home' : '' }}" data-bib-base="{{ route('biblioteca.index') }}" data-bib-token="{{ csrf_token() }}">
@php($preview=request()->attributes->get('biblioteca_preview'))
<div id="bib-preview-context">
@if($preview)
<div class="alert alert-warning bib-preview-banner" role="status"><strong>Estás viendo como {{ $preview['target']->name }} ({{ $preview['target']->username }})</strong><p class="mb-2">Sólo consulta de la biblioteca · Tu cuenta: {{ $preview['actor']->username }}. No se permiten firmas ni cambios.</p><form method="post" action="{{ route('biblioteca.preview.stop') }}">@csrf<button class="btn btn-dark btn-sm">Volver a mi usuario</button> <a href="{{ route('biblioteca.preview') }}" class="btn btn-outline-dark btn-sm">Cambiar usuario</a></form></div>
@endif
</div>
@php($canManage=$canManage && !$preview)
@php($activeCollection = request()->routeIs('biblioteca.catalog') ? request()->route('kind') : (request()->routeIs('biblioteca.show','biblioteca.edit','biblioteca.review') ? ($entry['document']['kind']??null) : null))
@php($administrationActive = request()->routeIs('biblioteca.manage','biblioteca.new','biblioteca.upload*','biblioteca.visibility','biblioteca.sync'))

    <header class="bib-header">
        <div><a class="bib-eyebrow" href="{{ route('biblioteca.index') }}">HOSPITAL PRIVADO TRES CERRITOS</a><h1>Biblioteca Institucional</h1><p>Documentos que acompañan nuestro trabajo.</p></div>
        @if($canManage)
        <div class="bib-actions bib-header-tools">
            @if(\App\Services\Biblioteca\UserPreview::allowed(auth()->user()))
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('biblioteca.preview') }}"><i aria-hidden="true" class="fa-regular fa-eye me-1"></i> Ver como otro usuario</a>
                @if(session('biblioteca_preview'))
                    <form method="post" action="{{ route('biblioteca.preview.stop') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Finalizar consulta y volver a mi usuario</button></form>
                @endif
            @endif
            @if($canManage)<a class="btn btn-outline-secondary btn-sm" href="{{ route('biblioteca.control') }}"><i aria-hidden="true" class="fa-solid fa-clipboard-check me-1"></i> Control documental</a>@endif
        </div>
        @endif
    </header>
    @include('biblioteca.navigation')
    <div id="bib-content" aria-busy="false">
    <div id="bib-navigation-feedback" class="bib-navigation-feedback" role="status" aria-live="polite" hidden></div>
    <div id="bib-view" role="region" aria-label="Contenido de la biblioteca" tabindex="-1">
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="bib-alert alert d-none" role="alert" tabindex="-1"></div>
    @yield('library')
    </div>
    </div>
</div>
@endsection
@push('scripts')<script src="{{ asset('js/biblioteca.js').'?v='.filemtime(public_path('js/biblioteca.js')) }}"></script><script src="{{ asset('js/biblioteca-navigation.js').'?v='.filemtime(public_path('js/biblioteca-navigation.js')) }}"></script>@endpush
