# Criterios de aceptacion

La integracion se considera terminada cuando se cumplen todos los puntos siguientes.

## Arquitectura

- El organigrama es un modulo nativo dentro de `SISTEMA DE GESTION RRHH`.
- Usa el layout, autenticacion, permisos y convenciones del proyecto.
- No usa iframe ni una aplicacion paralela.
- No duplica tablas o entidades ya existentes para puestos, areas o colaboradores.

## Datos

- Se importan las 65 posiciones del JSON sin duplicados.
- Los titulos repetidos mantienen posiciones independientes mediante sus identificadores estables.
- Las relaciones `direct`, `support` y `shared` se conservan.
- Una dependencia compartida se representa con un unico nodo y multiples vinculos.
- La importacion es idempotente.
- Los responsables se vinculan a colaboradores cuando existe una coincidencia confiable y se informa cualquier caso no resuelto.

## Interfaz

- Existen las tres vistas: horizontal por jerarquias, vertical y arbol.
- Cambiar de vista conserva la misma estructura.
- La vista arbol mantiene la logica visual de la captura de referencia.
- Funcionan busqueda, desplegar, contraer, zoom, centrar, ajustar y arrastrar donde corresponda.
- La interfaz es utilizable en escritorio y celular.
- La estructura no se corta ni se superpone de manera ilegible en resoluciones habituales.

## Edicion y reglas

- Un usuario autorizado puede crear, editar y eliminar posiciones.
- Puede asignar mas de una dependencia a una posicion.
- Puede elegir el tipo de relacion y el orden.
- El sistema impide ciclos y relaciones duplicadas.
- No permite eliminar una posicion con dependencias hijas sin resolverlas antes.
- Los cambios persisten despues de cerrar sesion o recargar la pagina.
- Un usuario de solo lectura no ve ni puede ejecutar acciones de configuracion.

## Calidad

- Existen pruebas automaticas para permisos, importacion idempotente, ciclos, relaciones duplicadas y operaciones principales.
- Codex informa migraciones, rutas, modelos, controladores, servicios, componentes y pruebas modificadas.
- Las pruebas existentes del proyecto siguen pasando o se documenta cualquier falla previa no relacionada.
