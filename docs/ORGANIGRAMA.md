# Organigrama institucional

Integración del 01/10/2026. Fuente y contrato: paquete `organigrama/`, conservado sin modificaciones. El módulo está implementado y validado en SQLite aislado. **No se ejecutó la migración ni la importación en MySQL**: la conexión configurada no estaba disponible en esta sesión.

## Arquitectura encontrada

- Laravel **12.43.1** instalado; Composer requiere Laravel `^12.0` y PHP `^8.2`. PHP CLI disponible: **8.2.12**.
- Frontend real: Blade, Bootstrap 5.3.3, jQuery 3.7.1 y JavaScript servido desde `public/`. Existe configuración Vite/Tailwind, pero el layout utilizado por RRHH carga CSS/JS directamente. No usa React, Vue, Inertia ni Livewire.
- Layout `resources/views/layouts/app.blade.php`; rutas de RRHH en `routes/rrhh.php`, con prefijo `/rrhh` definido en `routes/web.php`. Controladores bajo `App\Http\Controllers\RRHH`, servicios con Query Builder, validaciones y transacciones.
- Sesión y middleware `auth`; roles en `users.rol`, habilitación en `users.estado`. La administración de RRHH ya usa el Gate `edd.administrar`: cuenta `ACTIVO` y rol `Administrador/a`.
- `User` y `Area` son los modelos existentes. Colaboradores en `empleados` (`LEGAJO`, `COLABORADOR`, `ID_ROL`, `ID_AREA`, `ESTADO`); roles laborales en `rol_empleados` (`ID_ROL`, `NOMBRE`); áreas en `areas` (`ID_AREA`, `NOMBRE`). Estos catálogos legacy se consultan directamente, sin modelos Eloquent adicionales.
- Biblioteca administra documentos descriptivos de puestos y sus versiones; no es un catálogo de ocurrencias de posiciones jerárquicas. En el repositorio no hay una entidad previa que represente esas posiciones ni un grafo de dependencias.
- Las migraciones recientes son aditivas; EDD valida referencias a claves legacy `int signed` en sus servicios. Su auditoría usa eventos con before/after, actor y request ID. PHPUnit 11.5.46; pruebas Feature con esquemas SQLite aislados. La factory genérica de User no refleja el esquema legacy, por lo que se sigue el patrón de fixtures de EDD.

La inspección del esquema desplegado no pudo completarse por falta de conexión MySQL. Los nombres y tipos legacy anteriores se sustentan en los servicios, controladores y pruebas existentes. La simulación de importación permite verificar estos vínculos cuando la conexión esté disponible.

## Decisiones de integración

Una posición institucional es una **ocurrencia de un rol**, no un nuevo catálogo laboral: los dos `Enfermero` y los dos `Cadete` deben seguir siendo nodos diferentes. Se agregan:

| Tabla | Finalidad |
|---|---|
| `organigrama_posiciones` | ID estable del JSON o UUID para nuevas altas, título institucional, texto visible de responsables, referencias opcionales `rol_id` y `area_id` a los catálogos existentes. |
| `organigrama_dependencias` | Pares superior/hijo únicos, tipo `direct`, `support` o `shared`, orden entre hermanos. |
| `organigrama_responsables` | Vinculación de una posición con uno o varios legajos existentes; no copia personas. |
| `organigrama_estado` | Raíz, revisión global, huella y reporte de importación; una fila para serializar cambios concurrentes. |
| `organigrama_eventos` | Auditoría con actor existente, before/after y request ID; importaciones CLI identificadas con acción `importar` y actor nulo. |

No se modifican ni duplican `empleados`, `areas`, `rol_empleados`, `users` ni los descriptivos de Biblioteca. No se infiere la identidad de una posición por título. Los vínculos a tablas legacy se validan en servidor, como en EDD; las nuevas dependencias y responsables tienen claves foráneas a posiciones. Si otros módulos eliminan físicamente registros legacy, deberán resolver también sus referencias al organigrama; este módulo no cambia esos procesos.

Lectura: cuenta autenticada y activa. Edición: reutiliza la autorización `edd.administrar`; no se inventa un rol RRHH nuevo. La configuración y los catálogos de edición no se renderizan para lectores; todos los endpoints de escritura verifican el permiso en servidor.

Las altas, ediciones de posición/dependencias/responsables y bajas son transaccionales y auditadas. Un bloqueo sobre la fila de estado protege también contra ciclos concurrentes entre ramas. La revisión del cliente evita sobrescrituras: un guardado obsoleto devuelve HTTP 409 y pide recargar. La raíz no admite superiores ni baja. Una posición con hijos no puede eliminarse aunque tenga dependencias compartidas: primero deben reasignarse sus hijos. Cada posición debe estar conectada a la raíz y tener al menos un superior salvo la propia raíz. Empates de orden se resuelven por ID estable.

El prototipo se separó en Blade, CSS acotado a `#org-module` y JavaScript nativo. Se retiraron el restablecimiento destructivo y la exportación de una aplicación HTML autónoma. `localStorage` guarda solamente vista, zoom y expansión, con clave por usuario. Las tres vistas toman los mismos datos de la base; cada guardado incorpora la respuesta del servidor y el siguiente cambio de vista la presenta sin recargar.

La vista horizontal dibuja todas las relaciones visibles y un único nodo por posición. La vertical representa relaciones adicionales como enlaces navegables al nodo único. El árbol mantiene Directorio arriba, ejecutivos y apoyos/secretaría compartida en la franja superior y ramas debajo, con azul/verde de la referencia. Los apoyos también pueden tener hijos. En diagramas grandes se conserva el ajuste completo y se usa zoom/arrastre para leer el detalle. No se agregaron dependencias Composer/npm ni se requiere compilar assets.

## Instalación e importación

Desde la raíz del proyecto, con la conexión institucional disponible:

```powershell
php artisan migrate --path=database/migrations/2026_10_01_100000_create_organigrama_tables.php
php artisan organigrama:importar --dry-run --reporte=storage/app/private/organigrama/simulacion.json
php artisan organigrama:importar --reporte=storage/app/private/organigrama/importacion.json
```

El archivo predeterminado es `organigrama/02_DATOS/organigrama-base.json`. Se puede pasar otra ruta como argumento. La migración solo crea las tablas del módulo y no borra datos existentes. En despliegues con rutas o vistas cacheadas, regenerar esas cachés con el procedimiento habitual del proyecto.

Antes de la primera importación se admite un mapa explícito por ID del JSON:

```json
{
  "cadete": { "rol_id": 2, "area_id": 1, "legajos": [123] },
  "cadete-2": { "rol_id": 2, "area_id": 1, "legajos": [456] }
}
```

Los números del ejemplo deben sustituirse por IDs reales verificados; no ejecutar ese ejemplo literalmente. Usar `--mapa=ruta/al/mapa.json` tanto en la simulación como en la importación. Los legajos deben corresponder a colaboradores activos. Un array vacío de legajos deja explícitamente la posición sin vínculos.

El matching automático de responsables exige nombre completo único entre colaboradores activos, normalizando acentos, mayúsculas y espacios. No invierte apellidos/nombres, no acepta coincidencias parciales ni crea empleados. Nombres abreviados como `Dr. Gual` quedan pendientes salvo mapa explícito. Solo se vincula automáticamente un rol cuando hay un único colaborador resuelto y el título coincide con el rol laboral de esa persona. Las áreas se vinculan explícitamente: no se presume que el área actual del responsable defina la ubicación institucional de la posición.

La segunda ejecución con el mismo archivo es una operación sin cambios: no duplica filas, no resucita bajas ni repone dependencias eliminadas, y preserva las ediciones manuales. Devuelve el reporte de la importación inicial. Un archivo diferente no reemplaza una estructura existente. Cambiar el mapa después de importar tampoco sobrescribe la estructura: esos vínculos se corrigen desde Configuración. `--dry-run` calcula el mapeo actual sin escribir en la base.

Los reportes incluyen posiciones, dependencias, disponibilidad de catálogos, coincidencias con criterio, responsables pendientes y vínculos por ID. Se guardan fuera de `public/`.

## Resultado obtenido y limitación actual

- Importación de QA: **65 posiciones y 65 dependencias**, preservando los tres tipos y la secretaría única con sus dos superiores.
- Reimportación comprobada sin duplicados ni pérdida de ediciones.
- Con los colaboradores ficticios **exclusivos de la base de pruebas**, hubo 1 coincidencia y 36 menciones pendientes. El fixture incluye deliberadamente dos homónimos y un colaborador de baja para validar que no se vinculen automáticamente.
- Reporte de QA: `storage/framework/testing/organigrama-browser/import-report.json`. **No es un reporte de la nómina real.**
- La vinculación real de responsables sigue sin verificar. No se importó ninguna posición en la base institucional. Ejecutar la simulación y revisar `pendientes` cuando vuelva MySQL; esa será la lista válida de responsables no vinculados.

## Verificación manual

### Ajuste visual de cabecera y conexiones (01/10/2026)

La cabecera de consulta conserva únicamente «Nuestra organización», el contador y el acceso a Configuración. Las tres vistas, búsqueda y controles se reúnen en una barra compacta; las referencias pasan al pie y el gráfico usa la altura restante de la ventana. En Configuración se vuelve al gráfico con el botón correspondiente.

Las tarjetas sin responsable omiten ese renglón en las tres vistas. En el árbol, las posiciones hermanas se disponen en una sola fila bajo su superior: la grilla anterior de varias filas hacía que las conexiones hacia filas inferiores atravesaran tarjetas ajenas y aparentaran dependencias inexistentes. Los vínculos laterales de apoyo/compartidos pasan por encima de las tarjetas. Se mantienen los datos y los superiores originales.

Verificado en Chrome, escritorio y celular, con las 65 relaciones: el control geométrico de las líneas comprueba que no crucen ninguna tarjeta ajena. También se verificaron la cabecera compacta, la omisión del texto de responsable vacío y las pruebas existentes del módulo (10 pruebas, 90 aserciones).

La cabecera de cada ejecutivo y sus dependencias ahora comparten una misma columna de layout. Dirección Médica queda centrada sobre su rama y Gerencia sobre la suya, con separación horizontal entre sus barras de dependencias. Las líneas directas médicas se distinguen en verde; las administrativas, en azul. Los apoyos se mantienen junto a su superior y la Secretaría ocupa una única columna compartida. La prueba de navegador comprueba el centrado y la ausencia de solapamiento de ambas barras con los dos ejecutivos abiertos y al cerrar/reabrir cada uno; también verifica que Farmacia y Quirófano pertenezcan al grupo de Dirección Médica.

1. Instalar e importar con los comandos anteriores. Ingresar con una cuenta activa. Abrir **General → Organigrama** en el sidebar, antes de Capacitaciones y Biblioteca Organizacional. URL `/rrhh/organigrama`.
2. Elegir **Vista horizontal por jerarquías**, pulsar **Desplegar todo** y comprobar 65 posiciones. Buscar `Cadete`, `Enfermero` o un responsable y pulsar Enter. Probar contracción individual, global, centrar, ajustar, zoom, Ctrl+rueda y arrastre sobre el fondo.
3. Elegir **Vista vertical**. Abrir/cerrar ramas y usar los enlaces de dependencia compartida. Cada posición tiene una sola tarjeta; el otro superior conserva un enlace hacia ella.
4. Elegir **Vista árbol**. Comprobar Directorio arriba, Dirección Médica y Gerencia, la Secretaría compartida y apoyos. Ajustar, acercar y arrastrar; ampliar una rama y verificar sus conectores.
5. Como administrador activo, abrir **Configuración**. Elegir una posición por su ID; modificar título/texto de responsable y seleccionar rol, área y colaboradores existentes. Agregar un segundo superior, elegir tipo y orden entre hermanos; guardar, alternar las tres vistas y recargar.
6. Crear una posición, mover sus dependencias y eliminarla cuando no tenga hijos. Intentar un superior descendiente, una relación duplicada o borrar una posición con hijos: el servidor debe rechazarlo sin guardar cambios parciales.
7. Abrir dos sesiones de edición; guardar una y luego la otra: la segunda debe rechazar la revisión obsoleta. Recargar antes de reintentar.
8. Ingresar como lector activo: no debe aparecer Configuración y los endpoints de escritura deben devolver 403. Una cuenta de baja tampoco tiene lectura. Repetir consulta en celular.

## Pruebas ejecutadas

- `php vendor/bin/phpunit tests/Feature/OrganigramaTest.php`: **10 pruebas, 90 aserciones aprobadas**. Permisos, importación/comando/simulación, 65 posiciones, títulos repetidos, responsables ambiguos, múltiples superiores, compartidas, ciclos, duplicados, orden, CRUD, auditoría, persistencia y conflictos de revisión.
- Suite previa sin ajustes de entorno: 91 pruebas, 3 fallos previos de Biblioteca.
- Suite completa con `BIBLIOTECA_TEST_PYTHON` apuntando al Python del runtime: **101 pruebas, 1434 aserciones, 99 aprobadas y 2 fallos**. Los restantes son `BibliotecaImportTest::test_sync_unchanged_sources_creates_no_versions_or_events` y `BibliotecaTest::test_migration_preserves_all_versions_records_files_and_views`: requieren `BIBLIOTECA_TEST_BUNDLE`/`BIBLIOTECA_TEST_SOURCE`, que no estaban configurados ni se incluyeron en este paquete. No se omitieron esas pruebas ni se modificó Biblioteca.
- `php vendor/bin/pint --test` sobre los siete PHP de implementación/fixture/Feature: aprobado.
- `node --check public/js/organigrama.js`: aprobado.
- `php artisan route:list --path=organigrama`: cinco rutas registradas.
- `tests/organigrama-browser.cjs`: Chrome, escritorio 1440×1000 y móvil 390×844. Tres vistas, unicidad de nodos, cantidad de conectores, ausencia de superposición de tarjetas, búsqueda, expansión individual/global, hijo de una posición compartida, múltiples superiores, CRUD con recarga, orden, zoom, arrastre, ajuste, lector sin configuración y preferencias sin estructura institucional. Aprobado sin errores JavaScript del módulo.
- El layout general produjo errores de `$ is not defined` cuando no pudo descargar jQuery del CDN. El test los registra explícitamente como `shellErrors` en vez de confundirlos con errores del módulo; cualquier otra excepción hace fallar la prueba. No se cambió el AppShell para resolver ese problema externo.

Evidencia: `storage/logs/organigrama-baseline.xml`, `storage/logs/organigrama-final.xml` y `storage/framework/testing/organigrama-browser/qa/` (capturas y `result.json`).

Para repetir QA de navegador en una base separada:

```powershell
php tests/organigrama-fixture.php
$env:ORGANIGRAMA_BROWSER_TEST='1'
php -S 127.0.0.1:8013 -t public tests/organigrama-router.php
```

En otra terminal, con Playwright disponible (o `PLAYWRIGHT_MODULE` apuntando a su instalación):

```powershell
node tests/organigrama-browser.cjs
```

El fixture se crea solo una vez y se niega a sobrescribir una base de QA existente. El router solo acepta localhost con la variable de pruebas explícita, utiliza SQLite en `storage/framework/testing/organigrama-browser/` y nunca está registrado en las rutas públicas. La prueba de navegador agrega y elimina su posición temporal; no usa MySQL.

## Archivos creados y modificados

Creados:

- `database/migrations/2026_10_01_100000_create_organigrama_tables.php`
- `app/Services/Organigrama/Estructura.php`
- `app/Services/Organigrama/Importador.php`
- `app/Http/Controllers/RRHH/OrganigramaController.php`
- `app/Console/Commands/ImportOrganigrama.php`
- `resources/views/organigrama/index.blade.php`
- `public/css/organigrama.css`
- `public/js/organigrama.js`
- `tests/Support/OrganigramaFixture.php`
- `tests/Feature/OrganigramaTest.php`
- `tests/organigrama-fixture.php`
- `tests/organigrama-router.php`
- `tests/organigrama-browser.cjs`
- `docs/ORGANIGRAMA.md`

Modificados:

- `app/Providers/AppServiceProvider.php`: Gates de lectura/edición.
- `routes/rrhh.php`: rutas del módulo con permisos.
- `resources/views/layouts/app.blade.php`: enlace de navegación.

No se crearon modelos, factories ni seeders paralelos: se usa Query Builder como los módulos recientes, el comando realiza la importación y los fixtures existen solo para las pruebas.
