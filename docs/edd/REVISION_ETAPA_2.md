# Revisión de la etapa 2: configuración y guardado

Esta revisión corresponde al primer incremento. El posterior de [población, evaluadores y competencias por área/persona](06-poblacion-y-competencias.md) ya reemplaza esos pendientes. Ambos siguen sin commit a la espera del OK explícito del usuario.

## Resultado de este incremento

RRHH puede crear y editar períodos en borrador, preparar instrumentos con criterios y ponderaciones, publicar una versión completa y crear la siguiente versión sin alterar la publicada. Los datos se recuperan al volver a la pantalla y se conserva la escala **1 a 4**.

El cambio queda sin commit y sin preparar en el índice en la rama `FRAN240926`. Se espera el OK explícito del usuario después de revisarlo en VS Code. No se hizo merge en `main`.

## Archivos para revisar

| Archivo o carpeta | Cambio |
| --- | --- |
| `app/Http/Controllers/RRHH/EddController.php` | Carga de períodos e instrumentos, selección por ID y seis acciones de escritura con respuesta de persistencia. |
| `app/Http/Requests/Edd/GuardarPeriodoRequest.php` | Código único, fechas compatibles, reglas de autoevaluación y revisión. |
| `app/Http/Requests/Edd/GuardarInstrumentoRequest.php` | Estructura de los cinco bloques, criterios, pesos y detección de formularios incompletos. |
| `app/Services/Edd/ConfiguracionEdd.php` | Transacciones, control de revisión, publicación, copia de versiones y auditoría. |
| `database/migrations/2026_09_25_170000_create_edd_configuration_tables.php` | Seis tablas nuevas, claves únicas y relaciones con borrado restringido. |
| `routes/rrhh.php` | Seis rutas de escritura con autenticación y permiso administrativo EDD. |
| `resources/views/edd/configuracion/` | Formularios reales de período e instrumento, selector, criterios dinámicos, publicación y mensajes de guardado. |
| `resources/views/edd/layout.blade.php` | Estado de configuración y alcance disponible. |
| `public/js/edd/configuracion.js` | Envío con CSRF, validación, conservación del texto ante errores, aviso de cambios pendientes y bloqueo mientras se guarda/publica. |
| `tests/Feature/EddConfigurationTest.php` | Pruebas de persistencia, permisos, integridad, versiones y conflictos. |
| `docs/edd/` | Alcance actualizado y continuidad de las etapas siguientes. |

En VS Code, abrir **Control de código fuente** con `Ctrl+Shift+G`. Los archivos modificados muestran su comparación con HEAD; los archivos nuevos aparecen como `U`. No hay cambios preparados para commit.

## Base de datos

El 25/09/2026 se ejecutó únicamente esta migración sobre la base configurada `desarrollo`, después de verificar `users.id` como `bigint unsigned`. Quedó registrada en el lote 7.

```text
php artisan migrate --path=database/migrations/2026_09_25_170000_create_edd_configuration_tables.php
```

Tablas creadas: `edd_periodos`, `edd_instrumentos`, `edd_bloques`, `edd_items`, `edd_periodo_instrumentos` y `edd_eventos`. La comprobación posterior confirmó las seis tablas vacías, sus once claves foráneas con `RESTRICT` y el almacenamiento disponible para el módulo. No se cargaron períodos, criterios ni resultados de ejemplo en esa base. No se ejecutaron otras migraciones pendientes ni se alteró la nómina.

Las pruebas automáticas y de navegador usaron SQLite aislado con datos ficticios. La reversión de la migración se probó solamente en esa base de pruebas; no se ejecutó contra `desarrollo`.

## Flujo disponible para probar

1. Ingresar con una cuenta activa de Administrador/a en **Recursos Humanos → Colaboradores → Evaluación de desempeño → Configuración**.
2. Crear un período con código, nombre y año. Las fechas y reglas pueden completarse después. Guardarlo lo mantiene en borrador.
3. Ir a **Instrumento**, indicar código y nombre y crear el borrador. Los cinco bloques comienzan sin criterios ni pesos predeterminados.
4. Cargar criterios y ponderaciones. Los pesos relativos distribuyen el puntaje dentro de cada bloque. Un bloque desactivado conserva sus criterios.
5. Guardar el borrador. Para publicar, los bloques activos necesitan criterios y pesos positivos que sumen exactamente 100 %.
6. Confirmar la revisión de criterios/pesos y publicar. El instrumento pasa a consulta y el período sigue en configuración.
7. Crear una nueva versión para editar nuevamente; la publicada permanece intacta.

## Verificación realizada

- `php artisan test --compact --filter=Edd`: **20 pruebas correctas, 272 verificaciones**, en SQLite aislado.
- Pruebas de acceso a las seis escrituras, revisiones obsoletas, fechas inválidas, códigos duplicados, criterios de otro instrumento, ponderaciones decimales, publicación repetida, versiones inmutables y reversión completa si falla la auditoría.
- Pruebas de formulario truncado y migración/reversión sin afectar tablas anteriores.
- Comprobación de sintaxis de JavaScript, formato de PHP con Pint y `git diff --check`.
- Navegador: creación de período/instrumento, seis criterios entre los cinco bloques, guardado, publicación, creación de versión 2 y recuperación de valores.
- Dos sesiones sobre el mismo período: la segunda recibió 409, mantuvo lo escrito y ofreció abrir la versión guardada; la primera conservó su modificación.
- Revisión visual en escritorio y móvil de 390 px, sin desbordamiento horizontal. No hubo errores del JavaScript EDD; el entorno de pruebas bloquea intencionalmente el service worker de notificaciones del sistema.

## Alcance pendiente

Población, asignación de evaluadores, apertura del período, respuestas, autoevaluación, devolución, cierre y reportes permanecen como estructura. No existen aún operaciones para habilitar períodos ni abrir evaluaciones. Las condiciones de cierre y excepciones guardadas son texto de definición, pendiente de convertir en reglas operativas. La asociación con puestos/descriptivos y las rúbricas estructuradas de objetivos se completarán con esas entidades.

El siguiente incremento puede continuar con población y responsables sobre esta configuración, conservando versiones, escala y auditoría.
