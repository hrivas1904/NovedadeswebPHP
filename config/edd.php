<?php

return [
    // Referencia de diseño; no representa un período abierto en la base de datos.
    'institucion' => 'Hospital Privado Tres Cerritos',
    'periodo_referencia' => 'EDD 2026',

    'escala' => [
        1 => ['nombre' => 'Por debajo de lo esperado', 'descripcion' => 'No cumple con los objetivos ni con los estándares de la posición.'],
        2 => ['nombre' => 'En desarrollo', 'descripcion' => 'Cumple parcialmente con lo esperado y necesita acompañamiento.'],
        3 => ['nombre' => 'Cumple con lo esperado', 'descripcion' => 'Alcanza los objetivos establecidos con un desempeño sólido y confiable.'],
        4 => ['nombre' => 'Supera las expectativas', 'descripcion' => 'Va más allá de lo esperado de manera consistente y aporta valor al equipo.'],
    ],

    'bloques' => [
        'generales' => ['nombre' => 'Competencias generales', 'descripcion' => 'Competencias organizacionales comunes a todos los colaboradores.'],
        'especificas' => ['nombre' => 'Competencias específicas', 'descripcion' => 'Criterios vinculados al rol, servicio y descriptivo de puesto.'],
        'desempeno' => ['nombre' => 'Desempeño', 'descripcion' => 'Cumplimiento de tareas, calidad y resultados del puesto.'],
        'conductas' => ['nombre' => 'Conductas', 'descripcion' => 'Comportamientos observables y evidencias del período.'],
        'objetivos' => ['nombre' => 'Objetivos', 'descripcion' => 'Metas acordadas, indicadores y criterios de cumplimiento.'],
    ],
];
