# Etapa 2. Pantallas, permisos y flujo

## Estructura implementada

Todas las vistas están en `resources/views/edd`, las interacciones propias en `public/js/edd/edd.js` y las acciones de navegación en `app/Http/Controllers/RRHH/EddController.php`. Se aprovechan el layout y Bootstrap del sistema, con estilos limitados al contenedor EDD y adaptación a dispositivos pequeños.

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

Los Gates están definidos en `AppServiceProvider`. La restricción se aplica en servidor, además de ocultar enlaces. La estructura no consulta nóminas ni resultados. Las cuentas sin legajo reciben una indicación de vinculación en su autoevaluación.

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

Las transiciones de escritura todavía no tienen endpoints. Los controles de los modelos están deshabilitados: no simulan guardados ni usan almacenamiento del navegador. El enum central describe los cinco estados; la validación transaccional se implementará con la persistencia.

## Autoevaluación y devolución

La autoevaluación tiene estado propio y formulario separado. El acceso futuro se resolverá por `users.legajo`, verificando el vínculo activo y unívoco con la persona evaluada. No se acepta un legajo elegido desde el navegador.

El evaluador completará puntajes y comentarios propios. La visibilidad de respuestas entre colaborador y evaluador se configurará para el período antes de abrirlo. Hasta entonces no se publican respuestas ajenas.

La devolución guarda fecha, participantes, acuerdos, fortalezas, aspectos a desarrollar y acciones con responsable/plazo. Registrar una entrevista no equivale a firmar en nombre del colaborador ni a cerrar automáticamente.

## Reportes e historial

Se reservan resumen hospitalario, avance por área, resultados por competencia e historial por colaborador. Las definiciones de indicadores están en la etapa 1. Se muestran guiones cuando aún no hay datos; los números ilustrativos del pedido no se incorporan al sistema. El historial conservará escala y versión del instrumento de cada resultado.

## Verificación de esta etapa

`tests/Feature/EddStructureTest.php` comprueba autenticación, destinos por perfil, permisos por URL, cuenta inactiva, navegación de jefaturas, ausencia de acceso administrativo desde autoevaluación y renderizado de las pantallas con SQLite en memoria sin tablas institucionales. PHP y JavaScript se verifican también por sintaxis.

Resultado: 7 pruebas correctas y 137 verificaciones. Se revisaron en el navegador el resumen, la navegación de configuración, el instrumento a 390 px sin desbordamiento horizontal y el ingreso de una jefatura directamente a su equipo, usando una instancia local aislada con identidad ficticia.
