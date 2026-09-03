# ZNOORO - Guia completa del sistema

Este documento esta pensado para que cualquier IA o desarrollador entienda rapidamente como funciona el proyecto `final`, que hace cada modulo, cuales son los roles del sistema y que reglas se deben respetar al modificarlo.

## Resumen general

`final` es una aplicacion web Laravel 10 para gestion comercial, inventario, almacenes, ventas, cotizaciones, traspasos internos, pagos de comprador, reportes PDF, auditoria, respaldos y agentes inteligentes de prediccion/reposicion.

La aplicacion esta orientada a operaciones tipo Pil Andina. Maneja productos, categorias, clientes/empresas, compradores finales, vendedores, almacenes por ciudad, stock por lotes con vencimiento, ventas, transferencias entre almacenes y solicitudes generadas por IA.

## Stack tecnico

- Backend: Laravel 10, PHP 8.1 o superior.
- Base de datos: configurada por Laravel en `.env`; el codigo de backups asume SQL tipo MySQL/MariaDB porque usa `SHOW TABLES` y `SHOW CREATE TABLE`.
- Frontend: Blade + React 19 cargado con Vite.
- Bundler: Vite 5 con `laravel-vite-plugin` y `@vitejs/plugin-react`.
- Estilos principales: `public/landing/dashboard.css`, `public/landing/auth.css`, `public/landing/landing.css`, `resources/css/app.css`.
- Graficos: Chart.js y D3.
- PDFs: `spatie/laravel-pdf` y `spatie/browsershot`. En recibos de pago tambien hay fallback para Dompdf si existe.
- IA externa: servicio HTTP configurable con `AI_AGENT_URL`, por defecto `http://127.0.0.1:8010`.

## Comandos utiles

- Instalar dependencias PHP: `composer install`
- Instalar dependencias JS: `npm install`
- Copiar variables de entorno: `copy .env.example .env`
- Generar clave: `php artisan key:generate`
- Migrar base de datos: `php artisan migrate`
- Poblar datos demo: `php artisan db:seed`
- Levantar Laravel: `php artisan serve`
- Levantar Vite: `npm run dev`
- Compilar assets: `npm run build`
- Ejecutar tests: `php artisan test`
- Ejecutar backups programados pendientes: `php artisan backups:run-scheduled`
- Consultar agente de reposicion y crear solicitudes: `php artisan agent:replenishment-check`

## Estructura principal

- `routes/web.php`: rutas web de landing, login, registro, dashboards, CRUDs, reportes, pagos y agente de reposicion.
- `routes/api.php`: solo expone `/api/user` protegido por Sanctum; no hay API publica extensa.
- `app/Http/Controllers`: controladores por modulo.
- `app/Models`: modelos Eloquent y relaciones.
- `app/Services`: servicios de reportes, backups y agentes IA.
- `database/migrations`: esquema completo de tablas.
- `database/seeders`: roles, usuarios demo, productos, categorias, empresas, ciudades, almacenes y datos de ejemplo.
- `resources/views`: vistas Blade de dashboards, layout, reportes PDF y pantallas legacy.
- `resources/js/react`: paginas y componentes React usados por varias pantallas administrativas.
- `public/landing`: CSS/JS publico y assets de landing/dashboard.
- `ai_agent/predict.py`: script/agente Python relacionado con predicciones.

## Autenticacion y roles

La autenticacion esta en `AuthController`. El login usa email y password, registra logs de auditoria en intentos fallidos, login exitoso y logout. El registro publico crea usuarios con rol `Comprador`.

Roles sembrados por `RoleSeeder`:

- `Administrador`: control total del ecosistema, usuarios, reportes, productos, inventario, traspasos, agente IA, backups y logs.
- `Vendedor`: gestion comercial, clientes propios, ventas, cotizaciones, visitas y reportes personales.
- `Comprador`: cliente final que puede registrarse, ver catalogo, comprar y consultar recibos.
- `Almacen`: operacion de inventario, lotes, recepciones, traspasos y danos.

Usuarios demo de `UserSeeder`:

- Administrador: `admin@gmail.com` / `admin1234`
- Vendedor: `ventas@gmail.com` / `vendedor1234`
- Comprador: `comprador@gmail.com` / `comprador1234`
- Almacen: `almacen@gmail.com` / `almacen1234`

Despues del login, `AuthController::routeForRole` redirige asi:

- `Administrador` -> `dashboard.admin`
- `Vendedor` -> `dashboard.vendedor.home`
- `Almacen` -> `dashboard.almacen`
- Cualquier otro rol -> `dashboard.comprador`

## Rutas principales

- `/`: landing publica.
- `/login`: login React.
- `/register`: registro de comprador.
- `/predicciones`: pantalla de predicciones.
- `/dashboard/admin`: dashboard administrativo.
- `/dashboard/vendedor`: dashboard del vendedor.
- `/dashboard/comprador`: dashboard/catalogo del comprador.
- `/dashboard/almacen`: dashboard de almacen.
- `/dashboard/usuarios`: usuarios.
- `/dashboard/clientes`: empresas/clientes.
- `/dashboard/productos`: productos.
- `/dashboard/categorias`: categorias.
- `/dashboard/lotes`: lotes.
- `/dashboard/traspasos`: traspasos internos.
- `/dashboard/ventas`: ventas.
- `/dashboard/cotizaciones`: cotizaciones.
- `/dashboard/logs`: auditoria.
- `/dashboard/backups`: respaldos.
- `/dashboard/pago`: checkout/pago del comprador.
- `/admin/agente-reposicion`: agente inteligente de reposicion.

Importante: muchas rutas bajo `/dashboard` no estan dentro de middleware `auth` en `web.php`, aunque los controladores usan `request->user()` en varios puntos. Si se endurece seguridad, revisar rutas y permisos por rol antes de cambiar comportamiento.

## Frontend

El sistema mezcla Blade y React.

### React

El render React entra por `resources/js/react-pages.jsx`.

Funcionamiento:

1. La vista Blade `resources/views/react-page.blade.php` imprime un contenedor con `id="react-root"` y una clave de pagina.
2. `react-pages.jsx` lee esa clave.
3. `resources/js/react/pageRegistry.js` carga dinamicamente el componente correspondiente.
4. `AdminReact::page()` arma props comunes para pantallas administrativas: layout, sidebar, topbar, CSRF, flash messages, errores, old input y rutas.

Paginas React registradas:

- `landing`
- `login`
- `adminDashboard`
- `adminUsers`
- `adminCompanies`
- `adminProducts`
- `adminCategories`
- `adminLots`
- `adminTransfers`
- `adminSales`
- `adminQuotations`
- `adminLogs`
- `adminBackups`
- `adminAgentOverview`
- `adminAgentReplenishment`
- `adminResource`

### Blade

Siguen existiendo vistas Blade para varias areas, sobre todo vendedor, almacen, comprador, reportes y recibos:

- `resources/views/dashboard/*.blade.php`
- `resources/views/dashboard/vendedor/*.blade.php`
- `resources/views/layouts/sidebar*.blade.php`
- `resources/views/reports/*.blade.php`

## Modelo de datos principal

### Usuarios y roles

- `roles`: nombre y descripcion.
- `users`: nombre, email, username, password, `role_id`, soft deletes.
- `customers`: perfil de comprador final asociado 1 a 1 con `users`, con direccion y ciudad.

### Clientes, ciudades y empresas

- `companies`: empresas, tiendas o clientes institucionales. Incluye tipo, nombre, NIT, contacto, ciudad, propietario, `created_by`, soft deletes y URL de Google Maps.
- `cities`: ciudades normalizadas con nombre, codigo y departamento.

### Productos e inventario

- `categories`: categorias con soft deletes.
- `products`: categoria, nombre, descripcion, SKU unico, precio publico, precio institucional, activo/inactivo, imagen, stock minimo y stock maximo.
- `warehouses`: almacenes con nombre, codigo, direccion, ciudad, capacidad minima/maxima y soft deletes.
- `inventory`: cantidad agregada por producto y almacen; se sincroniza desde lotes.
- `product_lots`: lote por producto/almacen, codigo de lote, cantidad, vencimiento y umbral de seguridad.
- `product_lot_movements`: historial de movimientos por lote con tipo, cantidad positiva/negativa, usuario y nota.

Regla critica: `ProductLot` sincroniza automaticamente la tabla `inventory` al guardar o eliminar lotes. No actualizar `inventory.quantity` manualmente salvo que sepas exactamente por que.

### Ventas y cotizaciones

- `sales`: venta con empresa o comprador, vendedor, almacen, tipo, direccion/ciudad de entrega, estado, metodo de pago, monto recibido, cambio y total.
- `sale_items`: productos vendidos, cantidad, precio unitario y subtotal.
- `quotations`: cotizaciones con empresa o comprador, vendedor, fecha de validez, estado, total y notas.
- `quotation_items`: items de cotizacion.

Tipos de venta (`Sale::TYPES`):

- `empresa_institucional`
- `tienda_barrio`
- `comprador_minorista`

Estados de venta (`Sale::STATUSES`):

- `sin_entregar`
- `entregado`

Metodos de pago usados en ventas:

- `efectivo`
- `qr`
- `tarjeta_debito`

### Traspasos y recepciones

- `transfers`: traspaso entre almacenes, origen, destino, usuario solicitante, aprobador, receptor, estado, fecha esperada, fecha recibida y notas.
- `transfer_items`: productos del traspaso, cantidad solicitada, cantidad recibida, cantidad danada, lote generado, vencimiento de recepcion y nota.
- `transfer_requests`: solicitudes sugeridas por agente IA para reposicion. Pueden aprobarse/rechazarse y vincularse a un `transfer`.

Estados de traspaso (`Transfer::STATUSES`):

- `pendiente`
- `en_transito`
- `recibido`

Estados de solicitud IA (`TransferRequest`):

- `Pendiente`
- `Aprobado`
- `Rechazado`

Regla operacional importante: los traspasos normales van desde almacenes fuente `SCZ` o `CBA` hacia el almacen objetivo `LPZ` o ciudad `La Paz`.

### Comprador y pagos

- `buyer_orders`: orden de comprador final con recibo, metodo/estado de pago, estado, subtotal, envio, total y fecha de emision.
- `buyer_order_items`: items historicos del pedido, con nombre del producto y precio al momento de compra.

El checkout descuenta stock del almacen de La Paz si existe, o del primer almacen como fallback.

### Auditoria, respaldos y danos

- `audit_logs`: usuario, entidad, accion, descripcion, valores anteriores/nuevos y fecha.
- `backups`: archivo SQL generado, disco, tamano, estado, mensaje, creador, origen manual/programado y agenda.
- `backup_schedules`: configuracion de respaldo automatico, frecuencia en dias, hora, activo, proxima ejecucion y ultima ejecucion.
- `damage_reports`: reportes de producto/lote danado, almacen, cantidad, motivo y usuario.

## Flujos de negocio

### Gestion de productos

`ProductController` permite listar, crear, editar, activar/desactivar, eliminar y generar PDF de productos. Las imagenes se guardan como `image_path`; `Product::getImageUrl()` devuelve `asset('storage/' . image_path)` o placeholder si no hay imagen.

Al crear o actualizar productos se manejan precios publico/institucional y limites `min_quantity` / `max_quantity`. Si se carga stock inicial o se modifican lotes, la cantidad real debe reflejarse por `ProductLot`.

### Gestion de lotes

`ProductLotController` y `AlmacenLotController` muestran productos agrupados con historial de lotes. Los lotes tienen cantidad, vencimiento, almacen y codigo. Hay filtros de busqueda, producto, almacen y vencimiento.

`ProductLot::addStock()`:

- Valida que el total del producto no exceda `products.max_quantity` si esta configurado.
- Crea un lote.
- Registra movimiento positivo.
- Dispara sincronizacion de inventario.

`ProductLot::consumeFefo()`:

- Consume stock por producto y almacen.
- Solo usa lotes con cantidad positiva y no vencidos.
- Ordena por `expires_at`, aplicando FEFO: vence primero, sale primero.
- Registra movimientos negativos.
- Lanza error si no hay stock suficiente.

### Ventas

`SaleController` cubre ventas administrativas y ventas del vendedor.

Al registrar una venta:

1. Si no se envia `warehouse_id`, usa el almacen La Paz (`code = LPZ` o ciudad `La Paz`).
2. Valida tipo de venta, cliente/empresa, ciudad, metodo de pago e items.
3. Si el tipo es `comprador_minorista`, exige `customer_id`.
4. Si no es minorista, exige `company_id`.
5. Verifica stock disponible por lotes para cada producto.
6. Crea `Sale` y `SaleItem`.
7. Descuenta inventario con `ProductLot::consumeFefo(..., 'venta', ...)`.

El lookup de producto puede buscar por SKU o texto y devuelve precio segun tipo de venta:

- `empresa_institucional` usa `price_institutional`.
- Otros tipos usan `suggested_price_public`.

### Cotizaciones

`QuotationController` permite listar, crear, buscar productos y generar PDF. Las cotizaciones tienen items y total, pero no descuentan stock porque no son venta confirmada.

### Traspasos internos

`TransferController` gestiona traspasos desde admin.

Al crear un traspaso:

1. Valida que exista almacen objetivo La Paz.
2. Valida origen dentro de `SCZ` o `CBA`.
3. Crea `Transfer` con estado `pendiente` por defecto.
4. Crea `TransferItem` por producto.
5. Registra auditoria de creacion.

El lookup de producto por SKU puede devolver stock disponible en el almacen origen elegido.

`TransferController::ensureApprovedAgentRequestsHaveTransfer()` convierte solicitudes IA aprobadas y aun no vinculadas en traspasos reales hacia La Paz.

### Recepcion en almacen

`AlmacenTransferController` permite al rol de almacen revisar y actualizar traspasos hacia La Paz.

Al actualizar un item:

- Se registra `received_qty`, `damaged_qty`, `lot_code`, vencimiento y nota.
- Se calcula cantidad buena: `received_qty - damaged_qty`.
- Se compara contra la cantidad buena anterior.
- Si el delta es positivo, crea stock con `ProductLot::addStock(..., 'traspaso', ...)`.
- Si el delta es negativo, consume/ajusta stock con `ProductLot::consumeFefo(..., 'ajuste_traspaso', ...)`.

Al marcar un traspaso como `recibido`, puede recepcionar automaticamente items no procesados, creando lote con codigo automatico `TR-{transferId}-{itemId}-{YYYYMMDD}` y vencimiento por defecto a 6 meses.

### Danos en almacen

`AlmacenDamageController` lista productos/lotes y permite registrar danos. Al guardar un dano, descuenta cantidad del lote, crea `DamageReport` y registra movimiento correspondiente. Mantiene trazabilidad por producto, lote, almacen y usuario.

### Checkout del comprador

`PaymentController` maneja `/dashboard/pago`.

Funcionamiento:

1. Lee el carrito desde el campo `cart` en JSON.
2. Muestra historial de ultimas ordenes y recomendaciones basadas en productos comprados.
3. Al procesar, valida metodo de pago y carrito.
4. Crea numero de recibo `RC-YYYYMMDDHHMMSS-random`.
5. Crea `BuyerOrder` y `BuyerOrderItem`.
6. Verifica stock disponible.
7. Descuenta stock con FEFO desde almacen La Paz.
8. Redirige con confirmacion y numero de recibo.

Estado de pago:

- Si metodo es `efectivo`, queda `pendiente`.
- Otros metodos quedan `completado`.

Recibos:

- Vista HTML: `/dashboard/pago/recibo/{number}`
- Descarga: `/dashboard/pago/recibo/{number}/descargar`
- Si Dompdf existe, descarga PDF; si no, descarga HTML como fallback.

### Vendedor

El panel vendedor incluye:

- Dashboard personal con estadisticas.
- Gestion de clientes propios/empresas.
- Registro y consulta de ventas propias.
- Registro de visitas.
- Cotizaciones.
- Reporte PDF de ventas personales.

Cuando una ruta es `dashboard.vendedor.*`, `SaleController` filtra ventas por `seller_id` del usuario autenticado y evita que un vendedor actualice ventas de otro usuario.

### Comprador

`BuyerController` arma el catalogo para comprador. Usa productos, stock por lotes y resumen de disponibilidad. El comprador puede ir a pago, generar orden y recibo.

### Administrador

El administrador concentra:

- Dashboard general con estadisticas.
- Usuarios.
- Clientes/empresas.
- Productos.
- Categorias.
- Lotes.
- Traspasos.
- Ventas.
- Cotizaciones.
- Logs.
- Backups.
- Agente inteligente.

El sidebar React se define en `AdminReact::layout()`.

## Agentes inteligentes

Hay dos servicios relacionados:

### `AiAgentService`

Consulta `GET {AI_AGENT_URL}/predict`, cachea la respuesta 5 minutos con clave `ai-agent-response` y sirve datos para `AdminAiController`.

Si falla:

- Registra warning/error en logs.
- Lanza excepcion con mensaje amigable.

`AdminAiController` usa estos datos para:

- Vista del agente.
- Endpoint `/dashboard/agente/data`.
- Reporte PDF.
- Detalle por producto.
- Series de ventas, stock, lotes por vencer y recomendaciones.

### `AiReplenishmentAgentService`

Consulta:

- Health: `GET {AI_AGENT_URL}/health`
- Prediccion/reposicion: `GET {AI_AGENT_URL}/api/predict`

Normaliza el payload en:

- `online`
- `last_run_at`
- `forecasts`
- `transfer_requests`
- `alerts.low_stock`
- `alerts.expiring`
- `alerts.post_peak_drop`
- `raw`
- `error`

Si el agente no responde, devuelve payload offline sin romper la pantalla.

`createPendingRequests()` crea registros `TransferRequest` para sugerencias de reposicion y evita duplicados recientes por producto dentro de `AI_AGENT_DUPLICATE_WINDOW_HOURS` (24 horas por defecto).

El controlador `AiReplenishmentAgentController` permite:

- Ver panel de reposicion.
- Generar reporte PDF.
- Ejecutar agente manualmente.
- Aprobar solicitudes.
- Rechazar solicitudes.
- Enriquecer pronosticos con productos, stock, transito y alertas operativas.

Comando CLI:

- `php artisan agent:replenishment-check`
- Opcion: `--window=` para indicar horas de ventana anti duplicado.

## Backups

`BackupService` genera archivos SQL en `storage/app/backups`.

Funcionamiento:

1. Crea registro `Backup` con estado `running`.
2. Abre conexion DB actual.
3. Lista tablas con `SHOW TABLES`.
4. Escribe `DROP TABLE`, `CREATE TABLE` e `INSERT` por cada registro.
5. Si termina, marca `completed` y guarda tamano.
6. Si falla, marca `failed` con mensaje.

`BackupController` permite:

- Ver backups.
- Crear backup manual.
- Descargar backup.
- Eliminar backup.
- Actualizar agenda.

Backups automaticos:

- `BackupService::ensureDefaultSchedule()` crea agenda "Respaldo automatico principal".
- Frecuencia por defecto: cada 3 dias.
- Hora por defecto: `02:00`.
- `Console\Kernel` ejecuta `backups:run-scheduled` cada minuto con `withoutOverlapping()`.
- El comando solo crea backups si hay agendas vencidas.

## Reportes PDF

`ReportService::download($view, $data, $filename)` centraliza generacion de PDF con Spatie Laravel PDF.

Vistas de reporte en `resources/views/reports`:

- `agent.blade.php`
- `replenishment-agent.blade.php`
- `categories.blade.php`
- `companies.blade.php`
- `products.blade.php`
- `lots.blade.php`
- `transfers.blade.php`
- `sales` / `vendor-sales.blade.php`
- `quotations.blade.php`
- `users.blade.php`
- `logs.blade.php`
- `vendor-companies.blade.php`

Si se agrega un reporte nuevo, seguir el patron:

1. Crear vista en `resources/views/reports`.
2. Preparar datos en controlador.
3. Retornar `ReportService::download(...)`.

## Auditoria

`AuditLog` registra acciones relevantes.

Campos:

- `user_id`
- `entity_type`
- `entity_id`
- `action`
- `description`
- `old_values`
- `new_values`
- `created_at`

`LogsAudit` es un trait usado por algunos controladores para registrar cambios. Login, logout y fallos de autenticacion tambien crean logs directamente.

## Convenciones y reglas para futuras modificaciones

- Respetar Eloquent y relaciones existentes antes de agregar consultas manuales.
- No actualizar `inventory` directamente si la operacion puede expresarse como lote; usar `ProductLot::addStock()` o `ProductLot::consumeFefo()`.
- Toda salida de stock debe respetar FEFO para evitar vender productos con vencimiento mas lejano antes que los proximos a vencer.
- Validar stock con `ProductLot::available()` antes de vender o descontar.
- Mantener transacciones `DB::transaction()` en operaciones que crean cabecera + items + movimientos de stock.
- Mantener estados exactos: ventas usan minusculas (`sin_entregar`, `entregado`), traspasos usan minusculas (`pendiente`, `en_transito`, `recibido`), solicitudes IA usan capitalizado (`Pendiente`, `Aprobado`, `Rechazado`).
- Para pantallas admin React, usar `AdminReact::page()` y registrar la pagina en `pageRegistry.js` si se crea una nueva.
- Para formularios POST/PUT/PATCH/DELETE desde frontend, enviar CSRF.
- Para reportes, usar `ReportService` salvo que sea recibo de pago, que tiene su propio fallback.
- Si se cambia el esquema de datos, crear migracion nueva; no editar migraciones ya aplicadas en ambientes existentes.
- Si se agregan roles o permisos, revisar `RoleSeeder`, `AuthController::routeForRole`, sidebars y rutas.
- Si se cambia almacenes origen/destino de traspasos, revisar metodos `sourceWarehouses()`, `targetWarehouse()` y `sourceWarehouseForProduct()` en `TransferController` y controlador de agente.
- Si se cambia el agente IA, mantener compatibilidad con payloads `forecasts`, `forecast`, `transfer_requests`, `restock` y `alerts`.
- Si se modifican ventas, validar tambien checkout de comprador porque ambos descuentan stock por lotes.
- Si se modifican lotes, probar ventas, pagos, recepciones de traspaso, danos y reportes.

## Archivos clave por funcionalidad

- Login/registro: `app/Http/Controllers/AuthController.php`
- Layout admin React: `app/Support/AdminReact.php`
- Dashboard admin: `app/Http/Controllers/AdminController.php`
- Dashboard vendedor: `app/Http/Controllers/VendedorDashboardController.php`
- Dashboard almacen: `app/Http/Controllers/WarehouseDashboardController.php`
- Catalogo comprador: `app/Http/Controllers/BuyerController.php`
- Usuarios: `app/Http/Controllers/UserController.php`
- Clientes/empresas admin: `app/Http/Controllers/CompanyController.php`
- Clientes vendedor: `app/Http/Controllers/VendorCompanyController.php`
- Visitas vendedor: `app/Http/Controllers/VendorVisitController.php`
- Categorias: `app/Http/Controllers/CategoryController.php`
- Productos: `app/Http/Controllers/ProductController.php`
- Lotes admin: `app/Http/Controllers/ProductLotController.php`
- Lotes almacen: `app/Http/Controllers/AlmacenLotController.php`
- Danos almacen: `app/Http/Controllers/AlmacenDamageController.php`
- Ventas: `app/Http/Controllers/SaleController.php`
- Recepciones/despacho almacen: `app/Http/Controllers/AlmacenSaleController.php`
- Cotizaciones: `app/Http/Controllers/QuotationController.php`
- Traspasos admin: `app/Http/Controllers/TransferController.php`
- Traspasos almacen: `app/Http/Controllers/AlmacenTransferController.php`
- Pago comprador: `app/Http/Controllers/PaymentController.php`
- Auditoria: `app/Http/Controllers/AuditLogController.php`
- Backups: `app/Http/Controllers/BackupController.php`, `app/Services/BackupService.php`
- Agente general: `app/Http/Controllers/AdminAiController.php`, `app/Services/AiAgentService.php`
- Agente reposicion: `app/Http/Controllers/AiReplenishmentAgentController.php`, `app/Services/AiReplenishmentAgentService.php`
- Reportes PDF: `app/Services/ReportService.php`, `resources/views/reports`
- React entrypoint: `resources/js/react-pages.jsx`
- Registro de paginas React: `resources/js/react/pageRegistry.js`

## Pruebas recomendadas al cambiar el sistema

Despues de cualquier cambio importante ejecutar:

- `php artisan test`
- `npm run build`

Pruebas manuales recomendadas:

- Login con cada rol.
- Crear/editar producto con limites de stock.
- Crear lote y verificar inventario.
- Registrar venta y confirmar descuento FEFO.
- Crear cotizacion y generar PDF.
- Crear traspaso desde SCZ/CBA a LPZ.
- Recepcionar traspaso y verificar lote generado.
- Registrar dano y verificar descuento.
- Checkout de comprador y recibo.
- Crear backup manual.
- Ver agente IA con servicio online y con servicio offline.

## Notas de estado del repositorio

Al momento de crear este documento, el repositorio tenia un cambio no relacionado: `metricas_iso_25010_puntos_funcion.txt` aparece como eliminado en `git status`. Este documento no revierte ni toca ese cambio.
