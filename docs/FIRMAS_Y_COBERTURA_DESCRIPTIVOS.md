# Descriptivos: permisos, asignación y aceptación

Implementado el 20/09/2026 en el módulo Biblioteca Institucional del sistema Laravel.

**Actualización 21/09/2026:** la pantalla ahora se llama Descriptivos y firmas, trabaja por colaborador y guarda automáticamente al seleccionar un DP. Se retiró la tabla de cobertura por categorías. Convenio y categoría se consideran datos de liquidación; servicio y rol orientan el descriptivo. Ver [criterio vigente y alcance](CATEGORIAS_SERVICIOS_Y_DESCRIPTIVOS.md). La descripción siguiente conserva el diseño inicial y su compatibilidad histórica.

## Permisos

El rol autorizado para Administración, Control documental, revisión, importación, sincronización, publicación, fuentes documentales, asignaciones y registro general de aceptaciones es exactamente `Administrador/a`, que es la denominación existente en `users.rol`.

Esas rutas tienen middleware de autorización. Los enlaces administrativos también se ocultan al resto de los usuarios. Un usuario coordinador o supervisor no adquiere estos permisos por tener acceso a otros módulos del sistema. Un administrador dado de baja tampoco puede gestionar la biblioteca.

Los usuarios autenticados conservan la consulta de la biblioteca. Las constancias de aceptación sólo pueden consultarlas su firmante y los administradores.

## Categorías y asignaciones

Acceso administrativo: `/biblioteca/administracion/categorias-y-firmas`.

La vinculación usa los datos existentes:

```text
users.id → users.legajo → empleados.LEGAJO
                            └─ empleados.ID_CATEG → categ_empleados.ID_CATEG
```

Se configura un descriptivo base por categoría. Una asignación individual al legajo puede reemplazarlo. «Usar categoría» elimina la excepción y restablece el descriptivo base. Un descriptivo puede servir a varias categorías si el administrador confirma que corresponde; no se copia ni se inventa su contenido.

Las asignaciones se guardan de forma explícita, registran quién las modificó y detectan cambios simultáneos mediante una revisión numérica. No se deducen vínculos por nombres parecidos. Esto es especialmente relevante para categorías como Administrativo 1ra, Analista, Coordinador/a o Jefatura, que pueden incluir diferentes funciones y áreas.

El panel muestra todas las categorías, incluidas las inactivas, y distingue:

- Sin descriptivo vinculado: revisar si se puede asociar un documento existente o si falta confeccionarlo.
- Pendiente de publicación: hay un documento asociado, pero falta una versión vigente validada.
- Con versión vigente: la categoría tiene una versión disponible para aceptación.

Además muestra los colaboradores activos, su asignación efectiva, situación de firma, cuentas ausentes o duplicadas y acceso a la constancia vigente. El registro histórico está en `/biblioteca/administracion/firmas`.

## Circuito del colaborador

Acceso: `/biblioteca/mi-descriptivo`, desde el índice o la navegación de la biblioteca.

1. Se verifica la cuenta autenticada y su legajo; no se toman nombres, DNI ni legajos enviados por el formulario para determinar quién firma.
2. Se comprueba que el usuario y el colaborador estén activos, que la categoría exista y esté activa y que haya una sola cuenta activa con ese legajo.
3. Se muestra el descriptivo aplicable y su versión publicada y validada. Un borrador o una vigencia sin verificar no habilitan la aceptación.
4. El colaborador lee el contenido y marca expresamente la declaración de lectura, comprensión y aceptación.
5. Al pulsar «Firmar aceptación» se registra la aceptación de esa versión y se muestra una constancia imprimible.

El registro conserva usuario, nombre, legajo, categoría, versión, asignación, fecha y hora del servidor, declaración aceptada, copia del contenido y huella SHA-256. Es una aceptación registrada desde la cuenta autenticada; el módulo no solicita un trazo manuscrito ni incorpora certificados de firma.

No hay una función para que el administrador firme en nombre del colaborador. El reenvío del mismo formulario devuelve la constancia existente, sin duplicarla. Los formularios POST están protegidos por CSRF; el endpoint de firma limita la frecuencia de solicitudes.

## Cambios y nueva aceptación

La aceptación corresponde a una versión, no a un documento genérico. Al publicar una nueva versión, la anterior y su constancia se conservan y el colaborador vuelve a estar pendiente de firma. Editar un borrador no invalida la aceptación vigente hasta que ese borrador se publica.

La firma bloquea y vuelve a comprobar dentro de una transacción el usuario, colaborador, categoría, asignación, documento y versión vigente. Un formulario abierto antes de una publicación o reasignación se rechaza y solicita leer la versión actual. Una modificación de la asignación también requiere aceptación del vínculo actual; las excepciones individuales no se alteran por cambios en el descriptivo base de otra asignación.

Las constancias históricas muestran su propia copia del contenido, incluso si después cambia el documento, la categoría o la asignación. No se presentan como aceptación de versiones posteriores.

## Datos y despliegue

Migración específica:

```powershell
php artisan migrate --path=database/migrations/2026_09_20_120000_add_biblioteca_assignments_and_acceptances.php --force
```

Crea únicamente `bib_category_documents`, `bib_employee_documents` y `bib_acceptances`. No modifica las categorías, colaboradores ni usuarios existentes. Las constancias tienen una restricción única por usuario, legajo, versión y revisión de asignación, y referencias a los documentos/versiones que deben conservarse. No hay edición ni eliminación de constancias desde la aplicación. Incluir estas tablas en el respaldo del módulo.

## Cruce inicial real

Resultado al 20/09/2026, conservado en `docs/COBERTURA_DESCRIPTIVOS_2026-09-20.json`:

- 23 categorías, de las cuales 22 están activas.
- 41 descriptivos existentes; 2 tienen una versión vigente publicada.
- 209 colaboradores activos.
- 188 colaboradores activos sin una cuenta activa vinculada a su legajo.
- 2 legajos de colaboradores activos con varias cuentas activas.
- Aún no hay asignaciones confirmadas por categoría o por legajo ni aceptaciones reales.

Las 22 categorías activas están pendientes de vincular. Esto no equivale a 22 documentos inexistentes: primero hay que revisar cuáles de los 41 descriptivos corresponden y cuáles necesitan confección o actualización. También se debe revisar la categoría activa «PRUEBA DE CATEG»; la implementación no la modifica ni la elimina.

Para habilitar la firma a toda la nómina, Administración debe confirmar las asignaciones, validar/publicar los documentos aplicables y resolver las cuentas faltantes o ambiguas desde la administración de usuarios existente. No se crearon cuentas, vínculos ni firmas reales como parte de las pruebas.

## Verificación

Pruebas Feature con SQLite aislado: permisos de todos los roles no administradores, consentimiento explícito, identidad obtenida del servidor, reenvío idempotente, nueva publicación y nueva firma, borradores, retiros, reasignaciones, categoría cambiada mientras se leía, formularios adulterados, legajos duplicados, usuarios dados de baja, privacidad e integridad de constancias y cobertura de categorías. También se conservaron las pruebas anteriores de migración, lectura, edición, Word/PDF y sincronización.

Prueba Chrome con colaboradores ficticios: firma inicial, publicación de otra versión, nueva firma, constancia anterior intacta, denegación de acceso a otro colaborador, asignación individual y visualización móvil. Los archivos `tests/biblioteca-acceptance-*` usan exclusivamente un servidor de prueba en loopback, requieren `BIBLIOTECA_ACCEPTANCE_TEST=1` y una base separada; nunca deben utilizarse como servidor normal.

Los accesos y conteos reales se comprobaron en MySQL mediante una transacción de sólo lectura. No se registraron aceptaciones ni asignaciones de prueba en la base institucional.
