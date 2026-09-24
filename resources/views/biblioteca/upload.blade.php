@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Importar descriptivo</h2><p>La carga conserva el archivo original y permite revisar la propuesta antes de incorporarla.</p></div><a class="btn btn-outline-secondary" href="{{ route('biblioteca.manage') }}"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> Volver</a></div>
<form class="bib-section bib-reading" method="post" enctype="multipart/form-data" action="{{ route('biblioteca.upload.stage') }}">@csrf<label for="bib-file">Archivo Word o PDF (hasta 50 MB)</label><input class="form-control mb-3" type="file" id="bib-file" name="file" accept=".docx,.pdf" required><button class="btn btn-primary">Cargar y revisar</button></form>
@if($pending->count())<section class="bib-section"><h3>Cargas pendientes</h3><div class="bib-reading">@foreach($pending as $u)@php($data=\App\Services\Biblioteca\Content::decode($u->payload_json))<p><a href="{{ route('biblioteca.upload.review',$u->id) }}">{{ $data['name'] }}</a> · {{ substr($u->created_at,0,10) }}</p>@endforeach</div></section>@endif
@endsection
