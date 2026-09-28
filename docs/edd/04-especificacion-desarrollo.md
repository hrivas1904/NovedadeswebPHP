# Etapa 4. Especificación para continuar el desarrollo

**Punto de partida actualizado:** ya están implementadas población, asignaciones, competencias funcionales por área/persona y Mi equipo por asignación. Consultar [el incremento vigente](06-poblacion-y-competencias.md) antes de aplicar los apartados históricos que todavía las describen como pendientes. Para las futuras respuestas, resolver las funcionales desde esta configuración y congelarlas al abrir, sin duplicarlas desde el editor inicial de instrumentos.

> Antes de continuar, aplicar el [alcance simplificado de la primera versión](05-alcance-primera-version.md), definido posteriormente por el usuario. Tiene prioridad sobre los requisitos más amplios de este encargo: competencias genéricas/específicas, asignaciones, autoevaluación enviada al responsable, evaluación y cierre. No implementar el conjunto completo de ampliaciones como requisito del primer desarrollo.

Este documento conserva el encargo integral y distingue lo implementado de lo pendiente. La continuación de la etapa 2 ya incorpora configuración persistente de períodos e instrumentos. Las operaciones de carga de evaluaciones, cálculo y cierre siguen pendientes. Los documentos de las etapas 1–3 y el código del módulo son el punto de partida.

## Encargo

Desarrollar la persistencia y el funcionamiento del módulo de Evaluación de Desempeño del Hospital Privado Tres Cerritos dentro del Sistema de Gestión RRHH existente, en Laravel 12 / PHP 8.2. Continuar sobre la estructura de EDD ya creada, respetando la rama de trabajo. Presentar cada incremento sin commit para su revisión en VS Code y esperar el OK explícito del usuario antes de crear el commit correspondiente. No hacer merge en `main`.

Mantener todas las vistas EDD en `resources/views/edd`, todo JavaScript propio en `public/js/edd` y las acciones HTTP en `app/Http/Controllers/RRHH/EddController.php`. Colocar las reglas de negocio y los cálculos en servicios EDD, validación en Form Requests y autorización sobre registros en Policies o Gates. Usar las rutas `rrhh.edd.*`, la autenticación, el layout y las convenciones del proyecto. Evitar consultas y cambios de estado desde Blade o JavaScript.

Leer primero:

1. `docs/edd/01-modelo-hp3c.md`: reglas del modelo y escala confirmada **1 a 4**.
2. `docs/edd/02-diseno-funcional.md`: pantallas, acceso y transiciones.
3. `docs/edd/03-modelo-informacion.md`: entidades, integridad e historia.
4. `app/Enums/Edd/EstadoEvaluacion.php`, `config/edd.php`, el controlador, `app/Services/Edd/ConfiguracionEdd.php`, las vistas y ambas pruebas `tests/Feature/Edd*Test.php`.
5. `App\Services\Biblioteca\Assignments` y `Acceptances`: identidades, puestos y asignación de descriptivos.

## Punto de partida

Ya existen diez rutas GET y seis escrituras de configuración. Períodos e instrumentos se guardan en seis tablas `edd_`, con criterios, ponderaciones, escala 1–4 por versión, publicación inmutable, nuevas versiones, auditoría transaccional y revisión optimista. Las rutas concretas están en la etapa 2. Reutilizar este servicio y extenderlo sin recrear las tablas ni reemplazar versiones publicadas.

Población y evaluadores, la lista vacía de equipo, los modelos de evaluación/autoevaluación, devolución/cierre y los reportes conservan la estructura inicial. Todavía faltan sus migraciones, consultas por asignación, escrituras, transiciones, cálculos y constancias. La configuración textual de condiciones/excepciones deberá convertirse en reglas operativas explícitas antes de habilitar períodos. No hay períodos abiertos ni resultados de ejemplo cargados por esta entrega.

## Requisitos por incremento

### A. Configuración y datos

- Verificar las claves institucionales y crear migraciones aditivas según la etapa 3, sin modificar la nómina ni los descriptivos existentes.
- Crear y editar períodos en borrador, con fechas, corte, reglas, autoevaluación y excepciones. Rechazar rangos de fechas incompatibles.
- Crear instrumentos versionados con los cinco bloques previstos: competencias generales, específicas, desempeño, conductas y objetivos. Permitir habilitar bloques según el instrumento, validando pesos activos que sumen 100 %.
- Mantener la escala 1–4 confirmada para 2026. Los nombres y comportamientos quedan versionados junto con el instrumento.
- Publicar solo instrumentos completos. Impedir cambios retroactivos a versiones publicadas o usadas.
- Incorporar población activa verificada, con inclusión/exclusión motivada, instrumento aplicable y copia de contexto laboral/descriptivo. No tratar la categoría salarial como puesto.
- Asignar explícitamente un evaluador a cada colaborador. Admitir funciones jefe, coordinador, responsable y gerente sin inventar equivalencias con roles de acceso.
- Detectar falta de usuario activo, vínculo de legajo ambiguo, autoasignación del evaluador, instrumento faltante y doble asignación vigente. No abrir el período hasta resolver las condiciones requeridas.
- Al habilitar, generar una única evaluación por participante incluido y congelar sus ítems efectivos, objetivos, escala y pesos. La operación debe ser atómica y tolerar reintentos.

### B. Equipo y autorización

- Al ingresar, RRHH ve el resumen, el evaluador su equipo asignado y el colaborador su autoevaluación.
- Evolucionar `edd.evaluar`: permitir el trabajo por asignación vigente, incluso si la función de responsable no coincide con los nombres de roles globales existentes.
- Consultar siempre las asignaciones de la cuenta autenticada. Las jefaturas no eligen personas de toda la nómina. Filtros, exportaciones y detalle conservan el mismo alcance.
- Autorizar nuevamente cada recurso y mutación. No confiar en IDs recibidos desde una lista ni en campos `evaluador`, `legajo`, `rol` o `estado` enviados por el cliente.
- En Mi equipo mostrar nombre/legajo, puesto/servicio, estado, última actualización, acción disponible y avance del equipo. No inferir asignaciones solamente de `area_id`.
- Calcular avance sobre la población autorizada completa, no sobre la página o búsqueda visible. Identificar el equipo y período reales.
- Al reasignar, conservar respuestas, responsables anteriores y eventos, y revocar de inmediato al responsable anterior.

### C. Evaluación y autoevaluación

- Guardar puntajes y comentarios por ítem, separados por tipo de respuesta. Guardar también comentarios generales, fortalezas, aspectos a desarrollar y acciones con responsable y plazo.
- Guardar borradores incompletos sin convertir vacíos en cero. Rechazar puntajes fuera de la escala, ítems de otro instrumento/evaluación y “No aplica” sin habilitación o motivo.
- Usar los estados principales del enum: pendiente, borrador, completada por evaluador, devolución realizada y cerrada. Solo los endpoints de transición pueden cambiar el estado.
- Mantener pendiente/borrador/enviada de autoevaluación por separado. Verificar cuenta activa, legajo unívoco y participación propia; no exponer respuestas ajenas.
- Definir y aplicar la visibilidad de respuestas según las reglas explícitas del período. La autoevaluación no se promedia en el resultado oficial salvo una futura decisión expresamente autorizada.
- Calcular resultados en servidor, con pesos versionados, precisión decimal y redondeo de presentación. No copiar sin validar las fórmulas de la planilla 2025.
- Exigir revisión optimista para guardar, completar y modificar. En 409 conservar lo escrito en pantalla y permitir revisar/recargar sin sobrescribir la versión remota.

### D. Devolución, cierre y seguimiento

- Registrar entrevista solo después de completar la evaluación, con fecha, participantes, acuerdos y plan de acción requerido por las reglas.
- Definir la evidencia de devolución/recepción antes de activar el cierre. No asumir que registrar la reunión constituye una firma digital del colaborador.
- Cerrar solo desde devolución realizada, con validación completa, actor y fecha. Crear una instantánea inmutable y un único cierre por ciclo.
- Rechazar modificaciones directas de respuestas en una evaluación cerrada.
- Permitir reapertura excepcional a RRHH con motivo, auditoría y conservación del cierre previo. Invalidar la devolución/constancia cuando deba repetirse.
- Mantener el seguimiento de acciones separado del contenido histórico cerrado.

### E. Reportes e historial

- Mostrar previstas, pendientes, en proceso y finalizadas. En proceso agrupa borrador, completada por evaluador y devolución realizada; finalizadas cuenta solo cerradas.
- Calcular promedio hospital y por área desde resultados cerrados comparables, mostrando denominador y escala `/ 4` para 2026.
- Agregar fortalezas y oportunidades por competencia, con cantidad de respuestas y cobertura; no mezclar competencias distintas por parecido de nombre.
- Consultar historial del colaborador conservando instrumento, puesto, descriptivo, escala, devolución y acciones del período original.
- No contar dos veces una evaluación por tener reasignaciones, varias acciones, dos tipos de respuesta o múltiples cierres históricos.
- Mostrar “Sin datos” cuando corresponda, sin inventar promedios ni presentar ejemplos como información institucional.

## Contratos HTTP previstos

Crear/editar períodos y publicar instrumentos ya están registrados, junto con crear/editar instrumentos y crear versiones (ver la etapa 2). Las demás rutas de esta tabla son propuestas para la implementación pendiente. Mantener los GET existentes cuando sea posible y usar parámetros por ID para los recursos persistentes.

| Operación | Método y ruta relativa a `/rrhh/edd` | Autorización y validación |
| --- | --- | --- |
| Crear período | `POST /periodos` | RRHH, reglas iniciales válidas. |
| Editar período | `PATCH /periodos/{periodo}` | RRHH, borrador, revisión. |
| Publicar instrumento | `POST /periodos/{periodo}/instrumentos/{instrumento}/publicar` | RRHH, pertenencia al período, revisión, criterios/escala/pesos completos y confirmación. |
| Preparar población | `POST /periodos/{periodo}/poblacion` | RRHH, fuente verificada, corte y excepciones. |
| Asignar responsable | `PUT /participantes/{participante}/evaluador` | RRHH, misma participación, revisión. |
| Habilitar período | `POST /periodos/{periodo}/habilitar` | RRHH, transacción e idempotencia. |
| Ver evaluación | `GET /evaluaciones/{evaluacion}` | Responsable vigente o RRHH, alcance explícito. |
| Guardar borrador | `PATCH /evaluaciones/{evaluacion}/borrador` | Responsable vigente, revisión, período/estado editables. |
| Completar | `POST /evaluaciones/{evaluacion}/completar` | Responsable vigente, respuestas y reglas completas. |
| Autoevaluar | `PATCH /evaluaciones/{evaluacion}/autoevaluacion` | Colaborador vinculado, revisión y plazo. |
| Enviar autoevaluación | `POST /evaluaciones/{evaluacion}/autoevaluacion/enviar` | Colaborador vinculado, validación completa. |
| Registrar entrevista | `POST /evaluaciones/{evaluacion}/devolucion` | Responsable vigente, estado previo y revisión. |
| Cerrar | `POST /evaluaciones/{evaluacion}/cerrar` | Responsable vigente, devolución y condiciones cumplidas. |
| Reabrir | `POST /evaluaciones/{evaluacion}/reabrir` | RRHH, motivo y revisión. |
| Consultar historial | `GET /historial/{legajo}` | RRHH; una consulta propia futura deberá validar identidad y visibilidad. |

Todas las escrituras usan autenticación y CSRF, ignoran campos no permitidos y validan la pertenencia de recursos relacionados. Respuestas JSON coherentes: datos persistidos/revisión al guardar; 422 para validación; 409 para conflicto; 403 para falta de permiso de módulo y 404 para objetos ajenos. Los mensajes no deben filtrar contenido de otras evaluaciones.

## Criterios de aceptación

1. Un coordinador con cinco personas asignadas ve exactamente esas cinco, sin poder ampliar el conjunto mediante URL, búsqueda, ID o parámetros manipulados.
2. Dos evaluaciones cerradas de cinco previstas muestran 40 % de avance. Completar como evaluador no incrementa “Finalizadas”. Los tres contadores de estado suman las previstas incluidas.
3. Un responsable reasignado pierde acceso; el nuevo responsable conserva el trabajo previo y el historial identifica quién hizo cada cambio.
4. Un colaborador solo lee/escribe su propia autoevaluación. Dos cuentas activas con el mismo legajo requieren resolución, no elección arbitraria.
5. El borrador se recupera después de recargar o volver a iniciar sesión. Un guardado fallido nunca se presenta como exitoso.
6. No se salta de pendiente a cerrada; no se registra entrevista antes de completar; no se cierra sin devolución ni reglas satisfechas.
7. En un caso de prueba con bloques al 50 % y promedios 2 y 4, el resultado es 3 sobre 4. Esto es una prueba de fórmula, no una ponderación aprobada para 2026.
8. Vacíos, ceros inválidos, valores fuera de rango, ítems ajenos y “No aplica” no permitido se rechazan sin escrituras parciales. Pesos inconsistentes impiden publicar.
9. La autoevaluación no cambia el puntaje oficial del evaluador. Comentarios y autoría permanecen separados.
10. Repetir la solicitud de cierre no crea otro resultado. Dos sesiones con la misma revisión no pueden sobrescribirse.
11. Cambiar la versión del instrumento, el servicio o el descriptivo después del cierre no modifica el resultado histórico.
12. Los reportes no duplican evaluaciones al unir respuestas, acciones, asignaciones o ciclos; distinguen ausencia de resultados de un valor numérico.
13. Un usuario inactivo pierde acceso al módulo. Ocultar enlaces no reemplaza la autorización HTTP.
14. Pruebas en una base aislada verifican migraciones, permisos, aislamiento de equipos, estados, concurrencia, cálculo e historia. Se revisan los flujos completos en navegador y su uso móvil.

## Decisiones funcionales pendientes

Las ponderaciones, fechas, población definitiva, rúbricas por puesto, autoevaluación obligatoria, visibilidad de respuestas, calibración, firma/constancia, regla de “No aplica” y umbral de seguimiento se completan con RRHH. Implementar su configuración y validación sin inventar valores. Se puede avanzar con pruebas ficticias identificadas y aisladas, manteniendo bloqueada la apertura de un período incompleto.

## Entrega esperada del siguiente desarrollo

Código, migraciones aditivas, pruebas útiles y documentación actualizada, con commits por incremento únicamente después del OK explícito del usuario. Informar qué funciona de extremo a extremo, qué decisiones permanecen pendientes y qué comandos se verificaron. Dejar los cambios sin preparar en el índice para que puedan revisarse en VS Code. No importar ni publicar los resultados personales de la planilla de referencia. Mantener `main` sin merge.
