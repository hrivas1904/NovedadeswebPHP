# Ver como otro usuario

Disponible exclusivamente para cuentas activas con rol Administrador/a y usuario `ffernandez` o `mcardoner`.

## Uso

1. Elegir **Ver como otro usuario** junto al nombre del perfil o en la cabecera de la biblioteca.
2. Buscar por nombre, usuario o legajo, seleccionar una cuenta activa y confirmar.
3. Consultar Mi descriptivo, la biblioteca y las constancias accesibles a esa persona.
4. La barra **Estás viendo como…** identifica la persona consultada y la cuenta original. **Volver a mi usuario** finaliza la consulta. **Cambiar usuario** vuelve al selector.

La consulta sólo afecta a la biblioteca y se comparte entre pestañas de la misma sesión. Las demás áreas siguen usando la cuenta original. No permite firmas, aprobaciones, edición ni otras escrituras de biblioteca. Se ocultan los formularios de firma y accesos administrativos; el servidor bloquea todas las operaciones de escritura, incluso desde formularios abiertos anteriormente. La navegación administrativa requiere salir del modo consulta.

## Identidad y registro

El servidor conserva el usuario autenticado de la sesión. Sólo durante la petición de lectura de biblioteca utiliza la identidad seleccionada, y restaura la identidad original al finalizar. No inicia sesión como la otra persona ni conoce o modifica su contraseña. Cada petición verifica el permiso de la cuenta original y que la cuenta seleccionada permanezca activa. Una sesión de consulta no puede reutilizarse al ingresar con otra cuenta.

Inicio, salida explícita, cambio de persona y cierre por inactividad del usuario seleccionado o pérdida de permiso generan eventos `iniciar_vista_usuario` / `finalizar_vista_usuario` en `bib_events`, con actor real, usuario consultado y fecha. No se modifica la base de usuarios. No requiere migraciones adicionales.

Pruebas Feature y Chrome con usuarios ficticios verifican selección y búsqueda, permisos, restauración de identidad, aislamiento fuera de biblioteca, bloqueo de firmas y cambios, cambio de usuario, revocación y registro de eventos.
