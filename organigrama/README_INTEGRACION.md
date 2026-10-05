# Paquete de integracion - Organigrama HP3C

## Objetivo

Integrar el organigrama multivista dentro del proyecto existente **SISTEMA DE GESTION RRHH**, respetando su arquitectura Laravel/PHP, su autenticacion, permisos, modelos, estilos, rutas y convenciones.

Este paquete es una referencia funcional y visual. El archivo HTML actual funciona de manera autonoma, con datos embebidos y `localStorage`. En la aplicacion final, la estructura institucional debe persistirse en la base de datos del sistema.

## Contenido

- `01_PROTOTIPO/organigrama-hp3c-multivista.html`: prototipo funcional completo.
- `02_DATOS/organigrama-base.json`: base actual con 65 posiciones y sus relaciones.
- `03_REFERENCIAS/vista-horizontal.png`: referencia de la vista horizontal por jerarquias.
- `03_REFERENCIAS/vista-vertical.png`: referencia de la vista vertical desplegable.
- `03_REFERENCIAS/vista-arbol.png`: referencia de la vista arbol institucional.
- `03_REFERENCIAS/referencia-arbol-original.png`: captura que define la composicion visual esperada del arbol.
- `PROMPT_PARA_CODEX.md`: instruccion lista para pegar en Codex.
- `CONTRATO_FUNCIONAL_Y_DATOS.md`: comportamiento y estructura de datos que deben conservarse.
- `CRITERIOS_DE_ACEPTACION.md`: pruebas para considerar terminada la integracion.

## Regla principal de arquitectura

No crear una aplicacion nueva, un micrositio separado ni una segunda base de datos. No integrar el prototipo mediante iframe. Codex debe convertirlo en un modulo nativo del sistema y reutilizar las entidades existentes de puestos, areas, colaboradores, usuarios y permisos.

Si faltan campos o relaciones, debe ampliar el modelo existente con la menor cantidad posible de migraciones. No debe crear tablas paralelas que representen nuevamente los mismos puestos o colaboradores.

## Secuencia recomendada

1. Copiar este paquete dentro del repositorio, preferentemente en una carpeta temporal de referencia, por ejemplo `DOCUMENTACION/REFERENCIAS/ORGANIGRAMA`.
2. Abrir Codex en la raiz del proyecto `SISTEMA DE GESTION RRHH`.
3. Pegar el contenido de `PROMPT_PARA_CODEX.md`.
4. Permitir que Codex inspeccione primero la arquitectura real del repositorio.
5. Revisar el resumen de archivos modificados, migraciones y pruebas ejecutadas.
6. Validar la integracion con `CRITERIOS_DE_ACEPTACION.md`.

## Nota importante sobre los datos

Los identificadores del JSON son estables y no deben reemplazarse por el titulo del puesto. Hay titulos repetidos, por ejemplo `Enfermero` y `Cadete`, que representan posiciones diferentes. Las dependencias compartidas se modelan como multiples relaciones hacia una misma posicion, no como posiciones duplicadas.
