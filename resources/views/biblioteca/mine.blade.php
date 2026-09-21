@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Mi descriptivo de puesto</h2><p>Leé tus funciones y registrá tu aceptación de la versión vigente.</p></div></div>
@if($context['employee'])<p><strong>{{ $context['employee']->COLABORADOR }}</strong> · Legajo {{ $context['employee']->LEGAJO }} · {{ $context['category']?->NOMBRE ?? 'Sin categoría' }}</p>@endif
@if(!$context['entry'])
<div class="alert alert-info" role="status">{{ $context['status'] }}</div>
@else
@php($entry=$context['entry'])
<section class="bib-section"><div class="bib-reading"><h3 class="h5">{{ $entry['title'] }}</h3><p>Versión {{ $entry['version']['number'] }} · Publicada el {{ \Carbon\CarbonImmutable::parse($entry['version']['published_at'])->setTimezone(config('biblioteca.timezone'))->format('d/m/Y') }} · Asignación {{ $context['origin']==='individual'?'individual':'por categoría' }}</p>
@if($context['acceptance'])<div class="alert alert-success mb-0" role="status">Ya firmaste esta versión el {{ \Carbon\CarbonImmutable::parse($context['acceptance']->accepted_at)->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }}. <a href="{{ route('biblioteca.receipt',$context['acceptance']->id) }}">Ver constancia</a></div>
@elseif($context['canSign'])<div class="alert alert-warning mb-0" role="status">Tenés una aceptación pendiente. Cada nueva versión publicada requiere volver a leer y firmar el descriptivo.</div>
@else<div class="alert alert-warning mb-0">{{ $context['status'] }}</div>@endif
</div></section>
@include('biblioteca.reading')
@if($context['canSign'] && !request()->attributes->has('biblioteca_preview'))
<section class="bib-section"><h3>Aceptación del descriptivo</h3><form method="post" action="{{ route('biblioteca.sign') }}" class="bib-reading" id="bib-sign-form">
@csrf
<input type="hidden" name="version_id" value="{{ $entry['version']['id'] }}">
<input type="hidden" name="assignment_token" value="{{ $context['token'] }}">
<input type="hidden" name="content_hash" value="{{ $context['hash'] }}">
<label class="bib-check"><input class="form-check-input flex-shrink-0" type="checkbox" name="confirmed" value="1" required> <span>{{ \App\Services\Biblioteca\Acceptances::STATEMENT }}</span></label>
<p class="text-muted small">Se registrarán tu usuario, legajo, fecha y hora, y una copia del contenido aceptado. Una publicación posterior tendrá su propia aceptación.</p>
<button type="submit" class="btn btn-primary">Firmar aceptación de la versión {{ $entry['version']['number'] }}</button>
</form></section>
@endif
@endif
<details class="bib-section bib-details"><summary>Mis aceptaciones anteriores ({{ $history->total() }})</summary><div class="bib-reading">
@forelse($history as $a)<p>{{ \Carbon\CarbonImmutable::parse($a->accepted_at)->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }} · {{ $a->category_name }} · Versión {{ $a->version_number }} · <a href="{{ route('biblioteca.receipt',$a->id) }}">Ver contenido y constancia</a></p>@empty<p>Todavía no registraste aceptaciones.</p>@endforelse
@include('biblioteca.simple-pagination',['paginator'=>$history])
</div></details>
@endsection
