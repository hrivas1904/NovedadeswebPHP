@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Registro de aceptaciones</h2><p>Constancias históricas. Consultá Categorías y firmas para saber quién aceptó la versión vigente.</p></div><a class="btn btn-outline-primary" href="{{ route('biblioteca.coverage') }}">Categorías y firmas</a></div>
<form method="get" class="bib-filters"><div><label for="acceptance-q">Colaborador o legajo</label><input class="form-control" id="acceptance-q" name="q" value="{{ $q }}"></div><button class="btn btn-primary">Buscar</button></form>
<div class="bib-table-wrap mt-3"><table class="table bib-table"><thead><tr><th>Colaborador</th><th>Categoría al firmar</th><th>Versión</th><th>Fecha y hora</th><th>Constancia</th></tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->employee_name }} · {{ $record->legajo }}</td><td>{{ $record->category_name }}</td><td>{{ $record->version_number }}</td><td>{{ \Carbon\CarbonImmutable::parse($record->accepted_at)->setTimezone(config('biblioteca.timezone'))->format('d/m/Y H:i') }}</td><td><a href="{{ route('biblioteca.receipt',$record->id) }}">Ver contenido aceptado</a></td></tr>@empty<tr><td colspan="5">Todavía no hay aceptaciones registradas.</td></tr>@endforelse
</tbody></table></div>@include('biblioteca.simple-pagination',['paginator'=>$records])
@endsection
