<?php

return [
    'management_roles' => ['Administrador/a'],
    'storage' => storage_path('app/private/biblioteca'),
    'python' => env('BIBLIOTECA_PYTHON', 'python'),
    'source_root' => env('BIBLIOTECA_SOURCE_ROOT'),
    'timezone' => 'America/Argentina/Buenos_Aires',
    'collections' => [
        'politicas' => ['label' => 'Políticas', 'icon' => 'fa-shield-halved', 'color' => '#3766ad', 'description' => 'Criterios y lineamientos para las decisiones del Hospital.'],
        'procedimientos' => ['label' => 'Procedimientos', 'icon' => 'fa-diagram-project', 'color' => '#19877a', 'description' => 'Circuitos de trabajo, responsables y pasos de cada proceso.'],
        'instructivos' => ['label' => 'Instructivos', 'icon' => 'fa-list-check', 'color' => '#bd8024', 'description' => 'Guías prácticas para realizar tareas y utilizar herramientas.'],
        'descriptivos' => ['label' => 'Descriptivos de puesto', 'icon' => 'fa-users', 'color' => '#8063ac', 'description' => 'Propósito, funciones, competencias y relaciones de cada puesto.'],
    ],
    'groups' => ['purpose'=>'Propósito del puesto','tasks'=>'Principales tareas y responsabilidades','internal'=>'Relaciones internas','external'=>'Relaciones externas','education'=>'Formación','experience'=>'Experiencia','knowledge'=>'Conocimientos','generic'=>'Competencias genéricas','specific'=>'Competencias específicas','commitment'=>'Compromiso','contribution'=>'Contribución al Hospital'],
    'competencies' => [
        'Ética profesional: Demuestra altos estándares de conducta ética, actuando con integridad y transparencia en todas las acciones y decisiones, fomentando un entorno de confianza y respeto.',
        'Trabajo en equipo colaborativo: Participa activamente en la colaboración con las diferentes áreas, promoviendo la sinergia grupal, la alineación y el cumplimiento de los objetivos comunes.',
        'Compromiso con el propósito: Identifica los objetivos del Hospital, su vínculo entre el rol individual y la misión institucional.',
        'Orientación al paciente/cliente: Identifica y satisface proactivamente las necesidades y expectativas de los pacientes y clientes, manteniendo un enfoque adaptativo y flexible para ofrecer un servicio de calidad.',
        'Excelencia Operativa: Se compromete con la mejora continua en los procesos, garantizando la calidad y excelencia en cada tarea y manteniendo altos estándares de servicio. Promueve una cultura de seguridad en el trabajo, asegurando el uso adecuado de EPP (Equipos de Protección Personal) y el cumplimiento de normativas para proteger a todos los colaboradores y minimizar riesgos.',
    ],
];
