@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Constancia de aceptación</h2><p>{{ $entry['title'] }} · Versión {{ $record->version_number }}</p></div><div class="bib-actions"><a class="btn btn-outline-secondary" href="{{ route('biblioteca.mine') }}">Mi descriptivo</a><button type="button" class="btn btn-outline-primary" data-bib-print>Imprimir constancia</button></div></div>
<section class="bib-section"><h3>Aceptación registrada</h3><div class="bib-reading">
<p><strong>{{ $record->employee_name }}</strong> · Legajo {{ $record->legajo }} · {{ $record->category_name }}</p>
<p>Usuario: {{ $record->user_name }} · Fecha y hora: {{ \Carbon\CarbonImmutable::parse($record->accepted_at)->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i:s') }} (Argentina)</p>
<blockquote>{{ $record->statement }}</blockquote>
<p class="small text-muted">Esta constancia conserva el contenido aceptado en esa fecha. No acredita la aceptación de versiones publicadas posteriormente.</p>
<p class="small mb-0">Identificador: {{ $record->id }}<br>Huella del contenido: <span style="overflow-wrap:anywhere">{{ $record->content_hash }}</span></p>
</div></section>
@include('biblioteca.reading')
@endsection
