@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>{{ $source['name'] }}</h2><p>Documento fuente · {{ $source['format'] }} · {{ $source['read_state'] }}</p></div><div class="bib-actions"><a class="btn btn-outline-primary" href="{{ route('biblioteca.show',$document['id']) }}">Volver al documento</a><a class="btn btn-primary" href="{{ route('biblioteca.source.file',$source['id']) }}">Descargar original</a></div></div>
@if($source['format']==='PDF')<iframe class="bib-pdf" title="Documento PDF original" src="{{ route('biblioteca.source.file',['source'=>$source['id'],'inline'=>1]) }}"></iframe>
@elseif($parsed)<section class="bib-section"><h3>Vista interpretada del original</h3><div class="bib-reading">@include('biblioteca.blocks',['blocks'=>array_merge($parsed['headers']??[],$parsed['blocks']??[],$parsed['extras']??[]),'bullets'=>false])</div></section>
@else<div class="alert alert-info">Descargá el archivo para consultar su contenido original. La fuente HTML se conserva como archivo descargable.</div>@endif
<details class="bib-section bib-details"><summary>Extracción completa y trazabilidad</summary><div class="bib-reading"><p>SHA-256: <code class="text-break">{{ $source['sha256'] }}</code></p><pre class="bib-raw">{{ $parsed['rawText']??'Sin extracción textual' }}</pre><pre class="bib-raw">{{ json_encode(\App\Services\Biblioteca\Content::decode($source['raw_json']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div></details>
@endsection
