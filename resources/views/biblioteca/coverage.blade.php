@extends('biblioteca.layout')
@section('library')
<div class="bib-page-heading"><div><h2>Descriptivos y firmas</h2><p>Asigná el descriptivo que corresponde al servicio y la función de cada colaborador.</p></div><a class="btn btn-outline-primary" href="{{ route('biblioteca.acceptance.register') }}">Registro de aceptaciones</a></div>
<div class="bib-coverage-counts">
@foreach(['employees'=>'Colaboradores activos','missing'=>'Sin descriptivo asignado','unpublished'=>'Pendientes de publicación','signed'=>'Con firma vigente','pending'=>'Pendientes de firma','withoutAccount'=>'Sin cuenta activa','ambiguousAccounts'=>'Legajos con varias cuentas'] as $key=>$label)<div><strong data-coverage-count="{{ $key }}">{{ $counts[$key] }}</strong><span>{{ $label }}</span></div>@endforeach
</div>
<div id="coverage-account-warning" class="alert alert-warning {{ !$counts['withoutAccount'] && !$counts['ambiguousAccounts'] ? 'd-none' : '' }}">Para firmar, cada colaborador necesita una única cuenta activa con su legajo correcto. Se detectaron <span data-coverage-count="withoutAccount">{{ $counts['withoutAccount'] }}</span> colaboradores sin cuenta activa y <span data-coverage-count="ambiguousAccounts">{{ $counts['ambiguousAccounts'] }}</span> legajos con varias cuentas. <a href="{{ route('rrhh.administrarUsuarios') }}">Revisar usuarios</a></div>
<p>El convenio y la categoría corresponden al encuadre de liquidación. El servicio y el rol orientan la elección del descriptivo de puesto. Las categorías actuales están pendientes de revisión.</p>
<p class="bib-result-count">Los descriptivos pueden incorporarse progresivamente. Asignar un borrador deja al colaborador pendiente de publicación; la firma se habilita cuando existe una versión vigente y validada.</p>
<section class="bib-section"><h3>Colaboradores y aceptación de la versión vigente</h3><div class="bib-reading">
<p>Seleccioná un descriptivo para guardarlo automáticamente. Cada cambio actualiza la aceptación requerida y conserva las constancias anteriores.</p>
<form method="get" action="{{ route('biblioteca.coverage') }}" class="bib-filters" id="coverage-filters"><div><label for="coverage-q">Buscar por nombre, legajo, categoría, convenio, servicio o rol</label><input id="coverage-q" class="form-control" name="q" value="{{ $q }}"></div><div><label for="coverage-status">Situación</label><select id="coverage-status" class="form-select" name="status"><option value="">Todas</option>@foreach($statuses as $status)<option @selected($state===$status)>{{ $status }}</option>@endforeach</select></div><button class="btn btn-primary" data-coverage-search>Buscar</button></form>
<noscript><p class="alert alert-warning">Activá JavaScript para asignar descriptivos con guardado automático.</p></noscript>
<p id="coverage-feedback" class="small" role="status" aria-live="polite"></p>
<div id="coverage-results" aria-busy="false">@include('biblioteca.coverage-employees')</div>
</div></section>
@endsection
