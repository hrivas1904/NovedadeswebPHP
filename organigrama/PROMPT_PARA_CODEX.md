# Prompt para Codex: integrar organigrama multivista

Estas trabajando dentro del repositorio existente **SISTEMA DE GESTION RRHH**. Integra el organigrama incluido en este paquete como un modulo nativo de la aplicacion.

## Archivos de referencia

- `01_PROTOTIPO/organigrama-hp3c-multivista.html`
- `02_DATOS/organigrama-base.json`
- `03_REFERENCIAS/vista-horizontal.png`
- `03_REFERENCIAS/vista-vertical.png`
- `03_REFERENCIAS/vista-arbol.png`
- `03_REFERENCIAS/referencia-arbol-original.png`
- `CONTRATO_FUNCIONAL_Y_DATOS.md`
- `CRITERIOS_DE_ACEPTACION.md`

## Instruccion de arquitectura

Primero inspecciona el repositorio y determina con precision:

- version de Laravel y PHP;
- estructura de modulos, rutas, controladores, servicios y modelos;
- stack real de frontend: Blade, Livewire, Alpine, Inertia, Vue, React u otro;
- layout principal, menu y sistema de estilos;
- autenticacion, roles y permisos;
- entidades existentes de puestos, areas, colaboradores, usuarios y dependencias;
- convenciones de migraciones, seeders, factories, auditoria y pruebas.

Luego implementa la integracion siguiendo esas convenciones. No crees una aplicacion nueva, no agregues un segundo framework de frontend, no uses iframe y no dejes el HTML como una pagina aislada. No reemplaces autenticacion, permisos ni modelos existentes.

## Reutilizacion obligatoria

Reutiliza las entidades existentes de puestos, areas, colaboradores, usuarios y permisos. Evita tablas paralelas y datos duplicados. Si el modelo actual no soporta multiples dependencias o tipos de relacion, amplialo con la menor cantidad posible de cambios y explica la decision.

No hagas matching de posiciones solo por titulo: existen nombres repetidos como `Enfermero` y `Cadete`. Usa el `id` estable del JSON como clave externa de importacion o crea un mecanismo equivalente. Para responsables, vincula nombres con colaboradores existentes solo cuando la coincidencia sea segura; no crees personas ficticias automaticamente. Genera un reporte de coincidencias y pendientes.

## Comportamiento requerido

Implementa tres vistas sobre una unica fuente de datos:

1. `Vista horizontal por jerarquias`.
2. `Vista vertical`.
3. `Vista arbol`, siguiendo la composicion de `referencia-arbol-original.png`.

Conserva busqueda, despliegue y contraccion global e individual, zoom, centrado, ajuste a pantalla, arrastre, responsive, alta y edicion de posiciones, eliminacion controlada, dependencias multiples, tipos `direct`, `support` y `shared`, orden de hijos y actualizacion inmediata de las tres vistas.

La base de datos debe ser la fuente de verdad. Usa `localStorage` solamente para preferencias visuales del usuario, nunca para la estructura institucional.

## Reglas de negocio

- Impedir ciclos jerarquicos.
- Impedir dependencias duplicadas.
- Permitir multiples superiores para una posicion.
- Representar una dependencia compartida con un solo nodo y multiples relaciones.
- No eliminar una posicion con hijos sin reasignarlos primero.
- Ejecutar cambios relacionados dentro de transacciones.
- Mantener orden estable de las ramas.
- Restringir configuracion a los permisos existentes de RRHH o administracion.
- Mantener lectura separada de edicion.

## Importacion inicial

Crea un importador, comando o seeder idempotente para `organigrama-base.json`. Una segunda ejecucion no debe duplicar posiciones ni relaciones. Antes de insertar, mapea los datos contra las entidades actuales y conserva un identificador externo estable.

## Integracion de navegacion

Agrega el acceso al organigrama dentro del menu y layout existentes, en la ubicacion coherente con los otros modulos de RRHH. No modifiques el AppShell general salvo lo estrictamente necesario.

## Pruebas

Agrega pruebas para:

- permisos de lectura y edicion;
- importacion idempotente;
- preservacion de las 65 posiciones;
- titulos repetidos con IDs diferentes;
- multiples dependencias;
- relaciones compartidas;
- bloqueo de ciclos;
- bloqueo de relaciones duplicadas;
- alta, edicion y baja controlada;
- persistencia luego de recargar;
- endpoints o acciones principales.

Ejecuta tambien la suite existente y no ignores regresiones.

## Forma de trabajo y entrega

1. Inspecciona la arquitectura real antes de modificar.
2. Implementa en cambios pequenos y coherentes.
3. No borres datos existentes ni hagas migraciones destructivas.
4. Documenta cualquier supuesto.
5. Al finalizar, entrega:
   - resumen de la arquitectura encontrada;
   - decisiones de integracion;
   - lista de archivos creados y modificados;
   - migraciones y comandos a ejecutar;
   - resultado de la importacion y responsables no vinculados;
   - pruebas ejecutadas y resultado;
   - pasos exactos para verificar las tres vistas.

Considera obligatorios `CONTRATO_FUNCIONAL_Y_DATOS.md` y `CRITERIOS_DE_ACEPTACION.md`. Si alguna convencion del repositorio entra en conflicto con el prototipo, conserva la funcionalidad pero adapta la implementacion al patron ya existente y explica la diferencia.
