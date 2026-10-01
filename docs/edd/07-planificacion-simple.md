# Planificación simple de EDD

Incremento del 28/09/2026. Cambios sin commit, sobre `FRAN240926`, para revisar en VS Code. La base anterior está en el commit `00eab19`.

## Criterios acordados

- Solamente competencias generales y específicas. Cada competencia se califica del **1 al 4**. No se agregan objetivos ni metas separados.
- Generales: una única lista compartida por todo el personal del período.
- Específicas: base por área o lista personalizada por colaborador, sin depender del módulo de descriptivos.
- Colaboradores, área, rol y servicio proceden de la nómina institucional existente. El rol laboral viene de `rol_empleados`; no se confunde con el perfil de acceso de `users`.
- Varios evaluadores pueden compartir un área y repartirse colaboradores. Se conserva un responsable vigente por colaborador. El acceso depende de esa asignación, no de pertenecer al área.
- Circuito previsto: autoevaluación enviada → evaluación del responsable → cierre. La carga de respuestas y el cierre todavía no están implementados.

## Cómo usarlo

1. **Período:** seleccionar el período creado.
2. **Población:** filtrar la nómina y agregar colaboradores. Se muestran área, rol y servicio; el botón de selección marca solamente la página visible.
3. **Generales:** usar la lista “Generales HP3C”, revisarla y guardar. Son las cinco competencias documentadas en el catálogo de autoevaluación 2025. No se cargan automáticamente en períodos reales.
4. **Específicas por área:** elegir un área real, tomar una lista de referencia o de la biblioteca, ajustar y guardar. Quienes usan la base del área siguen sus cambios durante la preparación.
5. **Biblioteca:** crear listas con nombre, por ejemplo “Administración · pagos”. Se guardan dentro del período y sirven para configurar áreas, grupos o personas.
6. **Específicas por grupo:** filtrar el área, tomar una lista, marcar colaboradores y aplicar. Reemplaza sus específicas por copias individuales; no altera las generales ni las listas de otros colaboradores. También permite volver a la base del área de cada seleccionado.
7. **Población → Configurar:** personalizar a una persona, cargarle una biblioteca o volver a su base de área. Se muestran sus generales comunes y la información actual de la nómina.
8. **Evaluadores → Configurar evaluadores por área:** registrar responsables. Se admite más de uno por área. Volver a “Asignar colaboradores”, marcar las personas que corresponden a cada responsable y guardar. Asignar también registra al evaluador en las áreas EDD de esas personas.

Las copias de una biblioteca son independientes: cambiar la biblioteca no modifica las copias ya aplicadas. Las generales, en cambio, son comunes para todos. Una descripción opcional después de dos puntos forma parte del texto de la competencia, no constituye un objetivo separado.

## Persistencia y protección

- `edd_listas_competencias`: bibliotecas y la única lista general de cada período. Código reservado `generales`; las bibliotecas llevan UUID. Revisión para impedir sobrescrituras desde pantallas desactualizadas.
- `edd_evaluador_areas`: inscripciones de responsables por período y área. Una inscripción no concede acceso a personas sin asignación. Para quitarla hay que reasignar primero sus colaboradores vigentes de esa área.
- Se conservan `edd_area_competencias`, `edd_participantes` y `edd_asignaciones`. El guardado masivo comprueba las revisiones de todos los seleccionados y revierte el lote completo ante un conflicto, una exclusión o un error de auditoría.
- Las escrituras exigen administrador activo, período en borrador, CSRF y auditoría transaccional. La pertenencia de los responsables previamente asignados se importa al instalar la migración, sin cambiar las asignaciones.
- Los participantes nuevos conservan nombre, área, rol y servicio al incorporarlos. Las pantallas también consultan rol y servicio actuales. Los participantes anteriores se muestran sin reescribir sus datos guardados.
- El editor previo de instrumentos se conserva por compatibilidad, fuera de la navegación principal. Sus bloques y ponderaciones no son un requisito para esta planificación simple. Las vistas previas muestran generales y específicas; no incorporan objetivos o planes como etapas obligatorias.

## Base de desarrollo

Se aplicó únicamente:

```text
php artisan migrate --path=database/migrations/2026_09_28_100000_create_edd_planning_tables.php
```

Destino comprobado: `desarrollo`. Comparación por hash antes/después: período, participante y asignación existentes sin cambios. La nueva tabla de listas quedó vacía; la inscripción del evaluador existente se conservó en la nueva tabla de áreas. No se ejecutaron migraciones ajenas ni se cargaron datos ficticios en esa base.

## Verificación y revisión en VSC

40 pruebas correctas, 582 verificaciones en SQLite aislado: listas generales, copias independientes, aplicación masiva, conflictos, permisos, exclusiones, pertenencia por área, acceso por asignación, importación de asignaciones existentes y reversión ante fallo de auditoría. Sintaxis JavaScript y formato PHP comprobados.

En navegador local con datos ficticios se verificó guardar generales, crear una biblioteca, aplicarla a dos personas, recuperar la copia individual y registrar un responsable por área. Las consultas de rol y servicio se comprobaron también en modo de solo lectura contra desarrollo.

Revisar en **Control de código fuente** (`Ctrl+Shift+G`). Archivos principales:

- `app/Services/Edd/PlanificacionEdd.php`: listas comunes, biblioteca y evaluadores por área.
- `app/Services/Edd/PoblacionEdd.php`: datos institucionales y aplicación masiva.
- `app/Http/Controllers/RRHH/EddController.php`, `GuardarPlanificacionRequest.php` y `routes/rrhh.php`: formularios, validación y operaciones.
- `resources/views/edd/configuracion/` y `public/js/edd/`: pantallas y guardado.
- La migración nueva y las pruebas `EddPopulationTest` / `EddStructureTest`.

Ningún cambio de este incremento está preparado ni confirmado en Git. Se espera el OK explícito del usuario antes del commit; no se hace merge en main.
