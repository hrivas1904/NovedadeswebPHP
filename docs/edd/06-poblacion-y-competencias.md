# Población, evaluadores y competencias funcionales

## Cómo usarlo en EDD 2026

1. Abrir **Configuración → Población a evaluar** y seleccionar el período existente. Buscar por nombre/legajo o filtrar por área, marcar colaboradores y presionar **Agregar seleccionados**. La selección corresponde a la página visible.
2. Abrir **Evaluadores**, marcar las personas incluidas, elegir una cuenta activa y su función y presionar **Asignar a seleccionados**. También se puede asignar desde la ficha individual.
3. Abrir **Competencias por área**, elegir el área real del sistema y una lista inicial de RRHH. Presionar **Usar esta lista**, revisar el texto y **Guardar competencias del área**.
4. Desde **Población a evaluar → Configurar**, seleccionar **Personalizar para este colaborador**, ajustar la lista y guardar. La personalización afecta solamente a esa persona en ese período.

El catálogo contiene las 40 competencias funcionales compartidas por el usuario: cinco para Enfermería, Administración, Facturación, Mantenimiento, Sistemas, Limpieza, Recepción y Quirófano. Las descripciones se conservan completas; en Limpieza se retiraron únicamente los corchetes de formato del mensaje.

Existen áreas homónimas de Administración y Recepción no figura como área independiente. Se muestran nombre e identificador para asociar cada lista explícitamente. No se crean, renombran ni fusionan áreas institucionales. Las competencias genéricas todavía deben definirse.

## Herencia e independencia

- **Base del área:** utiliza las competencias de su área EDD en ese período. Mientras está en borrador, los cambios en la base se reflejan en las personas que la heredan.
- **Personalizada:** conserva una lista propia. Cambiar la base del área no la modifica.
- **Volver a la base:** al guardar retira la personalización y utiliza la base vigente. El contenido anterior queda en auditoría.
- **Cambiar de área EDD:** modifica únicamente la organización dentro del período. Si hereda la base, la vista se actualiza después de guardar.

Cada competencia ocupa una línea y puede incluir una descripción después de dos puntos. El ajuste visual de renglones no separa competencias. No se necesita un descriptivo de puesto.

## Participantes y cuentas

La población se selecciona desde la nómina activa actual, sin incorporar automáticamente áreas completas. Se guarda una copia del nombre y contexto al agregar a la persona; la fecha de corte no simula una reconstrucción histórica.

Se muestra si cada legajo tiene una cuenta activa, ninguna o más de una. Se permite preparar la población con ese vínculo pendiente, pero debe resolverse antes de habilitar la autoevaluación. No se crean cuentas ni se modifican roles globales.

Excluir requiere motivo, conserva el participante y finaliza la asignación vigente. Reintentar una incorporación no duplica participantes ni deshace una exclusión. Para reincluir se usa la ficha individual y se elige nuevamente el responsable.

La función de evaluador surge de la asignación EDD. Una cuenta activa asignada puede entrar a **Mi equipo**, aunque su perfil global no se llame Coordinador/a. Solo consulta sus asignaciones vigentes e incluidas. Una reasignación revoca el acceso a esa persona para el responsable anterior y conserva el historial. No se permite evaluarse a sí mismo.

## Persistencia y permisos

La migración `2026_09_26_100000_create_edd_population_tables.php` agrega:

Se instaló únicamente esta migración en `desarrollo`. La verificación posterior confirmó las tres tablas vacías y el período existente `EDD-2026` conservado en borrador. No se cargaron asignaciones ni participantes institucionales automáticamente.

| Tabla | Contenido |
| --- | --- |
| `edd_area_competencias` | Competencias por período/área, revisión y autor. |
| `edd_participantes` | Legajo, área EDD, contexto, inclusión/exclusión, lista personal opcional y revisión. |
| `edd_asignaciones` | Responsable, función y vigencia; una sola asignación actual por participante. |

Se verificaron los tipos institucionales: legajo y área son enteros con signo; `users.id` es bigint sin signo. Las relaciones internas EDD y la autoría tienen claves foráneas con borrado restringido. Legajo/área se validan contra la institución sin cambiar sus índices ni esquema.

Las escrituras requieren cuenta activa con `edd.administrar`, autenticación, CSRF y período en borrador. Se comprueba la pertenencia de cada participante. Las operaciones son transaccionales y auditadas; una revisión obsoleta devuelve 409 sin sobrescribir. Una asignación múltiple se revierte completa si alguna persona presenta un conflicto.

| Operación | Ruta relativa a `/rrhh/edd` |
| --- | --- |
| Ver competencias del área | `GET /configuracion/competencias?periodo=…&area=…` |
| Configurar persona | `GET /periodos/{periodo}/participantes/{participante}` |
| Incorporar selección | `POST /periodos/{periodo}/poblacion` |
| Guardar base de área | `PATCH /periodos/{periodo}/competencias/{area}` |
| Guardar ficha individual | `PATCH /periodos/{periodo}/participantes/{participante}` |
| Asignar responsable | `POST /periodos/{periodo}/evaluadores` |

Código principal: `PoblacionEdd`, `GuardarPoblacionRequest`, acciones en `RRHH/EddController`, vistas en `resources/views/edd/configuracion`, `public/js/edd/poblacion.js` y catálogo en `config/edd_competencias.php`.

## Validación

`php artisan test --compact --filter=Edd`: **33 pruebas correctas, 443 verificaciones** en SQLite aislado. Cubren selección/reintentos, legajos inválidos/inactivos/duplicados, herencia/personalización, ocho listas completas, conflictos, reasignaciones, exclusiones, recursos de otro período, permisos y reversión ante fallos de auditoría. También acceso a Mi equipo por asignación, aislamiento y revocación al reasignar.

En navegador se incorporaron dos personas ficticias, se guardó la base de Administración, se asignó un responsable y se personalizó una lista individual. Los valores persistieron al recargar. La ficha se revisó en escritorio y a 390 px sin desbordamiento horizontal. Las pruebas no cargaron personas ni asignaciones ficticias en la base institucional.

## Próximo paso

Estas funciones completan la preparación de población, competencias funcionales y responsables. El envío de autoevaluaciones, las respuestas del jefe y el cierre todavía no están habilitados. Mi equipo muestra asignaciones configuradas y aclara esa situación.

Antes de abrir el circuito se deben definir las competencias genéricas y congelar las competencias efectivas de cada persona, heredadas o personalizadas. Las funcionales se resolverán desde esta configuración, sin depender de un descriptivo pendiente ni duplicar el bloque específico del editor inicial de instrumentos. Se mantiene el recorrido simple autoevaluación → jefe/coordinador → cierre.

Cambios sin commit para revisión en VS Code y OK explícito del usuario. Sin merge en main.
