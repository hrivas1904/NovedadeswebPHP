# Biblioteca Institucional — módulo Laravel

Integración del 18/09/2026 en `NovedadeswebPHP`: Laravel 12, PHP 8.2, Blade, jQuery, Bootstrap y MySQL. La biblioteca usa la sesión y el layout existentes. No requiere React, Next.js ni un servidor Node para funcionar.

## Acceso y funciones

- Menú: **GENERAL → Capacitaciones → Biblioteca Institucional → Ayuda**.
- Índice: `/biblioteca`. Cuatro colecciones: políticas, procedimientos, instructivos y descriptivos de puesto.
- Consulta y búsqueda por contenido, área, estado, revisión y colección; control documental, versiones e historial.
- Administración: crear, duplicar, editar borradores, previsualizar, publicar y retirar versiones. Una publicación conserva la versión anterior en el historial. No hay eliminación de documentos ni edición del contenido de una versión publicada.
- Revisión de descriptivos: guardar observaciones, validar, o validar y publicar. Nombre y área son explícitos; el comentario es opcional y se registra una resolución automática al confirmar.
- Competencias genéricas institucionales en cinco viñetas. Compromiso se presenta con viñetas. Registro de acciones contraíble.
- Exportación PDF y Word; descarga del original, extracción y huella SHA-256.
- Incorporación de DOCX/PDF con revisión previa: nuevo puesto, versión de un puesto, documento asociado o fuente complementaria. Un archivo ilegible puede conservarse como fuente complementaria.
- Sincronización manual de las 15 carpetas configuradas: detectar nuevos, modificados, faltantes o restaurados. Los nuevos contenidos quedan pendientes de validación; no reemplazan automáticamente una versión vigente. Los originales nunca se escriben.

Usuarios autenticados pueden consultar. Gestión, importación, revisión y publicación requieren el rol existente `Administrador/a`, configurable en `config/biblioteca.php`. Las comprobaciones se realizan también en el servidor. Las acciones registran el identificador y nombre del usuario.

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

La asociación automática con la nómina actual de puestos todavía requiere definir cuál es el catálogo institucional autoritativo y su clave de vinculación. La migración no inventa esa relación ni declara puestos faltantes sin ese catálogo.
