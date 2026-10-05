# Etapa 2. Pantallas, permisos y flujo

**Actualización vigente:** [población, evaluadores y competencias funcionales por área/persona](06-poblacion-y-competencias.md) ya están implementados. Esa guía amplía las rutas de esta página, reemplaza los estados vacíos y habilita Mi equipo por asignación real. Verificación conjunta: 33 pruebas y 443 verificaciones; las cifras inferiores describen el incremento anterior.

> La próxima implementación seguirá el [alcance sencillo de la primera versión](05-alcance-primera-version.md). Este documento describe también las pantallas ya creadas y el diseño inicial más amplio; la devolución separada y los bloques adicionales no son requisitos para el primer circuito funcional.

## Estructura implementada

Todas las vistas están en `resources/views/edd`, las interacciones propias en `public/js/edd` y las acciones HTTP en `app/Http/Controllers/RRHH/EddController.php`. `edd.js` conserva los filtros de equipo y `configuracion.js` administra los formularios persistentes. Las validaciones están en `app/Http/Requests/Edd` y las transacciones en `app/Services/Edd/ConfiguracionEdd.php`. Se aprovechan el layout y Bootstrap del sistema, con estilos limitados al contenedor EDD y adaptación a dispositivos pequeños.

`GET /rrhh/edd` resuelve el destino por perfil. El enlace **Evaluación de desempeño** aparece en Recursos Humanos para las cuentas activas.

| Perfil actual | Destino inicial | Alcance de esta estructura |
| --- | --- | --- |
| Administrador/a | Resumen RRHH | Configuración, equipo, formulario, autoevaluación y reportes. |
| Coordinador/a, Coordinador/a L2 | Mi equipo | Equipo, estructura del formulario y autoevaluación propia. |
| Otros perfiles activos | Mi autoevaluación | Estructura de su autoevaluación. |
| Cuenta inactiva | Acceso denegado | Ninguna ruta EDD. |
| Sin sesión | Inicio de sesión | Ninguna ruta EDD. |

Se usan los roles que ya existen en el proyecto. Jefe, responsable y gerente son funciones del evaluador: no se crean roles globales con esos nombres. Cuando se implemente la persistencia, una asignación explícita vigente permitirá acceder como evaluador aunque su perfil global tenga otro nombre. El acceso a registros deberá comprobar esa asignación en cada lectura y escritura, nunca solo el rol ni la pertenencia a un área.

## Rutas y pantallas

Todos los nombres tienen el prefijo `rrhh.edd.` y todas las rutas requieren `auth` y `can:edd.acceder`.

| Ruta relativa a `/rrhh/edd` | Nombre | Pantalla | Permiso adicional |
| --- | --- | --- | --- |
| `/` | `index` | Redirección según perfil | — |
| `/resumen` | `resumen` | Indicadores generales y por área | `edd.administrar` |
| `/configuracion` | `configuracion` | Período, fechas y reglas | `edd.administrar` |
| `/configuracion/poblacion` | `configuracion.poblacion` | Colaboradores al corte y excepciones | `edd.administrar` |
| `/configuracion/evaluadores` | `configuracion.evaluadores` | Asignaciones de responsables | `edd.administrar` |
| `/configuracion/instrumento` | `configuracion.instrumento` | Bloques, pesos y escala 1–4 | `edd.administrar` |
| `/equipo` | `equipo` | Personas asignadas y estados | `edd.evaluar` |
| `/modelo-evaluacion` | `evaluacion.modelo` | Estructura de puntajes, devolución y cierre | `edd.evaluar` |
| `/autoevaluacion` | `autoevaluacion` | Estructura de respuestas del colaborador | — |
| `/reportes` | `reportes` | Filtros y estructura del historial | `edd.administrar` |

Los Gates están definidos en `AppServiceProvider`. La restricción se aplica en servidor, además de ocultar enlaces. Las páginas de configuración consultan períodos e instrumentos guardados; todavía no consultan nóminas ni resultados. Las cuentas sin legajo reciben una indicación de vinculación en su autoevaluación.

## Configuración persistente

Período y reglas permite crear un período con código único, nombre y año, completar las fechas y guardar las reglas de autoevaluación, condiciones de cierre y excepciones. El selector conserva el período al navegar por las secciones de configuración. Guardar mantiene el estado **borrador** y no habilita evaluaciones. Las fechas pueden quedar pendientes; si están informadas, su orden debe ser válido. Las condiciones y excepciones se almacenan como texto, con su aplicación operativa pendiente.

Instrumento permite crear la primera versión, agregar/editar/quitar criterios dentro de los cinco bloques, deshacer la última eliminación antes del guardado, definir pesos relativos y obligatoriedad por criterio y activar/desactivar bloques. Desactivar conserva los criterios pero excluye su ponderación. El código de competencia es opcional y queda disponible para relacionar resultados equivalentes más adelante; no asigna automáticamente un puesto.

Publicar exige al menos un bloque activo, al menos un criterio por bloque activo, ponderaciones positivas y suma de 100 %. La operación utiliza el borrador guardado: si hay cambios locales pendientes, primero hay que guardarlos. La confirmación fija esa versión como **publicada**, con fecha y actor; el período continúa en borrador. Una nueva versión copia escala, criterios y pesos en otro borrador, conservando la versión publicada. Solo se admite un borrador por código de instrumento a la vez.

Las seis operaciones registradas requieren `auth`, `edd.acceder`, `edd.administrar` y CSRF:

| Método y ruta relativa a `/rrhh/edd` | Nombre | Condiciones |
| --- | --- | --- |
| `POST /periodos` | `periodos.store` | Identificación válida y código único. |
| `PATCH /periodos/{periodo}` | `periodos.update` | Período en borrador y revisión vigente. |
| `POST /periodos/{periodo}/instrumentos` | `instrumentos.store` | Período en borrador; código de instrumento nuevo. |
| `PATCH /periodos/{periodo}/instrumentos/{instrumento}` | `instrumentos.update` | Pertenencia al período, borrador y revisión vigente. |
| `POST /periodos/{periodo}/instrumentos/{instrumento}/publicar` | `instrumentos.publicar` | Borrador guardado completo, revisión y confirmación. |
| `POST /periodos/{periodo}/instrumentos/{instrumento}/versiones` | `instrumentos.versiones` | Origen publicado y vigente; sin otro borrador del mismo código. |

El servicio asigna actor y estado en servidor, bloquea la cabecera y registra el evento en la misma transacción que los datos. Responde 422 por validación, 409 por revisión/estado incompatible, 404 por recursos no vinculados y 403 por falta de permiso. Los formularios conservan lo escrito ante errores; un conflicto permite abrir la versión guardada en otra pestaña. Un fallo de conexión o respuesta incierta no se muestra como éxito ni se reintenta automáticamente. Sin JavaScript no se habilita el envío.

Si no se ha instalado la migración, las páginas siguen siendo consultables y muestran un aviso de instalación pendiente, con el guardado deshabilitado. No se crean tablas desde solicitudes web.

## Pantalla del evaluador

Presenta su equipo directamente, con colaborador, legajo, puesto/servicio, estado y última actualización. No hay selector para buscar evaluados en toda la institución. La búsqueda por nombre/legajo y estado opera únicamente sobre las filas autorizadas ya recibidas; nunca debe usarse como control de permisos.

La colección inicial está vacía. Los futuros datos deberán proceder de asignaciones del usuario autenticado, con consultas y paginación en servidor al crecer el volumen. El avance del equipo se calculará sobre toda la asignación autorizada, independientemente del filtro visual.

## Operaciones previstas para la implementación posterior

| Operación | Estado anterior | Estado posterior | Condiciones mínimas |
| --- | --- | --- | --- |
| Guardar primera respuesta | Pendiente | En borrador | Asignación vigente, período habilitado, valores ingresados válidos. |
| Guardar borrador | En borrador | En borrador | Admite respuestas incompletas. Conserva revisión y autor. |
| Completar evaluación | En borrador | Completada por evaluador | Todos los ítems obligatorios, pesos válidos, comentario general, fortalezas y desarrollo completos. |
| Registrar devolución | Completada por evaluador | Devolución realizada | Entrevista con fecha, participantes y acuerdos; plan de acción cuando corresponda. |
| Cerrar | Devolución realizada | Cerrada | Reglas del período satisfechas y confirmación del responsable. |
| Reabrir excepcionalmente | Cerrada | En borrador | Solo RRHH, motivo y nueva revisión; conserva el cierre anterior e invalida hitos que deban repetirse. |

Las transiciones de evaluación de esta tabla todavía no tienen endpoints. Los controles de los modelos de evaluación, autoevaluación, entrevista y cierre están deshabilitados: no simulan guardados ni usan almacenamiento del navegador. El enum central describe los cinco estados; su validación transaccional se implementará al desarrollar respuestas y cierres.

## Autoevaluación y devolución

La autoevaluación tiene estado propio y formulario separado. El acceso futuro se resolverá por `users.legajo`, verificando el vínculo activo y unívoco con la persona evaluada. No se acepta un legajo elegido desde el navegador.

El evaluador completará puntajes y comentarios propios. La visibilidad de respuestas entre colaborador y evaluador se configurará para el período antes de abrirlo. Hasta entonces no se publican respuestas ajenas.

La devolución guarda fecha, participantes, acuerdos, fortalezas, aspectos a desarrollar y acciones con responsable/plazo. Registrar una entrevista no equivale a firmar en nombre del colaborador ni a cerrar automáticamente.

## Reportes e historial

Se reservan resumen hospitalario, avance por área, resultados por competencia e historial por colaborador. Las definiciones de indicadores están en la etapa 1. Se muestran guiones cuando aún no hay datos; los números ilustrativos del pedido no se incorporan al sistema. El historial conservará escala y versión del instrumento de cada resultado.

## Verificación de esta etapa

`tests/Feature/EddStructureTest.php` comprueba autenticación, destinos por perfil, permisos por URL, cuenta inactiva, navegación de jefaturas, ausencia de acceso administrativo desde autoevaluación y renderizado de las pantallas con SQLite en memoria sin tablas institucionales. PHP y JavaScript se verifican también por sintaxis.

`tests/Feature/EddConfigurationTest.php` añade persistencia de períodos/criterios/ponderaciones, permisos sobre las seis escrituras, publicación y versionado, fechas inválidas, criterios ajenos, revisiones obsoletas, formularios truncados y reversión ante un fallo de auditoría. La migración y su reversión se prueban exclusivamente en SQLite aislado.

Resultado conjunto: 20 pruebas correctas y 272 verificaciones. Se comprobó en navegador el flujo crear período → crear instrumento → guardar criterios → publicar → crear nueva versión, y un conflicto entre dos sesiones sin pérdida del texto local. Revisión en escritorio y móvil a 390 px sin desbordamiento horizontal, usando una instancia aislada con identidad ficticia. Las verificaciones anteriores de resumen e ingreso directo de jefatura se conservan en las pruebas de estructura.
