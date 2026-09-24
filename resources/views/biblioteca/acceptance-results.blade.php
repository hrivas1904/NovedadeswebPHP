<p class="bib-result-count">{{ $records->total() }} aceptaciones encontradas</p>
<div class="bib-table-wrap mt-3"><table class="table bib-table"><thead><tr>@foreach(['employee_name'=>'Colaborador','category_name'=>'Categoría al firmar','version_number'=>'Versión','accepted_at'=>'Fecha y hora'] as $key=>$label)
@include('biblioteca.sort-heading',['key'=>$key,'label'=>$label,'paginator'=>$records,'defaultSort'=>'accepted_at','defaultDirection'=>'desc'])
@endforeach<th>Constancia</th></tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->employee_name }} · {{ $record->legajo }}</td><td>{{ $record->category_name }}</td><td>{{ $record->version_number }}</td><td>{{ \Carbon\CarbonImmutable::parse($record->accepted_at)->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }}</td><td><a href="{{ route('biblioteca.receipt',$record->id) }}">Ver contenido aceptado</a></td></tr>@empty<tr><td colspan="5">Todavía no hay aceptaciones registradas.</td></tr>@endforelse
</tbody></table></div>@include('biblioteca.simple-pagination',['paginator'=>$records])
