<form method="post" action="{{ route('biblioteca.visibility.save') }}" class="bib-visibility-control" data-visibility-control data-state-url="{{ route('biblioteca.visibility.state',['key'=>$key]) }}" data-confirmed="{{ ($setting->visible??$default)?'1':'0' }}">@csrf
<input type="hidden" name="key" value="{{ $key }}"><input type="hidden" name="revision" value="{{ $setting->revision??0 }}"><input type="hidden" name="visible" value="0">
<label class="bib-visibility-switch">
<input type="checkbox" name="visible" value="1" role="switch" aria-label="Visibilidad de {{ $label }}" @checked($setting->visible??$default) data-visibility-toggle>
<span class="bib-switch-track" aria-hidden="true"></span><span data-visibility-label>{{ ($setting->visible??$default)?'Visible':'Oculto' }}</span>
</label>
<span class="bib-visibility-status" role="status" aria-live="polite" data-visibility-status></span>
<button type="button" class="bib-visibility-retry" data-visibility-retry hidden>Verificar estado</button>
<noscript><button class="btn btn-outline-primary" type="submit">Guardar</button></noscript>
</form>
