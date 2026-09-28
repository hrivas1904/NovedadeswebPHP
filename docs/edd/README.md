# Evaluación de Desempeño HP3C

Módulo para EDD 2026, con escala **1 a 4** confirmada por el usuario. Períodos, instrumentos, población, competencias funcionales por área/persona y evaluadores tienen guardado real. El envío de autoevaluaciones y las respuestas permanecen pendientes.

**Incremento vigente:** [Población, evaluadores y competencias funcionales](06-poblacion-y-competencias.md). Incluye las ocho listas compartidas por RRHH y la personalización sin descriptivo de puesto. Hay 22 rutas (12 GET y 10 escrituras) y 33 pruebas correctas con 443 verificaciones. Mi equipo muestra las asignaciones reales del responsable.

**Alcance prioritario de la primera versión:** [circuito funcional sencillo](05-alcance-primera-version.md). Competencias genéricas y específicas, áreas, colaboradores y evaluadores definidos por RRHH; autoevaluación enviada al responsable asignado; evaluación de la jefatura y cierre. Esta definición posterior del usuario simplifica el desarrollo previsto en las etapas siguientes.

| Etapa | Documento | Entrega |
| --- | --- | --- |
| 1 | [Modelo HP3C](01-modelo-hp3c.md) | Criterios, instrumento, escala, estados e indicadores. |
| 2 | [Diseño funcional](02-diseno-funcional.md) | Pantallas, permisos y configuración persistente de períodos e instrumentos. |
| 3 | [Modelo de información](03-modelo-informacion.md) | Diseño de entidades, relaciones, integridad e historial. |
| 4 | [Especificación de desarrollo](04-especificacion-desarrollo.md) | Encargo, operaciones y criterios de aceptación de la implementación posterior. |

## Acceso

Recursos Humanos → Colaboradores → **Evaluación de desempeño**, o `/rrhh/edd`.

- Administrador/a: resumen, configuración, equipo, autoevaluación y reportes.
- Coordinador/a y Coordinador/a L2: ingreso directo a Mi equipo y acceso a su autoevaluación.
- Otros perfiles activos: su autoevaluación.

Las páginas de configuración muestran **En configuración** cuando las tablas están instaladas. RRHH puede agregar participantes, asignar responsables y guardar competencias. Mi equipo muestra sus asignaciones vigentes, todavía en preparación. Evaluación, autoevaluación y reportes siguen pendientes de carga de respuestas y resultados.

## Ubicación del código

```text
app/Http/Controllers/RRHH/EddController.php
app/Http/Requests/Edd/                       # Validación de período e instrumento
app/Services/Edd/ConfiguracionEdd.php        # Persistencia, versiones y auditoría
app/Services/Edd/PoblacionEdd.php            # Participantes, asignaciones y competencias por área/persona
app/Enums/Edd/EstadoEvaluacion.php
app/Providers/AppServiceProvider.php          # Gates EDD
config/edd.php                               # Referencia 2026 y escala 1–4
config/edd_competencias.php                   # 40 competencias funcionales compartidas por RRHH
routes/rrhh.php                              # Rutas autenticadas rrhh.edd.*
resources/views/edd/
  index.blade.php                            # Resumen RRHH
  layout.blade.php
  configuracion/                            # Período, población, evaluadores e instrumento
  equipo/
  evaluaciones/
  autoevaluacion/
  devolucion/
  cierre/
  reportes/
  partials/
public/js/edd/edd.js                         # Filtros de la estructura de equipo
public/js/edd/configuracion.js               # Guardado, criterios y conflictos
public/js/edd/poblacion.js                   # Selección de bases y personalización
database/migrations/2026_09_25_170000_create_edd_configuration_tables.php
database/migrations/2026_09_26_100000_create_edd_population_tables.php
tests/Feature/EddStructureTest.php
tests/Feature/EddConfigurationTest.php
tests/Feature/EddPopulationTest.php
```

## Alcance implementado

Doce rutas GET y diez escrituras. A la configuración de períodos e instrumentos se agregan población, responsables y competencias por área/persona. El editor inicial de instrumentos conserva su publicación con ponderaciones; el panel funcional del alcance simple permite preparar las listas sin ese requisito. La escala sigue siendo 1–4.

Cada operación exige una cuenta activa con permiso administrativo. El servicio guarda cambios y auditoría en la misma transacción; una revisión desactualizada devuelve 409 sin sobrescribir. La interfaz conserva lo escrito ante errores o conflictos y confirma éxito únicamente después de persistir. Las versiones publicadas quedan protegidas.

Población y asignaciones ya son persistentes; las competencias funcionales se heredan por área o se personalizan por participante/período. Quedan pendientes las competencias genéricas definitivas, apertura, respuestas, cierre y reportes. Entrevistas y planes de acción son ampliaciones. Las condiciones de cierre del editor inicial aún son definiciones textuales. La etapa 3 describe también entidades futuras y la etapa 4 mantiene el encargo restante.

El detalle para revisar este incremento está en [Revisión de la etapa 2](REVISION_ETAPA_2.md).

## Validación realizada

```text
php artisan route:list --path=rrhh/edd -v
php artisan test --compact --filter=Edd
node --check public/js/edd/edd.js
node --check public/js/edd/configuracion.js
node --check public/js/edd/poblacion.js
git diff --check
```

Resultado actualizado: 33 pruebas correctas (443 verificaciones) en SQLite aislado. Se comprobaron períodos/instrumentos, población, asignaciones, personalización, herencia, exclusiones y alcance de Mi equipo. Formularios revisados en navegador y móvil; PHP formateado con Pint y JavaScript comprobado por sintaxis. Ver el detalle del nuevo incremento en su guía.

## Trabajo por etapas

Se conserva la rama `FRAN240926`, con un commit propio por etapa y sin merge en `main`. Antes de cada nuevo commit se informa el detalle de los cambios y se espera el OK explícito del usuario. Hasta entonces, los cambios quedan sin commit y sin preparar en el índice, disponibles para revisión en Control de código fuente de VS Code. Los commits anteriores se conservan.

Las decisiones de implementación posteriores y su validación deben actualizar estos documentos.
