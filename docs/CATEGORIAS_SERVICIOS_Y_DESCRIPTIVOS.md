# Categorías, servicios y descriptivos — 21/09/2026

## Criterio acordado con RR. HH.

- Convenio: distingue colaboradores dentro y fuera de convenio.
- Categoría: encuadre convencional utilizado para liquidar los sueldos.
- Rol: función concreta, por ejemplo Enfermero de UTI o Mucamo de Quirófano.
- Servicio: referencia organizativa para determinar el descriptivo de puesto.

Una categoría de liquidación puede abarcar diferentes servicios y funciones. Por ejemplo, Administrativo de Primera puede trabajar en Facturación, Consultorios Externos o Telefonía. Por eso la categoría no determina por sí sola un descriptivo.

## Pantalla implementada

El acceso se denomina **Descriptivos y firmas** y conserva la dirección `/biblioteca/administracion/categorias-y-firmas`.

Se retiró la tabla de cobertura por categorías. La tabla de colaboradores muestra convenio, categoría actual, servicio, rol, descriptivo aplicable y aceptación. La búsqueda comprende todos esos datos y el legajo. Los indicadores cuentan colaboradores.

La selección de un descriptivo se guarda automáticamente por legajo mediante la autorización administrativa existente, CSRF y revisión de concurrencia. Mientras se guarda se bloquea la interacción para impedir que una búsqueda reemplace la fila en curso. Después se consultan nuevamente los datos guardados y se actualizan la tabla, los indicadores y las situaciones disponibles, conservando filtros y página cuando siga existiendo.

Un error no se presenta como éxito. Ante conflicto o respuesta incierta se vuelve a consultar la asignación. Si la consulta posterior falla, la selección permanece bloqueada hasta actualizar mediante Reintentar. Con JavaScript desactivado las asignaciones quedan deshabilitadas y se informa cómo habilitarlas.

Los descriptivos pueden completarse gradualmente: una asignación sin versión publicada y validada sigue pendiente de publicación. Las constancias anteriores se conservan.

## Alcance de esta etapa

El servicio y el rol se consultan como referencia para elegir el DP. La selección sigue siendo individual; todavía no se creó un vínculo automático de servicio a descriptivo ni se asignaron documentos en masa.

Se conserva la compatibilidad interna con asignaciones anteriores por categoría, sin exponer la tabla de administración por categoría. Si existe una asignación anterior, la opción vacía informa expresamente que vuelve a ese vínculo; si no existe, indica Sin descriptivo asignado. No se alteraron los tokens ni el contenido de las constancias históricas.

No se reemplazaron categorías ni se modificó la nómina. La sustitución queda pendiente del listado autorizado por persona, con legajo (o DNI), nombre, condición dentro/fuera de convenio y categoría convencional correcta. El cambio deberá contemplar las referencias en empleados y rol_empleados y el impacto en las aceptaciones.

## Verificación

La suite BibliotecaAcceptanceTest usa SQLite en memoria y datos ficticios: guardado JSON, altas/cambios/retiro de asignación individual, rechazo de revisión obsoleta, validación, permisos, búsquedas por servicio/rol/convenio, paginación y conservación de firmas. La prueba de Chrome utiliza una instancia local independiente con SQLite, nunca la base MySQL institucional.

Resultado: 20 pruebas y 277 verificaciones correctas. Chrome verificó firma propia, nueva publicación, constancia histórica, privacidad, permisos, guardado automático, persistencia después de recargar, filtros por servicio y rol, actualización de contadores, errores de validación, conflicto entre dos sesiones, recuperación ante fallo de actualización y presentación móvil, sin errores JavaScript.

Se comprobó la nueva consulta contra MySQL `desarrollo` mediante READ ONLY y ROLLBACK: 209 colaboradores activos, 23 categorías, ninguna asignación por categoría, 2 individuales y 1 aceptación. Estas cifras corresponden al corte de esta implementación y pueden variar con el uso de la aplicación; esta tarea no modificó esos registros.
