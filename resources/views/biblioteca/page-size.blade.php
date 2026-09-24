<div class="bib-page-size">
<label for="{{ $prefix }}-per-page">Mostrar por página</label>
<select id="{{ $prefix }}-per-page" name="per_page" class="form-select">
@foreach([25,50,100,150] as $size)<option value="{{ $size }}" @selected(\App\Services\Biblioteca\Listing::perPage(request())===$size)>{{ $size }}</option>@endforeach
</select>
<input type="hidden" name="sort" value="{{ request('sort',$defaultSort??'title') }}">
<input type="hidden" name="direction" value="{{ request('direction',$defaultDirection??'asc') }}">
</div>
