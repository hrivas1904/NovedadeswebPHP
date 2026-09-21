<form method="post" action="{{ route('biblioteca.visibility.save') }}" class="bib-filters">@csrf
<input type="hidden" name="key" value="{{ $key }}"><input type="hidden" name="revision" value="{{ $setting->revision??0 }}">
<div><label>{{ $label }}<select name="visible" class="form-select"><option value="1" @selected($setting->visible??$default)>Mostrar</option><option value="0" @selected(!($setting->visible??$default))>Ocultar</option></select></label></div><button class="btn btn-outline-primary">Guardar</button>
</form>
