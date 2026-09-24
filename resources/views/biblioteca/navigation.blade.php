    <nav class="bib-nav" aria-label="Secciones de la biblioteca">
        <a href="{{ route('biblioteca.index') }}" class="{{ request()->routeIs('biblioteca.index')?'active':'' }}">Índice general</a>
        @foreach($collections as $key=>$collection)@continue(!app(\App\Services\Biblioteca\Governance::class)->sectionVisible($key))<a style="--bib-color:{{ $collection['color'] }}" class="{{ $activeCollection===$key?'active':'' }}" href="{{ route('biblioteca.catalog',$key) }}"><i aria-hidden="true" class="fa-solid {{ $collection['icon'] }}"></i> {{ $collection['label'] }}</a>@endforeach
        <a href="{{ route('biblioteca.mine') }}" class="{{ request()->routeIs('biblioteca.mine','biblioteca.receipt')?'active':'' }}">Mi descriptivo</a>
        @if($canManage)<a href="{{ route('biblioteca.coverage') }}" class="{{ request()->routeIs('biblioteca.coverage','biblioteca.acceptance.register')?'active':'' }}">Descriptivos y firmas</a>@endif
        @if($canManage)<a href="{{ route('biblioteca.manage') }}" class="{{ $administrationActive?'active':'' }}"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i> Administración</a>@endif
    </nav>
