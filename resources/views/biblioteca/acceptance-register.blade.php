@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Registro de aceptaciones</h2><p>Constancias históricas. Consultá Categorías y firmas para saber quién aceptó la versión vigente.</p></div><a class="btn btn-outline-primary" href="{{ route('biblioteca.coverage') }}">Categorías y firmas</a></div>
<form method="get" action="{{ route('biblioteca.acceptance.register') }}" class="bib-filters" data-live-filter="#acceptance-results" data-live-feedback="#acceptance-feedback"><div><label for="acceptance-q">Colaborador o legajo</label><input class="form-control" id="acceptance-q" name="q" value="{{ $q }}"></div><button class="btn btn-primary" data-live-submit>Buscar</button></form>
<p id="acceptance-feedback" role="status" aria-live="polite"></p><div id="acceptance-results" aria-busy="false">@include('biblioteca.acceptance-results')</div>
@endsection