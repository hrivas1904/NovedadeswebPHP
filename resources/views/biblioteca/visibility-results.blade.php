<p class="bib-result-count">{{ $documents->total() }} documentos</p>
<div class="bib-table-wrap"><table class="table bib-table bib-visibility-table"><thead><tr>
@include('biblioteca.sort-heading',['key'=>'title','label'=>'Documento','paginator'=>$documents])
@include('biblioteca.sort-heading',['key'=>'area','label'=>'Área / responsable','paginator'=>$documents])
<th scope="col">Visibilidad para colaboradores</th></tr></thead><tbody>
@forelse($documents as $e)
@php($key='document:'.$e['document']['id'])
@php($setting=$settings[$key]??null)
<tr><td><span class="bib-type" style="--bib-color:{{ $collections[$e['document']['kind']]['color'] }}">{{ $collections[$e['document']['kind']]['label'] }}</span><a class="bib-document-link" href="{{ route('biblioteca.show',$e['document']['id']) }}">{{ $e['title'] }}</a>@if($e['document']['code'])<small class="d-block">{{ $e['document']['code'] }}</small>@endif</td>
<td>{{ $e['area']?:'Sin registrar' }}</td>
<td>@include('biblioteca.visibility-switch',['key'=>$key,'setting'=>$setting,'default'=>true,'label'=>$e['title']])</td></tr>
@empty<tr><td colspan="3" class="p-4 text-center">No hay documentos que coincidan con estos filtros.</td></tr>@endforelse
</tbody></table></div>
@include('biblioteca.simple-pagination',['paginator'=>$documents])
