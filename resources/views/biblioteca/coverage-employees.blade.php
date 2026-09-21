<p class="bib-result-count">{{ $employees->total() }} colaboradores encontrados</p>
<div class="bib-table-wrap"><table class="table bib-table"><thead><tr><th>Colaborador</th><th>Categoría actual</th><th>Descriptivo aplicable</th><th>Aceptación</th><th>Asignación individual</th></tr></thead><tbody>
@forelse($employees as $row)
<tr><td><strong>{{ $row['employee']->COLABORADOR }}</strong><span class="d-block">Legajo {{ $row['employee']->LEGAJO }}</span>@if($row['accountCount']!==1)<small>{{ $row['accountCount'] }} cuentas activas vinculadas</small>@endif</td>
<td>{{ $row['category']?->NOMBRE ?? 'Sin categoría' }}</td>
<td>@if($row['document'])<a href="{{ route('biblioteca.show',$row['document']->id) }}">{{ $row['document']->canonical_name?:$row['document']->title }}</a><span class="d-block small">{{ $row['origin'] }}</span>@else Sin asignación @endif</td>
<td><span class="bib-badge {{ $row['status']==='Firmado'?'current':'' }}">{{ $row['status'] }}</span>@if($row['acceptance'])<a class="d-block" href="{{ route('biblioteca.receipt',$row['acceptance']->id) }}">Ver constancia</a>@endif</td>
<td><form method="post" action="{{ route('biblioteca.assign',['scope'=>'employee','id'=>$row['employee']->LEGAJO]) }}" class="bib-assignment-form">@csrf
<input type="hidden" name="revision" value="{{ $row['individual']?->revision ?? 0 }}"><select class="form-select" name="document_id" aria-label="Descriptivo individual del legajo {{ $row['employee']->LEGAJO }}"><option value="">Usar categoría</option>@foreach($documents as $d)<option value="{{ $d->id }}" @selected($row['individual']?->document_id===$d->id)>{{ $d->canonical_name?:$d->title }} · {{ $d->normalized_area?:$d->area }}</option>@endforeach</select><button class="btn btn-outline-primary" type="submit">Guardar asignación</button></form></td></tr>
@empty<tr><td colspan="5">No hay colaboradores con esos filtros.</td></tr>@endforelse
</tbody></table></div>@include('biblioteca.simple-pagination',['paginator'=>$employees])
