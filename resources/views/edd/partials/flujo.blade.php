<section class="edd-panel mt-4" aria-label="Estados del proceso">
    <h2 class="h6 mb-1">Recorrido previsto para esta primera versión</h2>
    <ol class="edd-flow">
        @foreach(['Autoevaluación' => 'El colaborador califica sus competencias y envía su autoevaluación.',
            'Evaluación del responsable' => 'El evaluador asignado recibe la autoevaluación y califica las mismas competencias.',
            'Cierre' => 'El responsable finaliza la evaluación.'] as $etapa => $descripcion)
            <li>
                <span class="edd-step">{{ $loop->iteration }}</span>
                <strong>{{ $etapa }}</strong>
                <p>{{ $descripcion }}</p>
            </li>
        @endforeach
    </ol>
</section>
