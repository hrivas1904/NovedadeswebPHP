# Contrato funcional y de datos

## 1. Vistas requeridas

El modulo debe ofrecer tres vistas intercambiables sobre la misma estructura:

1. **Vista horizontal por jerarquias**: niveles de izquierda a derecha, nodos desplegables, lineas de dependencia, arrastre y zoom.
2. **Vista vertical**: lectura desplegable similar a la primera version del organigrama.
3. **Vista arbol**: organigrama institucional de arriba hacia abajo, con Directorio en la parte superior y ramas jerarquicas como en las capturas de referencia.

Cambiar de vista no debe cambiar los datos ni crear copias de posiciones.

## 2. Funciones que deben conservarse

- Busqueda por nombre de posicion o responsable.
- Desplegar todo y contraer.
- Desplegar o contraer una rama individual.
- Zoom, centrado, ajuste a pantalla y arrastre en las vistas horizontal y arbol.
- Diseno responsive para escritorio y celular.
- Edicion de nombre de posicion y responsable.
- Alta y baja controlada de posiciones.
- Alta, baja y cambio de dependencias.
- Tipos de relacion: `direct`, `support`, `shared`.
- Orden manual de hijos dentro de una dependencia.
- Validacion para impedir ciclos.
- Validacion para impedir repetir la misma dependencia.
- Una posicion puede tener mas de un superior.
- Toda modificacion debe verse inmediatamente en las tres vistas.

## 3. Modelo del prototipo

El archivo `organigrama-base.json` contiene:

```json
{
  "version": 2,
  "rootId": "directorio",
  "nodes": [
    {
      "id": "director-medico",
      "title": "Director Medico",
      "person": "Martin Baldi",
      "expanded": false
    }
  ],
  "edges": [
    {
      "parentId": "directorio",
      "childId": "director-medico",
      "relation": "direct",
      "order": 0
    }
  ]
}
```

`expanded` es estado visual y no debe formar parte obligatoria del dato institucional persistente.

## 4. Adaptacion al modelo existente

Codex debe inspeccionar y reutilizar primero las entidades existentes del sistema. Mapeo conceptual esperado:

- `node.id`: identificador estable de importacion o clave externa.
- `node.title`: puesto o posicion institucional.
- `node.person`: responsable visible; debe vincularse a colaboradores existentes cuando sea posible.
- `edge.parentId` y `edge.childId`: relacion jerarquica entre posiciones.
- `edge.relation`: tipo de vinculo directo, apoyo o compartido.
- `edge.order`: orden visual dentro de la rama.

No hacer matching unicamente por titulo, porque hay titulos repetidos. Para responsables, buscar coincidencias seguras con colaboradores existentes y dejar registro de los nombres no resueltos; no crear colaboradores ficticios de manera automatica.

## 5. Persistencia y API

La base de datos del sistema debe ser la fuente de verdad. `localStorage` puede utilizarse solamente para preferencias personales de interfaz, por ejemplo vista elegida, zoom o ramas abiertas.

Las operaciones de edicion deben ejecutarse en el servidor, respetar validaciones y usar transacciones. La importacion inicial debe ser idempotente: volver a ejecutarla no debe duplicar posiciones ni relaciones.

## 6. Seguridad y trazabilidad

- Lectura: segun la politica ya existente del sistema.
- Edicion: solo usuarios autorizados de RRHH o administradores, reutilizando el esquema de roles y permisos existente.
- Registrar creacion, modificacion y eliminacion con los mecanismos de auditoria ya presentes, si existen.
- No exponer acciones de configuracion a usuarios con permiso de solo lectura.

## 7. Integracion visual

Reutilizar el AppShell, navegacion, paleta, componentes y convenciones actuales. No incorporar otro framework de frontend si el repositorio ya utiliza Blade, Livewire, Alpine, Inertia, Vue o React. Traducir el prototipo al stack real en lugar de copiarlo como pagina aislada.
