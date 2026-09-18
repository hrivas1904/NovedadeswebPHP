@extends('biblioteca.layout')
@section('library')
<h2>Sincronizar documentos fuente</h2><p>Revisa las 15 carpetas de origen. Los archivos nuevos o modificados se incorporan como lecturas pendientes y conservan las versiones anteriores.</p>
<div class="bib-callout"><div><strong>Las publicaciones se administran desde la biblioteca.</strong><p>Una modificación o retiro de un archivo fuente no reemplaza ni retira la versión publicada. La sincronización conserva los originales.</p></div></div>
@if(!$configured)<div class="alert alert-warning">La carpeta de origen todavía no está configurada en este servidor.</div>@endif
<form method="post" action="{{ route('biblioteca.sync.run') }}">@csrf<button class="btn btn-primary" @disabled(!$configured)>Sincronizar carpetas</button><a class="btn btn-outline-secondary" href="{{ route('biblioteca.manage') }}">Volver a Administración</a></form>
@if(session('sync_report'))@php($report=session('sync_report'))<section class="bib-section mt-4"><h3>Resultado</h3><div class="bib-reading"><dl class="bib-metadata">@foreach(['analyzed'=>'Analizados','unchanged'=>'Sin cambios','new'=>'Nuevos','modified'=>'Modificados','removed'=>'Fuentes retiradas','restored'=>'Restauradas'] as $key=>$label)<div><dt>{{ $label }}</dt><dd>{{ $report[$key] }}</dd></div>@endforeach</dl>@if($report['errors'])<div class="alert alert-warning"><ul>@foreach($report['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul><a href="{{ route('biblioteca.upload') }}">Revisar cargas pendientes</a></div>@endif</div></section>@endif
@endsection
