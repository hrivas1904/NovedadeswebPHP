# Evaluación de Desempeño HP3C

Estructura inicial para EDD 2026, con escala **1 a 4** confirmada por el usuario.

| Etapa | Documento | Entrega |
| --- | --- | --- |
| 1 | [Modelo HP3C](01-modelo-hp3c.md) | Criterios, instrumento, escala, estados e indicadores. |
| 2 | [Diseño funcional](02-diseno-funcional.md) | Pantallas navegables y permisos de acceso. |
| 3 | [Modelo de información](03-modelo-informacion.md) | Diseño de entidades, relaciones, integridad e historial. |
| 4 | [Especificación de desarrollo](04-especificacion-desarrollo.md) | Encargo, operaciones y criterios de aceptación de la implementación posterior. |

## Acceso

Recursos Humanos → Colaboradores → **Evaluación de desempeño**, o `/rrhh/edd`.

- Administrador/a: resumen, configuración, equipo, autoevaluación y reportes.
- Coordinador/a y Coordinador/a L2: ingreso directo a Mi equipo y acceso a su autoevaluación.
- Otros perfiles activos: su autoevaluación.

Las páginas identifican el módulo como **Diseño inicial**. Los modelos de formulario están deshabilitados y los indicadores quedan sin datos hasta implementar la carga real.

## Ubicación del código

```text
app/Http/Controllers/RRHH/EddController.php
app/Enums/Edd/EstadoEvaluacion.php
app/Providers/AppServiceProvider.php          # Gates EDD
config/edd.php                               # Referencia 2026 y escala 1–4
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
public/js/edd/edd.js
tests/Feature/EddStructureTest.php
```

## Alcance implementado

Diez rutas GET, vistas, navegación según perfil, permisos en servidor, estados centralizados y filtro local preparado para las filas autorizadas del equipo. Se continuó sobre el esqueleto EDD que ya existía en el directorio de trabajo.

Quedan para la siguiente implementación: migraciones/modelos, apertura de períodos, población y asignaciones persistentes, guardado de respuestas, transiciones, entrevistas reales, cierres, cálculos y reportes. La etapa 3 entrega el diseño de datos; no ejecuta migraciones. La etapa 4 entrega la especificación; no ejecuta ese encargo automáticamente.

## Validación realizada

```text
php artisan route:list --path=rrhh/edd -v
php artisan test --compact --filter=EddStructureTest
node --check public/js/edd/edd.js
git diff --check
```

Resultado: diez rutas con autenticación/autorización y siete pruebas correctas (137 verificaciones) con SQLite en memoria, sin tablas institucionales. Revisión visual en navegador de resumen, configuración, instrumento móvil e ingreso de jefatura. Los PHP editados se comprobaron por sintaxis y se formatearon con Pint.

## Trabajo por etapas

Se conserva la rama `FRAN240926`, con un commit propio por etapa y sin merge en `main`. Las decisiones de implementación posteriores y su validación deben actualizar estos documentos.
