@php
 $active=request('sort',$defaultSort??'title')===$key;
 $direction=request('direction',$defaultDirection??'asc')==='desc'?'desc':'asc';
 $next=$active&&$direction==='asc'?'desc':'asc';
 $sortUrl=$paginator->path().'?'.http_build_query(array_merge(request()->query(),['page'=>1,'sort'=>$key,'direction'=>$next]));
@endphp
<th scope="col" aria-sort="{{ $active?($direction==='asc'?'ascending':'descending'):'none' }}">
<a class="bib-sort" data-bib-sort href="{{ $sortUrl }}" aria-label="Ordenar por {{ $label }} {{ $next==='asc'?'de menor a mayor':'de mayor a menor' }}"><span>{{ $label }}</span><span class="bib-sort-arrow" aria-hidden="true">{{ $active?($direction==='asc'?'↑':'↓'):'↕' }}</span></a>
</th>
