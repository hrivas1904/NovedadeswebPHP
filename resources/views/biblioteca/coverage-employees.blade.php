<p class="bib-result-count">{{ $employees->total() }} colaboradores encontrados</p>
<div class="bib-table-wrap"><table class="table bib-table"><thead><tr>@foreach(['employee'=>'Colaborador','category'=>'Convenio y categoría actual','service'=>'Servicio y rol','document'=>'Descriptivo aplicable','status'=>'Aceptación'] as $key=>$label)
@include('biblioteca.sort-heading',['key'=>$key,'label'=>$label,'paginator'=>$employees,'defaultSort'=>'employee'])
@endforeach<th>Asignar descriptivo</th></tr></thead><tbody>
@forelse($employees as $row)
<tr><td><strong>{{ $row['employee']->COLABORADOR }}</strong><span class="d-block">Legajo {{ $row['employee']->LEGAJO }}</span>@if($row['accountCount']!==1)<small>{{ $row['accountCount'] }} cuentas activas vinculadas</small>@endif</td>
<td>{{ trim($row['employee']->CONVENIO ?? '') ?: 'Convenio sin informar' }}<span class="d-block">{{ $row['category']?->NOMBRE ?? 'Sin categoría' }}</span></td>
<td>{{ $row['employee']->service_name ?: 'Servicio sin informar' }}<span class="d-block small">{{ $row['employee']->role_name ?: 'Rol sin informar' }}</span></td>
<td>@if($row['document'])<a href="{{ route('biblioteca.show',$row['document']->id) }}">{{ $row['document']->canonical_name?:$row['document']->title }}</a><span class="d-block small">{{ $row['origin'] }}</span>@else Sin asignación @endif</td>
<td><span class="bib-badge {{ $row['status']==='Firmado'?'current':'' }}">{{ $row['status'] }}</span>@if($row['acceptance'])<a class="d-block" href="{{ route('biblioteca.receipt',$row['acceptance']->id) }}">Ver constancia</a>@endif</td>
<td><form method="post" action="{{ route('biblioteca.assign',['scope'=>'employee','id'=>$row['employee']->LEGAJO]) }}" class="bib-assignment-form" data-assignment-legajo="{{ $row['employee']->LEGAJO }}">@csrf
<input type="hidden" name="revision" value="{{ $row['individual']?->revision ?? 0 }}"><select disabled class="form-select" name="document_id" aria-describedby="assignment-feedback-{{ $row['employee']->LEGAJO }}" aria-label="Descriptivo individual del legajo {{ $row['employee']->LEGAJO }}"><option value="">{{ $row['base']?->document_id ? 'Usar vínculo anterior de categoría' : 'Sin descriptivo asignado' }}</option>@foreach($documents as $d)<option value="{{ $d->id }}" @selected($row['individual']?->document_id===$d->id)>{{ $d->canonical_name?:$d->title }} · {{ $d->normalized_area?:$d->area }}</option>@endforeach</select><small id="assignment-feedback-{{ $row['employee']->LEGAJO }}" class="d-block" role="status" aria-live="polite" data-assignment-feedback>Se guarda al seleccionar.</small></form></td></tr>
@empty<tr><td colspan="6">No hay colaboradores con esos filtros.</td></tr>@endforelse
</tbody></table></div>@include('biblioteca.simple-pagination',['paginator'=>$employees])
