<?php

return [
    'escala' => [
        4 => ['label' => 'Supera las expectativas',   'concepto' => 'Va más allá de lo esperado de manera consistente, aportando valor y generando impacto positivo en su equipo y en la organización.'],
        3 => ['label' => 'Cumple con lo esperado',    'concepto' => 'Desempeño sólido y confiable. Alcanza los objetivos establecidos y aporta al equipo.'],
        2 => ['label' => 'En desarrollo',             'concepto' => 'Cumple parcialmente con lo esperado. Tiene oportunidades de mejora y necesita seguimiento para potenciar su desempeño.'],
        1 => ['label' => 'Por debajo de lo esperado', 'concepto' => 'No cumple con los objetivos ni con los estándares de la posición. Es necesario un plan de mejora y acompañamiento.'],
    ],

    'estados' => [
        'PENDIENTE'         => ['label' => 'Pendiente',                'class' => 'bg-secondary'],
        'AUTO_EN_CURSO'     => ['label' => 'Autoevaluación en curso',  'class' => 'bg-info text-dark'],
        'AUTO_ENVIADA'      => ['label' => 'Autoevaluación enviada',   'class' => 'bg-primary'],
        'SUPERIOR_EN_CURSO' => ['label' => 'Evaluación en curso',      'class' => 'bg-warning text-dark'],
        'PENDIENTE_FIRMA'   => ['label' => 'Pendiente de firma',       'class' => 'bg-orange'],
        'CERRADA'           => ['label' => 'Cerrada',                  'class' => 'bg-success'],
    ],
];