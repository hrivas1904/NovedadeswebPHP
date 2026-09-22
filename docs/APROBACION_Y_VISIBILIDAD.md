# Aprobaciones y visibilidad de la biblioteca

En Administración → Aprobaciones y visibilidad se muestran todas las versiones de políticas pendientes y los controles para mostrar u ocultar cada sección y cada política. Instructivos comienza oculto. Ocultar no elimina documentos ni aprobaciones; los administradores conservan acceso desde Administración.

## Aprobar una política

1. Un administrador prepara el documento o guarda un borrador.
2. Mariano Cardoner, usuario `mcardoner` activo y con rol `Administrador/a`, abre la versión pendiente desde el panel.
3. Revisa el contenido, marca su conformidad y presiona **Aprobar y publicar política**. La observación es opcional.
4. El servidor registra usuario, nombre, fecha UTC, versión y SHA-256 del contenido publicado. Genera automáticamente el instrumento de aprobación y utiliza la fecha actual si no se indicó vigencia. Una fecha futura impide publicar.

Sólo la versión vigente aprobada puede ser leída o descargada por los colaboradores, siempre que tanto la sección como el documento estén visibles. Los borradores, versiones históricas y documentos ocultos no se exponen por búsqueda, enlace directo o exportación. Las versiones nuevas necesitan otra aprobación; la vigente anterior sigue disponible mientras se prepara el borrador. Las aprobaciones y el historial anteriores se conservan.

Las declaraciones importadas de aprobación no sustituyen una aprobación registrada por Gerencia. Si una versión ya había sido publicada antes de este circuito, se debe crear un nuevo borrador para aprobarla. No se aprobaron políticas reales durante la implementación.

La identidad autorizada se configura en `biblioteca.policy_approver_username` (variable opcional `BIBLIOTECA_POLICY_APPROVER`, valor inicial `mcardoner`). No se concede este permiso a todos los administradores. Cambiar la configuración requiere un despliegue autorizado.

## Cambios de consulta

- Mi Descriptivo aparece junto a Mi legajo en el menú Colaboradores.
- Administración muestra todos los documentos filtrados en una página, incluidos los retirados por defecto.
- Los filtros de Administración, Buscar en la biblioteca y las colecciones se pueden contraer y expandir.
- El aviso del índice se muestra sólo cuando existe un descriptivo vigente asignado pendiente de aceptación; desaparece después de firmar y reaparece si cambia la versión o la asignación aplicable.

## Instalación y verificación

Aplicar únicamente la migración `2026_09_21_010000_add_biblioteca_policy_governance.php`, que crea `bib_visibility` y `bib_policy_approvals`. No modifica las cuentas, categorías, originales ni contenidos documentales existentes.

Pruebas aisladas verifican permisos, confirmación explícita, control de revisión concurrente, aprobaciones por versión, visibilidad por documento, enlaces y exportaciones, panel, listado completo y aviso de aceptación. Chrome verifica aprobación con una cuenta ficticia, ocultamiento efectivo, filtros plegables y menú. Los datos de prueba se guardan exclusivamente en SQLite separado.
