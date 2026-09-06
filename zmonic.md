# Casos de Uso Corregidos y Prompts para Diagramas UML

Este documento corrige sutilmente los casos de uso para que coincidan mejor con el sistema implementado. La correccion principal es que el "Sistema" no debe aparecer como actor en la mayoria de diagramas UML, porque el sistema es el limite donde ocurren los casos de uso. Solo deben aparecer actores externos como Administrador, Vendedor, Almacenero, Cliente comprador y, cuando corresponda, Agente inteligente.

## Criterio para los diagramas

| Elemento | Recomendacion |
| --- | --- |
| Sistema | Dibujarlo como el rectangulo contenedor del diagrama. |
| Actores | Dibujar solo personas, roles externos o servicios que interactuan con el sistema. |
| Include | Usarlo para pasos obligatorios como validar datos, validar stock, calcular total o exportar PDF. |
| Extend | Usarlo para casos opcionales o alternativos como rechazar solicitud, registrar error o pago pendiente. |
| Redaccion | Usar nombres cortos dentro de los ovalos: "Registrar venta", "Validar stock", "Exportar PDF". |

---

## CU-01 Gestion de Usuarios y Roles

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Gestionar usuarios y roles |
| Actor principal | Administrador |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Administrar el acceso de los usuarios segun el rol que cumplen dentro del sistema. |
| Precondiciones | El administrador debe haber iniciado sesion. |
| Flujo principal | 1. El administrador ingresa al modulo de usuarios. 2. Consulta usuarios activos e inactivos. 3. Registra o edita datos del usuario. 4. Asigna un rol: administrador, vendedor, almacen o comprador. 5. Activa, desactiva o elimina logicamente el usuario. 6. El sistema valida correo, usuario y datos obligatorios. |
| Flujos alternativos | Si el correo o nombre de usuario ya existe, el sistema no permite guardar. Si faltan datos obligatorios, solicita correccion. |
| Postcondiciones | El usuario queda creado, actualizado, activado o desactivado con su rol correspondiente. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Gestion de usuarios y roles" de un sistema web PIL. Actor externo: Administrador. Dentro del limite del sistema incluir: consultar usuarios, registrar usuario, editar usuario, asignar rol, activar usuario, desactivar usuario, eliminar usuario, generar reporte PDF, validar correo y nombre de usuario. No dibujar al Sistema como actor; el Sistema debe ser el rectangulo contenedor. Usa include desde registrar y editar hacia validar datos. Estilo academico, fondo blanco, lineas negras, texto legible. Titulo: Figura 13: Diagrama de casos de uso gestion de usuarios.
```

---

## CU-02 Registro de Clientes Externos

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Registrar cliente externo |
| Actor principal | Cliente visitante |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Permitir que un cliente externo cree una cuenta de comprador para acceder al catalogo y realizar compras. |
| Precondiciones | El cliente no debe estar registrado previamente con el mismo correo o nombre de usuario. |
| Flujo principal | 1. El cliente accede al formulario de registro. 2. Ingresa nombre, correo, usuario, contrasena, NIT y direccion. 3. El sistema valida los datos ingresados. 4. El sistema crea la cuenta con rol comprador. 5. El cliente queda habilitado para iniciar sesion. |
| Flujos alternativos | Si el correo o usuario ya existe, el sistema solicita corregirlo. Si la informacion es incompleta, no se crea la cuenta. |
| Postcondiciones | Se registra un nuevo comprador activo. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Registro de clientes externos" en una plataforma PIL. Actor externo: Cliente visitante. Dentro del limite del sistema incluir: acceder al registro, completar formulario, registrar NIT, registrar direccion de entrega, validar datos, crear cuenta comprador, iniciar sesion. No dibujar al Sistema como actor. Usa include entre completar formulario y validar datos, y entre crear cuenta comprador y asignar rol comprador. Estilo academico, fondo blanco, texto claro. Titulo: Figura 14: Diagrama de casos de uso registro de clientes externos.
```

---

## CU-03 Gestion de Productos y Catalogacion

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Gestionar productos |
| Actor principal | Administrador |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Mantener actualizado el catalogo de productos, precios, imagenes, categorias y limites de stock. |
| Precondiciones | El administrador debe estar autenticado y las categorias deben existir en el sistema. |
| Flujo principal | 1. El administrador ingresa al modulo de productos. 2. Consulta productos activos e inactivos. 3. Registra o edita nombre, SKU, descripcion, categoria, imagen, precio publico, precio institucional y limites de stock. 4. Define si el producto esta activo o inactivo. 5. El sistema valida SKU unico, categoria existente y limites de stock. 6. El sistema guarda el cambio y registra auditoria. |
| Flujos alternativos | Si el SKU ya existe o el stock maximo es menor al minimo, el sistema impide guardar. Si se elimina un producto, el sistema lo archiva mediante baja logica. |
| Postcondiciones | El producto queda registrado, actualizado, activado, desactivado o archivado. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Gestion de productos y catalogacion" de un sistema PIL. Actor externo: Administrador. Dentro del limite del sistema incluir: consultar productos activos, consultar productos inactivos, registrar producto, editar producto, asignar categoria existente, cargar imagen, definir precio publico, definir precio institucional, definir stock minimo, definir stock maximo, activar producto, desactivar producto, archivar producto, generar reporte PDF, validar SKU, validar limites de stock. No dibujar al Sistema como actor ni al Almacenero en este diagrama. Usa include desde registrar y editar hacia validar SKU, validar categoria y validar limites de stock. Estilo formal para tesis, fondo blanco, texto legible. Titulo: Figura 15: Diagrama de casos de uso gestion de productos y catalogacion.
```

---

## CU-04 Control de Lotes y Vencimientos

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Gestionar lotes de productos |
| Actor principal | Almacenero |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Controlar lotes, cantidades, vencimientos y movimientos de inventario en el almacen de La Paz. |
| Precondiciones | Deben existir productos registrados y el almacen de La Paz debe estar configurado. |
| Flujo principal | 1. El almacenero ingresa al modulo de lotes. 2. Consulta lotes por producto. 3. Filtra por producto, fecha o lotes proximos a vencer. 4. Registra un lote con codigo, cantidad y vencimiento. 5. El sistema valida cantidad y limite maximo. 6. El sistema actualiza inventario y genera movimiento de ingreso. |
| Flujos alternativos | Si se requiere corregir stock, el almacenero ajusta la cantidad del lote. Si la cantidad supera el maximo permitido, el sistema rechaza el registro. |
| Postcondiciones | El lote queda registrado o ajustado y el inventario se mantiene sincronizado. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Control de lotes y vencimientos" de un sistema PIL. Actor externo: Almacenero. Dentro del limite del sistema incluir: consultar lotes, filtrar por producto, filtrar por vencimiento, consultar lotes proximos a vencer, registrar lote en La Paz, ingresar codigo de lote, ingresar cantidad, registrar fecha de vencimiento, ajustar lote, validar stock maximo, actualizar inventario, generar movimiento de ingreso, generar movimiento de ajuste, generar reporte PDF. No dibujar al Sistema como actor. Usa include para validar stock y actualizar inventario. Estilo academico, fondo blanco, UML claro. Titulo: Figura 16: Diagrama de casos de uso control de lotes y vencimiento.
```

---

## CU-05 Realizacion de Compras

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Realizar compra de productos |
| Actor principal | Cliente comprador |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Permitir que el comprador seleccione productos del catalogo y genere un pedido. |
| Precondiciones | El comprador debe haber iniciado sesion y el carrito debe contener productos validos. |
| Flujo principal | 1. El comprador consulta el catalogo. 2. Selecciona productos y cantidades. 3. Accede a la pantalla de pago. 4. El sistema valida carrito y stock disponible en La Paz. 5. El sistema registra el pedido. 6. El sistema descuenta stock por metodo FEFO. 7. El sistema genera el recibo. |
| Flujos alternativos | Si el carrito esta vacio, el sistema no procesa el pedido. Si no hay stock suficiente, informa la disponibilidad real. |
| Postcondiciones | El pedido queda procesado y el recibo queda disponible. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Realizacion de compras" en una plataforma PIL. Actor externo: Cliente comprador. Dentro del limite del sistema incluir: consultar catalogo, seleccionar producto, agregar al carrito, modificar cantidad, ir a pago, validar carrito, validar stock en La Paz, descontar stock por FEFO, generar pedido, generar recibo. No dibujar al Sistema como actor. Usa include para validar carrito, validar stock, descontar stock y generar recibo. Estilo limpio, profesional, academico, fondo blanco. Titulo: Figura 17: Diagrama de casos de uso realizacion de pedidos.
```

---

## CU-06 Procesamiento de Pago de Pedido

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Procesar pago de pedido |
| Actor principal | Cliente comprador |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Registrar el metodo de pago seleccionado y completar el pedido dentro del sistema. |
| Precondiciones | Debe existir un carrito con productos y cantidades validas. |
| Flujo principal | 1. El comprador revisa el resumen del pedido. 2. Selecciona metodo de pago: efectivo, QR o tarjeta. 3. El sistema valida el carrito y el stock. 4. El sistema registra el pedido. 5. Si el metodo no es efectivo, el pago queda completado. 6. Si el metodo es efectivo, el pago queda pendiente. 7. El sistema genera numero de recibo. |
| Flujos alternativos | Si el carrito esta vacio o el stock no alcanza, el sistema rechaza el proceso. |
| Postcondiciones | El pedido queda registrado con estado de pago completado o pendiente. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Procesamiento de pago de pedido" de una tienda web PIL. Actor externo: Cliente comprador. Dentro del limite del sistema incluir: revisar carrito, seleccionar metodo de pago, elegir efectivo, elegir QR, elegir tarjeta, validar carrito, validar stock, registrar pedido, marcar pago completado, marcar pago pendiente para efectivo, generar numero de recibo, consultar recibo, descargar recibo. No incluir pasarela de pagos externa porque el sistema registra el metodo internamente. No dibujar al Sistema como actor. Usa include para validar carrito, validar stock y generar recibo; usa extend para pago en efectivo pendiente. Estilo academico, fondo blanco, texto claro. Titulo: Figura 18: Diagrama de casos de uso procesamiento de pagos.
```

---

## CU-07 Registro de Ventas Manuales

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Registrar venta manual |
| Actor principal | Vendedor |
| Actores secundarios | Almacenero |
| Tipo | Primario |
| Proposito | Registrar ventas presenciales o comerciales realizadas por vendedores a empresas, tiendas o compradores minoristas. |
| Precondiciones | El vendedor debe estar autenticado. Deben existir productos y destinatarios registrados. |
| Flujo principal | 1. El vendedor ingresa al modulo de ventas. 2. Selecciona tipo de venta: empresa institucional, tienda de barrio o comprador minorista. 3. Busca productos por SKU. 4. Ingresa cantidades. 5. El sistema calcula total y valida stock por lotes. 6. El vendedor registra metodo de pago, monto recibido si corresponde y estado de entrega. 7. El sistema registra la venta y descuenta inventario por FEFO. |
| Flujos alternativos | Si no existe stock suficiente, el sistema impide registrar la venta. Si el pago es en efectivo, calcula el cambio. El almacenero puede actualizar posteriormente el estado de entrega desde recepciones. |
| Postcondiciones | La venta queda registrada y el inventario actualizado. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Registro de ventas manuales" de un sistema PIL. Actores externos: Vendedor y Almacenero. Dentro del limite del sistema incluir: seleccionar tipo de venta, seleccionar empresa institucional, seleccionar tienda de barrio, seleccionar comprador minorista, buscar producto por SKU, agregar productos, calcular total, registrar metodo de pago, registrar monto recibido, calcular cambio, validar stock por lotes, descontar inventario por FEFO, registrar venta, actualizar estado de entrega. El Vendedor se relaciona con registrar venta; el Almacenero solo con actualizar estado de entrega. No dibujar al Sistema como actor. Usa include para buscar producto, validar stock, calcular total y descontar inventario. Estilo profesional para tesis, fondo blanco. Titulo: Figura 19: Diagrama de casos de uso registro de ventas.
```

---

## CU-08 Gestion de Cotizaciones

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Emitir cotizacion |
| Actor principal | Vendedor |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Generar propuestas comerciales para empresas, tiendas o compradores antes de concretar una venta. |
| Precondiciones | Deben existir productos activos y un destinatario registrado. |
| Flujo principal | 1. El vendedor ingresa al modulo de cotizaciones. 2. Selecciona tipo de cliente y destinatario. 3. Busca productos por SKU. 4. Agrega productos, cantidades y precios. 5. El sistema calcula el total. 6. El vendedor define vigencia, estado y observaciones. 7. El sistema guarda la cotizacion y permite descargar el PDF. |
| Flujos alternativos | Si no se agregan productos, el sistema rechaza la cotizacion. Si el producto no existe, la busqueda muestra un error. |
| Postcondiciones | La cotizacion queda registrada con estado borrador, enviada, aceptada o rechazada. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Gestion de cotizaciones" de un sistema PIL. Actor externo: Vendedor. Dentro del limite del sistema incluir: consultar cotizaciones, crear cotizacion, seleccionar tipo de cliente, seleccionar empresa o comprador, buscar producto por SKU, agregar productos, calcular total, definir vigencia, registrar observaciones, definir estado, guardar cotizacion, descargar PDF. No dibujar al Sistema como actor. Usa include para buscar producto, calcular total y guardar cotizacion. Estilo academico, limpio, fondo blanco. Titulo: Figura 20: Diagrama de casos de uso gestion de cotizaciones.
```

---

## CU-09 Transferencia de Stock entre Almacenes

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Transferir stock entre almacenes |
| Actor principal | Administrador |
| Actores secundarios | Almacenero, Agente inteligente |
| Tipo | Primario |
| Proposito | Registrar traspasos desde Santa Cruz o Cochabamba hacia el almacen de La Paz para sostener disponibilidad. |
| Precondiciones | Deben existir los almacenes SCZ, CBA y LPZ, productos registrados y cantidades solicitadas. |
| Flujo principal | 1. El administrador ingresa al modulo de traspasos. 2. Selecciona almacen origen: Santa Cruz o Cochabamba. 3. El sistema fija La Paz como destino. 4. Agrega productos y cantidades solicitadas. 5. Registra el traspaso en estado pendiente o en transito. 6. El almacenero consulta el detalle desde el modulo de almacen. 7. El almacenero actualiza estado, cantidades recibidas, lote recibido, vencimiento y cantidad danada. |
| Flujos alternativos | Si el traspaso viene de una solicitud aprobada del agente, se crea automaticamente. Si hay diferencias en recepcion, el almacenero registra la observacion. |
| Postcondiciones | El traspaso queda registrado y su recepcion puede actualizar inventario y lotes. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Traspasos de stock entre almacenes" de un sistema PIL. Actores externos: Administrador, Almacenero y Agente inteligente. Dentro del limite del sistema incluir: consultar traspasos, crear traspaso, crear traspaso desde solicitud aprobada, seleccionar origen Santa Cruz o Cochabamba, fijar destino La Paz, buscar producto por SKU, agregar productos, definir cantidad solicitada, registrar estado pendiente o en transito, generar reporte PDF, consultar detalle en almacen, actualizar estado, registrar cantidad recibida, registrar lote recibido, registrar vencimiento, registrar cantidad danada. No dibujar al Sistema como actor. Usa include para buscar producto y registrar items; usa extend para registrar diferencias o danos. Estilo formal, fondo blanco. Titulo: Figura 21: Diagrama de casos de uso traspasos.
```

---

## CU-10 Aprobacion de Sugerencias del Agente Inteligente

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Aprobar sugerencias de reposicion |
| Actor principal | Administrador |
| Actores secundarios | Agente inteligente |
| Tipo | Primario |
| Proposito | Revisar recomendaciones automaticas de reposicion generadas a partir de ventas, stock, lotes, traspasos y demanda proyectada. |
| Precondiciones | Debe existir historial de ventas, inventario actualizado y productos con umbrales de stock. |
| Flujo principal | 1. El administrador ejecuta o consulta el agente de reposicion. 2. El agente analiza ventas, stock por lotes, traspasos recientes y demanda proyectada. 3. El agente genera solicitudes de reposicion con prioridad, cantidad y motivo. 4. El administrador revisa cada solicitud. 5. El administrador aprueba o rechaza la sugerencia. 6. Si se aprueba, el sistema crea un traspaso interno hacia La Paz. 7. El sistema registra la decision en auditoria. |
| Flujos alternativos | Si la sugerencia no corresponde, el administrador la rechaza con motivo. Si el agente no responde, el sistema muestra el error. |
| Postcondiciones | La solicitud queda aprobada, rechazada o pendiente; si fue aprobada, queda asociada a un traspaso. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Agente inteligente de reposicion" de un sistema PIL. Actores externos: Administrador y Agente inteligente. Dentro del limite del sistema incluir: ejecutar agente, consultar estado del agente, analizar ventas historicas, analizar stock por lotes, analizar traspasos recientes, proyectar demanda, generar solicitud de reposicion, revisar solicitud, aprobar solicitud, rechazar solicitud, crear traspaso interno hacia La Paz, generar reporte PDF, registrar decision. No dibujar al Sistema como actor. Usa include para analisis de ventas, stock y traspasos; usa extend para aprobar o rechazar. Estilo academico, profesional, fondo blanco. Titulo: Figura 22: Diagrama de casos de uso agente inteligente.
```

---

## CU-11 Registro de Productos Danados o Mermas

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Registrar productos danados |
| Actor principal | Almacenero |
| Actores secundarios | Ninguno |
| Tipo | Primario |
| Proposito | Registrar bajas de inventario por productos danados o no aptos para venta. |
| Precondiciones | Debe existir un lote con stock disponible. |
| Flujo principal | 1. El almacenero ingresa al modulo de danos. 2. Busca el producto o lote. 3. Selecciona el lote afectado. 4. Registra cantidad danada y comentario. 5. El sistema valida que la cantidad no supere el stock del lote. 6. El sistema descuenta inventario y registra el movimiento de baja. |
| Flujos alternativos | Si la cantidad supera el stock, el sistema rechaza la operacion. Si el lote no existe, la busqueda no permite continuar. |
| Postcondiciones | La merma queda registrada y el stock actualizado. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Registro de productos danados y mermas" de un sistema PIL. Actor externo: Almacenero. Dentro del limite del sistema incluir: consultar mermas, buscar producto o lote, seleccionar lote, registrar cantidad danada, registrar comentario, validar cantidad disponible, descontar stock, generar movimiento de baja, consultar historial. No dibujar al Sistema como actor. Usa include para validar cantidad y descontar stock. Estilo limpio, academico, fondo blanco. Titulo: Figura 23: Diagrama de casos de uso registro de danos.
```

---

## CU-12 Generacion de Reportes Maestros

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Generar reportes maestros |
| Actor principal | Administrador |
| Actores secundarios | Vendedor, Almacenero |
| Tipo | Primario |
| Proposito | Obtener reportes PDF de los modulos principales del sistema segun el rol autorizado. |
| Precondiciones | Deben existir datos registrados en el modulo consultado. |
| Flujo principal | 1. El usuario autorizado ingresa al modulo correspondiente. 2. Selecciona reporte de usuarios, clientes, productos, lotes, ventas, traspasos, cotizaciones, auditoria, backups o agente. 3. Define filtros disponibles segun el modulo. 4. El sistema consulta y consolida la informacion. 5. El usuario descarga el reporte en PDF. |
| Flujos alternativos | Si no existen datos para los filtros, el sistema genera el reporte sin registros o muestra mensaje informativo. |
| Postcondiciones | El reporte queda disponible para descarga. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Generacion de reportes maestros" de un sistema PIL. Actores externos: Administrador, Vendedor y Almacenero. Dentro del limite del sistema incluir: generar reporte de usuarios, generar reporte de clientes, generar reporte de productos, generar reporte de lotes, generar reporte de ventas, generar reporte de traspasos, generar reporte de cotizaciones, generar reporte de auditoria, generar reporte de backups, generar reporte de agente de reposicion, aplicar filtros, consultar datos consolidados, exportar PDF. No dibujar al Sistema como actor. Usa include para aplicar filtros, consultar datos y exportar PDF. Estilo profesional para documento academico, fondo blanco. Titulo: Figura 24: Diagrama de casos de uso reportes.
```

---

## CU-13 Respaldo de Base de Datos

| Elemento | Detalle |
| --- | --- |
| Caso de uso | Gestionar respaldos de base de datos |
| Actor principal | Administrador |
| Actores secundarios | Ninguno |
| Tipo | Soporte |
| Proposito | Crear, programar, descargar y eliminar respaldos de la base de datos. |
| Precondiciones | El administrador debe tener permisos de acceso al modulo de backups. |
| Flujo principal | 1. El administrador ingresa al modulo de backups. 2. Consulta el historial de respaldos. 3. Genera un respaldo manual o actualiza la programacion. 4. El sistema ejecuta el respaldo. 5. El sistema registra nombre, tamano y fecha del archivo. 6. El administrador descarga o elimina respaldos anteriores. |
| Flujos alternativos | Si ocurre un error de conexion o permisos, el sistema registra el fallo y muestra el mensaje correspondiente. |
| Postcondiciones | El respaldo queda almacenado, descargado, programado o eliminado segun la accion realizada. |

**Prompt para generar imagen:**

```text
Crea un diagrama UML de casos de uso en espanol para "Gestion de respaldos de base de datos" de un sistema web PIL. Actor externo: Administrador. Dentro del limite del sistema incluir: consultar backups, generar respaldo manual, actualizar programacion de respaldo, ejecutar copia de seguridad, almacenar archivo, descargar respaldo, eliminar respaldo, registrar error de respaldo. No dibujar al Sistema como actor. Usa include para ejecutar copia y almacenar archivo; usa extend para registrar error. Estilo academico, fondo blanco, texto legible. Titulo: Figura 25: Diagrama de casos de uso backups.
```

---

## Prompt General para Todas las Figuras

```text
Genera un diagrama UML de casos de uso en espanol con estilo academico, fondo blanco, lineas negras limpias, actores tipo stick figure, limite del sistema en un rectangulo, nombres claros y legibles, sin decoracion innecesaria. No dibujar al sistema como actor; el sistema debe ser el rectangulo contenedor. Usar include y extend solo cuando correspondan. Mantener buena separacion entre actores y casos de uso. No usar colores fuertes. No agregar explicaciones fuera del diagrama.
```

## Nota de Ajuste

Los diagramas quedan mas correctos si separas las acciones internas del sistema de los actores externos. Por ejemplo, "validar stock" puede ser un caso de uso incluido, pero no necesita un actor llamado Sistema. Esto hace que los diagramas sean mas tecnicos y mas defendibles.
