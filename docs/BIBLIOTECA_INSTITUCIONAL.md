# Biblioteca Institucional — módulo Laravel

Integración del 18/09/2026 en `NovedadeswebPHP`: Laravel 12, PHP 8.2, Blade, jQuery, Bootstrap y MySQL. La biblioteca usa la sesión y el layout existentes. No requiere React, Next.js ni un servidor Node para funcionar.

## Acceso y funciones

- Menú: **GENERAL → Capacitaciones → Biblioteca Institucional → Ayuda**.
- Índice: `/biblioteca`. Cuatro colecciones: políticas, procedimientos, instructivos y descriptivos de puesto.
- Consulta y búsqueda por contenido, área, estado, revisión y colección; control documental, versiones e historial.
- Administración: crear, duplicar, editar borradores, previsualizar, publicar y retirar versiones. Una publicación conserva la versión anterior en el historial. No hay eliminación de documentos ni edición del contenido de una versión publicada.
- Revisión de descriptivos: guardar observaciones, validar, o validar y publicar. Nombre y área son explícitos; el comentario es opcional y se registra una resolución automática al confirmar.
- Competencias genéricas institucionales propuestas en cinco viñetas, editables por documento. Compromiso se presenta con viñetas. Registro de acciones contraíble.
- Exportación PDF y Word; descarga del original, extracción y huella SHA-256.
- Incorporación de DOCX/PDF con revisión previa: nuevo puesto, versión de un puesto, documento asociado o fuente complementaria. Un archivo ilegible puede conservarse como fuente complementaria.
- Sincronización manual de las 15 carpetas configuradas: detectar nuevos, modificados, faltantes o restaurados. Los nuevos contenidos quedan pendientes de validación; no reemplazan automáticamente una versión vigente. Los originales nunca se escriben.

Usuarios autenticados pueden consultar. Gestión, importación, revisión y publicación requieren el rol existente `Administrador/a`, verificado explícitamente por el servidor. Las comprobaciones se realizan también en el servidor. Las acciones registran el identificador y nombre del usuario.

## Datos migrados

La copia final contiene **51 documentos**: 41 descriptivos, 5 políticas y 5 procedimientos; instructivos queda disponible para nuevas incorporaciones. Se trasladaron **61 versiones, 122 eventos y 64 hallazgos**. Se verificaron **43 archivos**: 41 originales, el HTML de políticas/procedimientos y su configuración documental.

Paquete de respaldo: `laravel-module/snapshot-final-20260918` en el proyecto original. Incluye una copia consistente de SQLite, `bundle.json`, archivos originales y fuente HTML. SHA-256 de `bundle.json`:

`930fe612bf6d8dc1c142693a3876ccd3a568adc3314ddc54c74f892caae17f45`

Las filas de todas las tablas originales se conservan adicionalmente en `bib_legacy_records`, con huellas verificadas. No se transforma una vigencia declarada en el HTML en una aprobación institucional: el control documental señala los instrumentos y fechas faltantes. Tampoco se resuelven hallazgos automáticamente por migrar.

## Organización

```text
app/Console/Commands/ImportBiblioteca.php
app/Http/Controllers/Biblioteca/
app/Services/Biblioteca/
config/biblioteca.php
database/migrations/2026_09_18_150000_create_biblioteca_tables.php
public/css/biblioteca.css
public/js/biblioteca.js
resources/biblioteca/                 # lector DOCX/PDF y carpetas autorizadas
resources/views/biblioteca/           # vistas Blade
routes/biblioteca.php
storage/app/private/biblioteca/       # originales privados; respaldar con la base
tests/Feature/BibliotecaTest.php
tests/Feature/BibliotecaImportTest.php
```

El layout receptor es `resources/views/layouts/app.blade.php`; `routes/web.php` incluye las rutas del módulo. Las nueve tablas nuevas usan prefijo `bib_`: documents, versions, sources, findings, events, legacy_records, migrations, requests y uploads. No se modifican tablas funcionales de otros módulos.

La publicación usa transacciones, bloqueo por documento, control de revisión para detectar ediciones simultáneas y una restricción única para una sola versión vigente por documento. Las solicitudes de creación/importación son idempotentes. Las versiones publicadas sólo se consultan o pasan al historial desde el servicio; el contenido se modifica creando otro borrador.

## Configuración y ejecución

La aplicación se inicia igual que el sistema existente:

```powershell
php artisan serve --host=localhost --port=8000
```

Abrir `http://localhost:8000/biblioteca` con una sesión del sistema.

Variables adicionales del `.env` local:

```dotenv
BIBLIOTECA_PYTHON="ruta/al/python.exe"
BIBLIOTECA_SOURCE_ROOT="ruta/a/DESCRIPTIVOS 2026 - CODEX"
```

Python se usa para leer nuevas cargas DOCX/PDF y sincronizar; requiere `pypdf` para PDF. La consulta, la edición, la publicación y las exportaciones funcionan con PHP. PDF usa el Dompdf ya instalado; Word se genera con ZipArchive y XML. No se agregaron dependencias Composer ni npm.

En este equipo se configuró el Python disponible en el runtime local de Codex. Para desplegar en otro servidor hay que instalar un Python estable, configurar su ruta y dar acceso de lectura a las carpetas fuente. Si ese servidor no tiene acceso a la unidad compartida, la sincronización de carpetas no estará disponible; la consulta y la carga de archivos siguen siendo independientes.

Los archivos fuente están en el almacenamiento privado de **esta instalación Laravel**, aunque MySQL sea remoto. Al desplegar en otro equipo hay que trasladar también `storage/app/private/biblioteca`; no alcanza con apuntar a la misma base. No deben publicarse mediante `storage:link`.

## Migración y respaldo

En una instalación nueva y vacía del módulo:

```powershell
php artisan biblioteca:importar "ruta/al/bundle.json" --solo-validar
php artisan migrate --path=database/migrations/2026_09_18_150000_create_biblioteca_tables.php --force
php artisan biblioteca:importar "ruta/al/bundle.json"
```

Repetir el mismo paquete sólo verifica registros y archivos; otro paquete se rechaza si la biblioteca destino tiene datos. No ejecutar migraciones generales del sistema para instalar este módulo.

Antes de integrar se respaldaron `.env`, `routes/web.php` y `resources/views/layouts/app.blade.php` en `%LOCALAPPDATA%/HP3C/NovedadeswebPHP/respaldos-biblioteca/20260918-144558`. Ese respaldo contiene configuración privada y no debe versionarse.

Respaldar conjuntamente las tablas `bib_*` y `storage/app/private/biblioteca`. La migración no implementa un rollback destructivo: para retirar temporalmente el módulo, quitar su enlace y su inclusión de rutas conservando los datos; una reversión de datos requiere un respaldo verificado.

La aplicación anterior de `localhost:3000` se detuvo durante el traslado. Su SQLite y sus fuentes permanecen como respaldo. Continuar las ediciones en Laravel para evitar dos bibliotecas divergentes.

## Verificación

- 11 pruebas y 170 verificaciones sobre SQLite aislado: migración de todos los registros, permisos, revisión, publicación/historial, conflictos, idempotencia, HTML seguro, DOCX, PDF, preservación de originales y sincronización sin cambios.
- Prueba en Chrome con datos aislados: buscar Tesorera, comprobar competencias y registro contraíble, crear/guardar/previsualizar/publicar una guía, editar otra versión, retirar, crear/publicar un descriptivo y sincronizar. Sin errores JavaScript.
- Adaptación visual comprobada a 390, 768 y 1440 píxeles.
- Pruebas de navegador: `tests/biblioteca-fixture.php`, `tests/biblioteca-router.php` y `tests/biblioteca-browser.cjs`. El router de pruebas es exclusivo de loopback, requiere `BIBLIOTECA_BROWSER_TEST=1` y no forma parte de las rutas de la aplicación ni del directorio público. Nunca usarlo como servidor normal ni desplegar un proceso con esa variable.

Las pruebas funcionales fuerzan una base SQLite separada; no crean documentos de prueba en MySQL. Para ejecutarlas se configuran `BIBLIOTECA_TEST_BUNDLE`, `BIBLIOTECA_TEST_PYTHON` y `BIBLIOTECA_TEST_SOURCE` y se pasan ambos archivos Feature a PHPUnit. La prueba de sincronización espera los 41 originales de la copia inicial.

## Límites conservados

Los PDF escaneados no incorporan OCR. El original y el contenido extraído se conservan; los archivos que no puedan interpretarse requieren revisión. Las exportaciones representan la versión de la biblioteca, no son una reproducción exacta de la diagramación del archivo fuente. Las tablas se conservan como contenido editable.

Desde el 20/09/2026, Categorías y firmas usa el catálogo real `categ_empleados`, la nómina `empleados` y el legajo de `users`. La asociación se confirma por categoría, con excepciones individuales. Ver [Firmas y cobertura de descriptivos](FIRMAS_Y_COBERTURA_DESCRIPTIVOS.md).


## Actualización del 20/09/2026

Administración y Control documental son exclusivos de `Administrador/a`, con autorización en cada ruta. Se incorporan Mi descriptivo, aceptación personal por versión, constancias históricas privadas y el panel Categorías y firmas. Las tres tablas nuevas requieren la migración `2026_09_20_120000_add_biblioteca_assignments_and_acceptances.php`. Ver [la documentación del circuito y sus pendientes iniciales](FIRMAS_Y_COBERTURA_DESCRIPTIVOS.md).


## Edición y nuevas versiones — 23/09/2026

Con una cuenta de administración, cada política, procedimiento, instructivo y descriptivo tiene **Editar** en el listado y **Editar y crear nueva versión** en su ficha.

1. Abrir el editor y modificar el contenido. Se pueden conservar y editar las tablas y secciones existentes.
2. Registrar opcionalmente el motivo o resumen del cambio.
3. Elegir **Guardar nueva versión**. La aplicación asigna el siguiente número interno y conserva la versión base, su contenido y sus documentos fuente. Abrir el editor o cancelar no crea registros.
4. El resultado queda en borrador. Se puede seguir trabajando con **Guardar borrador**, recargar el editor o abrir **Ver versión guardada**. La ficha muestra los borradores en preparación con **Continuar borrador**.
5. Publicar cuando corresponda: la versión vigente anterior pasa al historial. Las políticas conservan su aprobación exclusiva de Gerencia. Las nuevas versiones de procedimientos e instructivos deben registrar su propia aprobación e instrumento antes de publicarse.

**Versiones e historial** muestra número, versión de origen, estado y última actualización. Administración también ve el autor de creación, el motivo y accesos para continuar borradores o preparar una nueva versión desde un antecedente. El registro de acciones conserva cada guardado y su autor.

La nueva versión se crea con una transacción, bloqueo del documento, control de revisión e identificador de solicitud. Reintentar el mismo guardado no duplica versiones; una edición sobre una revisión desactualizada se rechaza sin sobrescribir contenido. Guardar cambios de contenido devuelve la revisión a Pendiente. El motivo se conserva durante la publicación.

No requiere migraciones nuevas. La implementación está en este proyecto Laravel; la aplicación anterior y el paquete de migración permanecen como antecedentes.

### Verificación

- Pruebas funcionales: BibliotecaTest.php, BibliotecaImportTest.php y BibliotecaAcceptanceTest.php, con SQLite aislado. Incluyen las cuatro colecciones, versiones importadas e históricas, cancelación sin creación, reintentos, permisos, conflictos, tablas, publicación y conservación del original.
- Prueba de navegador: tests/biblioteca-versions-fixture.php y tests/biblioteca-versions-browser.cjs. Usan el router local de aceptación y un BIBLIOTECA_TEST_RUN exclusivo. Verifican editar desde ficha/listado, vista previa, guardar, recargar, continuar, publicar e historial en 390, 768 y 1440 píxeles.
- Los datos de prueba se guardan exclusivamente en storage/framework/testing/biblioteca-acceptance-browser-<BIBLIOTECA_TEST_RUN>; nunca en la base institucional.

Resultado de esta actualización: **35 pruebas funcionales, 627 comprobaciones y recorrido en Chrome aprobado para las cuatro colecciones**, sin errores JavaScript. Revisadas las capturas del historial y del editor en pantalla pequeña.


## Ajustes de interfaz — 23/09/2026

- Las acciones Editar, Duplicar y Retirar versión comparten la cabecera con PDF, Word e Imprimir. El regreso a la colección tiene formato de botón.
- El editor ofrece Descartar cambios para recuperar el último guardado. En una edición basada en otra versión o un documento nuevo, Descartar edición / Descartar nuevo documento sale sin crear ni modificar registros. Si existen cambios sin guardar, solicita confirmación.
- Aprobaciones y visibilidad e Importar descriptivo incluyen Volver a Administración.
- Las competencias genéricas se ofrecen como una propuesta editable: se pueden modificar, agregar o quitar. Los cambios se conservan al guardar, publicar, duplicar y preparar nuevas versiones.
- El editor de políticas, procedimientos e instructivos incluye estilos de título, subtítulo y cita; negrita, cursiva, subrayado y tachado; listas, enlaces, tablas configurables, deshacer/rehacer y limpieza de formato. El selector de emojis también está disponible en los bloques y tablas de descriptivos.
- Los buscadores del índice, colecciones, Administración, Control documental, colaboradores y registro de aceptaciones permiten mostrar 25, 50, 100 o 150 filas. Por defecto se muestran 25.
- Al pulsar un encabezado con flechas se alterna el orden ascendente/descendente. El orden se aplica al conjunto filtrado antes de paginar y se conserva al cambiar de página o de cantidad. Las versiones se ordenan como números y las fechas cronológicamente.

Pruebas de interfaz: tests/biblioteca-ui-fixture.php y tests/biblioteca-ui-browser.cjs, sobre SQLite aislado con más de 150 documentos, colaboradores y constancias ficticias.

Verificación de estos ajustes: **38 pruebas funcionales y 711 comprobaciones aprobadas**. Recorrido completo en Chrome aprobado, sin errores JavaScript: descartes sin escrituras, conservación de formato/emojis/competencias después de recargar, navegación, tamaños de página, orden global y diseño en 390, 768 y 1440 píxeles.


## Rediseño del índice — 24/09/2026

La portada concentra la consulta en un buscador destacado con filtros por colección. Las tarjetas de las colecciones son enlaces completos y se presentan en tres columnas en escritorio. El acceso personal al descriptivo conserva el aviso de firma pendiente; los accesos administrativos respetan los permisos existentes.

- La búsqueda funciona al escribir, pulsar Buscar o seleccionar una colección. Ver todos los documentos abre el listado en la misma página. Limpiar búsqueda o Escape restablece las colecciones.
- Los resultados incluyen cantidad, 25/50/100/150 filas por página, orden y paginación. El selector aparece junto a los resultados, donde se utiliza.
- Las búsquedas iniciadas desde el índice usan visible_sections=1 para coincidir con sus conteos y colecciones habilitadas. Administración conserva el acceso a las colecciones ocultas.
- Se contemplan búsquedas sin coincidencias, errores con reintento y cancelación de respuestas pendientes. Sin JavaScript siguen funcionando el formulario y los enlaces normales.
- En celular las filas se distribuyen en bloques con sus datos y acciones; se mantienen los encabezados para ordenar sin desplazamiento horizontal.
- El diseño reutiliza la paleta institucional, iconos existentes y recursos locales. No requiere migraciones ni nuevas dependencias.

Verificación específica: 7 pruebas funcionales y 135 comprobaciones aprobadas sobre SQLite aislado, más recorrido en Chrome en 320, 390, 768, 1024 y 1440 píxeles. Incluye búsqueda, filtros, orden, paginación, permisos, firma pendiente, reintento, cancelación y funcionamiento sin JavaScript. Sin errores JavaScript ni escrituras documentales durante el recorrido.

El fixture tests/biblioteca-home-fixture.php genera 51 documentos visibles en una base separada. tests/biblioteca-home-browser.cjs utiliza el router local de aceptación con BIBLIOTECA_ACCEPTANCE_TEST=1 y un BIBLIOTECA_TEST_RUN exclusivo; no debe registrarse como ruta pública.


## Identidad visual y encabezado compartido — 24/09/2026

Las colecciones usan los colores extraídos del logo institucional: Políticas #00568B, Procedimientos #008ECF y Descriptivos de puesto #02B18F. Instructivos comparte el turquesa cuando se habilita. Iconos y acentos mantienen los colores originales; los textos pequeños utilizan una variante más oscura para facilitar la lectura.

El encabezado del índice se comparte con todas las pantallas de la biblioteca: título, subtítulo, acciones en la misma fila y navegación con la sección activa. Ver como otro usuario y Control documental conservan sus permisos; durante la consulta como otra persona permanece el aviso y el regreso a la cuenta propia. Las subsecciones de administración también señalan su pestaña.

Comprobado en 14 pantallas de escritorio y en celular, sin errores JavaScript ni escrituras documentales. Las 6 pruebas existentes de permisos, visibilidad, firma pendiente y consulta como otro usuario aprobaron 126 comprobaciones.


## Navegación dinámica — 24/09/2026

El contenido bajo el menú está contenido en #bib-content. El encabezado, la navegación y los recursos globales permanecen montados; #bib-view recibe cada nueva vista. Durante la carga se muestra un estado accesible y se conserva la vista anterior si ocurre un error.

biblioteca-navigation.js intercepta los enlaces internos y los formularios que no tienen un manejador específico. Solicita HTML con X-Biblioteca-Navigation: 1; el layout fragment.blade.php omite la estructura y los scripts de la aplicación. Se conservan la URL, Atrás/Adelante y los filtros al regresar. La respuesta actualiza la pestaña activa, las opciones permitidas, el token CSRF y el aviso de consulta como otro usuario cuando corresponde. Los fragmentos no se almacenan en caché y sus respuestas declaran Vary.

biblioteca.js expone un ciclo de montaje y limpieza por vista: retira eventos y cancela búsquedas pendientes al salir. Impide salir durante guardados y pide confirmación cuando hay cambios del editor sin guardar. Los scripts recibidos no se ejecutan; sólo se conserva la configuración JSON del editor. Las respuestas de navegación atrasadas se descartan.

Los formularios conservan validación, archivos mediante FormData, confirmaciones y permisos del servidor. Ante una respuesta incierta a un POST, Revisar vista consulta por GET y no repite la operación. Descargas, enlaces externos, anclas y apertura en otra pestaña mantienen su comportamiento normal. Sin JavaScript se usan las rutas y formularios completos.

Verificación: tests/biblioteca-navigation-browser.cjs, con tests/biblioteca-home-fixture.php y un BIBLIOTECA_TEST_RUN nuevo por ejecución. El recorrido carga el documento principal una sola vez y comprueba la identidad de los nodos del encabezado y menú. Incluye filtros, historial, respuestas atrasadas, reintento, protección de ediciones, guardado único, vista previa/emojis, asignaciones, visibilidad, consulta como otro usuario, carga real de Word, descargas, firma personal y navegación móvil. Todo sobre SQLite y archivos de prueba aislados.

## Formato de descriptivos, eliminación de borradores y visibilidad documental — 24/09/2026

Los bloques de texto de descriptivos ahora usan edición enriquecida: negrita, cursiva, subrayado, tachado, enlaces, deshacer/rehacer, limpieza de formato y emojis. El selector de párrafo, viñeta, numeración o subtítulo conserva la estructura de cada bloque. Cada barra opera sobre su propio campo, también en secciones adicionales. El HTML se valida y sanitiza en servidor, se conserva junto al texto de búsqueda y se utiliza en la lectura y vista previa. La exportación Word conserva los estilos en línea y los saltos de línea. Los bloques antiguos de texto plano y las tablas continúan siendo compatibles.

Descartar cambios vuelve al último guardado y mantiene el borrador. Eliminar borrador es una acción distinta, con confirmación, disponible después de guardar: elimina sólo una versión de origen sistema que nunca se publicó y regresa a la versión de origen o a la vigente anterior. Si era el único borrador de un documento nuevo sin vínculos, elimina también su ficha. Rechaza revisiones desactualizadas, versiones publicadas, bases de otras versiones y vínculos que deban preservarse. Conserva la auditoría y los identificadores de solicitudes; los reintentos no recrean el borrador y los números eliminados no se reutilizan.

Aprobaciones y visibilidad permite Mostrar/Ocultar cada documento de todas las colecciones, con buscador, filtro por colección, orden y 25/50/100/150 filas. Las políticas conservan su requisito de aprobación. Los colaboradores no pueden buscar, abrir, descargar ni firmar un descriptivo oculto; las constancias anteriores permanecen disponibles para su titular y Administración. Los administradores conservan acceso a todos los documentos.

Pruebas sobre SQLite y datos ficticios: regresiones de formato, Word, descarte definitivo, concurrencia mediante revisión, idempotencia, permisos, visibilidad y conservación de constancias. tests/biblioteca-editor-browser.cjs comprueba formato en el segundo bloque, emoji, vista previa, guardar/reabrir, ambos descartes, filtros, Mostrar/Ocultar y navegación sin recargar el encabezado. Capturas en 1440 y 390 píxeles. No requiere migraciones ni cambios sobre documentos reales.

Verificación final: 46 pruebas funcionales y 849 comprobaciones aprobadas, más los recorridos de editor y navegación dinámica en Chrome, sin errores JavaScript. Las pruebas utilizaron exclusivamente documentos y usuarios ficticios en SQLite aislado.

## Interruptores de visibilidad con guardado automático — 24/09/2026

Todos los controles Mostrar/Ocultar de Biblioteca comparten visibility-switch.blade.php: interruptor accesible por teclado, estado Visible/Oculto y mensaje local de guardado. Las secciones se presentan como filas compactas con icono y el interruptor a la derecha. Los documentos usan el mismo control dentro de la columna de visibilidad. Sin JavaScript queda un botón de envío como alternativa.

El POST devuelve el estado y la revisión confirmados por la transacción. Mientras guarda se bloquean nuevos cambios, filtros y salida de la vista. Las secciones actualizan los enlaces del menú conservando sus nodos y el contenido de la pantalla. No se recarga la página ni se pierde la posición en la tabla.

Ante error, una consulta GET protegida verifica el estado persistido: restaura el estado real o confirma un guardado cuya respuesta se perdió, sin repetir la escritura. Los conflictos de revisión muestran el estado actual y permiten una nueva decisión. Si tampoco se puede consultar, el interruptor queda deshabilitado con Verificar estado. Se mantienen permisos y la protección de consulta como otro usuario.

Pruebas: BibliotecaAcceptanceTest y tests/biblioteca-visibility-browser.cjs sobre SQLite aislado; incluyen teclado, secciones/documentos, menú persistente, filtros, error de red, respuesta perdida, revisión desactualizada y recuperación por consulta. Los demás controles de visibilidad de períodos ya utilizaban interruptores con guardado automático; los filtros de búsqueda y campos de formularios de datos personales conservan su función.

Verificación de esta actualización: 26 pruebas funcionales, 383 comprobaciones y recorrido específico de interruptores aprobados. Revisión visual de secciones y documentos en escritorio y móvil.
