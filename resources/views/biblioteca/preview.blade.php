@extends('biblioteca.layout')
@section('library')
<h2>Ver como otro usuario</h2>
<p>Consultá la biblioteca con sus permisos, su descriptivo asignado y sus aceptaciones. No podrás firmar, aprobar ni guardar cambios en su nombre. El resto del sistema conserva tu cuenta original.</p>
<form method="post" action="{{ route('biblioteca.preview.start') }}" class="bib-reading">@csrf
<label for="preview-search">Buscar por nombre, usuario o legajo</label><input id="preview-search" type="search" class="form-control mb-3" autocomplete="off">
<label for="preview-user">Usuario a consultar</label><select id="preview-user" name="user_id" class="form-select" size="10" required>
@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} · {{ $u->username }} · Legajo {{ $u->legajo?:'sin vincular' }} · {{ $u->rol }}</option>@endforeach
</select><p id="preview-empty" class="d-none">No hay usuarios que coincidan.</p>
<button class="btn btn-primary mt-3">Ver biblioteca como este usuario</button>
</form>
@endsection
