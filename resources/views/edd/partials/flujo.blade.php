<section class="edd-panel mt-4" aria-label="Estados del proceso">
    <h2 class="h6 mb-1">Recorrido de la evaluación</h2>
    <ol class="edd-flow">
        @foreach($estados as $estado)
            <li>
                <span class="edd-step">{{ $loop->iteration }}</span>
                <strong>{{ $estado->etiqueta() }}</strong>
                <p>{{ $estado->descripcion() }}</p>
            </li>
        @endforeach
    </ol>
</section>
