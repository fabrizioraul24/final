-- Backup generado el 2025-11-19 12:17:02
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `entity_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `audit_logs_action_index` (`action`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (1, NULL, 'auth', 0, 'login_failed', 'Login fallido para admin@pil.com', NULL, NULL, '2025-11-19 02:22:43');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (2, 1, 'auth', 1, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 02:22:53');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (3, 1, 'auth', 1, 'logout', 'Logout', NULL, NULL, '2025-11-19 02:47:38');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (4, 1, 'auth', 1, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 02:49:31');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (5, 1, 'App\\Models\\Product', 1, 'update', 'Actualizacion de producto', '{\"sku\": \"1001\", \"name\": \"Leche UHT Entera 1L\", \"is_active\": true, \"category_id\": 1, \"total_stock\": 270, \"price_institutional\": \"7.20\", \"suggested_price_public\": \"8.50\"}', '{\"sku\": \"1001\", \"name\": \"Leche UHT Entera 1L\", \"is_active\": true, \"category_id\": \"1\", \"total_stock\": 270, \"price_institutional\": \"7.20\", \"suggested_price_public\": \"8.50\"}', '2025-11-19 02:50:12');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (6, 1, 'App\\Models\\Product', 2, 'update', 'Actualizacion de producto', '{\"sku\": \"1002\", \"name\": \"Leche UHT Entera 500ml\", \"is_active\": true, \"category_id\": 1, \"total_stock\": 250, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '{\"sku\": \"1002\", \"name\": \"Leche UHT Entera 500ml\", \"is_active\": true, \"category_id\": \"1\", \"total_stock\": 250, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '2025-11-19 02:50:32');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (7, 1, 'App\\Models\\Product', 3, 'update', 'Actualizacion de producto', '{\"sku\": \"1003\", \"name\": \"Leche UHT Descremada 1L\", \"is_active\": true, \"category_id\": 2, \"total_stock\": 230, \"price_institutional\": \"7.20\", \"suggested_price_public\": \"8.50\"}', '{\"sku\": \"1003\", \"name\": \"Leche UHT Descremada 1L\", \"is_active\": true, \"category_id\": \"2\", \"total_stock\": 230, \"price_institutional\": \"7.20\", \"suggested_price_public\": \"8.50\"}', '2025-11-19 02:50:54');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (8, 1, 'App\\Models\\Product', 4, 'update', 'Actualizacion de producto', '{\"sku\": \"1004\", \"name\": \"Leche UHT Descremada 500ml\", \"is_active\": true, \"category_id\": 2, \"total_stock\": 210, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '{\"sku\": \"1004\", \"name\": \"Leche UHT Descremada 500ml\", \"is_active\": true, \"category_id\": \"2\", \"total_stock\": 210, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '2025-11-19 02:51:37');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (9, 1, 'App\\Models\\Product', 4, 'update', 'Actualizacion de producto', '{\"sku\": \"1004\", \"name\": \"Leche UHT Descremada 500ml\", \"is_active\": true, \"category_id\": 2, \"total_stock\": 210, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '{\"sku\": \"1004\", \"name\": \"Leche UHT Descremada 500ml\", \"is_active\": true, \"category_id\": \"2\", \"total_stock\": 210, \"price_institutional\": \"4.30\", \"suggested_price_public\": \"5.00\"}', '2025-11-19 02:51:44');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (10, 1, 'App\\Models\\Product', 5, 'update', 'Actualizacion de producto', '{\"sku\": \"1005\", \"name\": \"Leche Fresca Pasteurizada 1L\", \"is_active\": true, \"category_id\": 3, \"total_stock\": 195, \"price_institutional\": \"6.60\", \"suggested_price_public\": \"7.80\"}', '{\"sku\": \"1005\", \"name\": \"Leche Fresca Pasteurizada 1L\", \"is_active\": true, \"category_id\": \"3\", \"total_stock\": 195, \"price_institutional\": \"6.60\", \"suggested_price_public\": \"7.80\"}', '2025-11-19 02:52:12');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (11, 1, 'App\\Models\\Product', 6, 'update', 'Actualizacion de producto', '{\"sku\": \"1006\", \"name\": \"Leche Fresca Pasteurizada 500ml\", \"is_active\": true, \"category_id\": 3, \"total_stock\": 185, \"price_institutional\": \"3.70\", \"suggested_price_public\": \"4.30\"}', '{\"sku\": \"1006\", \"name\": \"Leche Fresca Pasteurizada 500ml\", \"is_active\": true, \"category_id\": \"3\", \"total_stock\": 185, \"price_institutional\": \"3.70\", \"suggested_price_public\": \"4.30\"}', '2025-11-19 02:52:38');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (12, 1, 'App\\Models\\Product', 7, 'update', 'Actualizacion de producto', '{\"sku\": \"1007\", \"name\": \"Yogurt Bebible Frutilla 1L\", \"is_active\": true, \"category_id\": 4, \"total_stock\": 165, \"price_institutional\": \"10.20\", \"suggested_price_public\": \"12.00\"}', '{\"sku\": \"1007\", \"name\": \"Yogurt Bebible Frutilla 1L\", \"is_active\": true, \"category_id\": \"4\", \"total_stock\": 165, \"price_institutional\": \"10.20\", \"suggested_price_public\": \"12.00\"}', '2025-11-19 02:53:03');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (13, 1, 'App\\Models\\Product', 8, 'update', 'Actualizacion de producto', '{\"sku\": \"1008\", \"name\": \"Yogurt Bebible Durazno 1L\", \"is_active\": true, \"category_id\": 4, \"total_stock\": 165, \"price_institutional\": \"10.20\", \"suggested_price_public\": \"12.00\"}', '{\"sku\": \"1008\", \"name\": \"Yogurt Bebible Durazno 1L\", \"is_active\": true, \"category_id\": \"4\", \"total_stock\": 165, \"price_institutional\": \"10.20\", \"suggested_price_public\": \"12.00\"}', '2025-11-19 02:53:33');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (14, 1, 'App\\Models\\Product', 9, 'update', 'Actualizacion de producto', '{\"sku\": \"1009\", \"name\": \"Yogurt Bebible Natural 500ml\", \"is_active\": true, \"category_id\": 4, \"total_stock\": 155, \"price_institutional\": \"5.90\", \"suggested_price_public\": \"6.90\"}', '{\"sku\": \"1009\", \"name\": \"Yogurt Bebible Natural 500ml\", \"is_active\": true, \"category_id\": \"4\", \"total_stock\": 155, \"price_institutional\": \"5.90\", \"suggested_price_public\": \"6.90\"}', '2025-11-19 02:53:56');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (15, 1, 'App\\Models\\Product', 10, 'update', 'Actualizacion de producto', '{\"sku\": \"1010\", \"name\": \"Yogurt Griego Natural 150g\", \"is_active\": true, \"category_id\": 5, \"total_stock\": 140, \"price_institutional\": \"4.70\", \"suggested_price_public\": \"5.50\"}', '{\"sku\": \"1010\", \"name\": \"Yogurt Griego Natural 150g\", \"is_active\": true, \"category_id\": \"5\", \"total_stock\": 140, \"price_institutional\": \"4.70\", \"suggested_price_public\": \"5.50\"}', '2025-11-19 02:54:38');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (16, 1, 'App\\Models\\Product', 11, 'update', 'Actualizacion de producto', '{\"sku\": \"1011\", \"name\": \"Yogurt Griego Frutilla 150g\", \"is_active\": true, \"category_id\": 5, \"total_stock\": 140, \"price_institutional\": \"5.00\", \"suggested_price_public\": \"5.90\"}', '{\"sku\": \"1011\", \"name\": \"Yogurt Griego Frutilla 150g\", \"is_active\": true, \"category_id\": \"5\", \"total_stock\": 140, \"price_institutional\": \"5.00\", \"suggested_price_public\": \"5.90\"}', '2025-11-19 02:55:39');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (17, 1, 'App\\Models\\Product', 12, 'update', 'Actualizacion de producto', '{\"sku\": \"1012\", \"name\": \"Queso Fresco Criollo 500g\", \"is_active\": true, \"category_id\": 6, \"total_stock\": 130, \"price_institutional\": \"17.50\", \"suggested_price_public\": \"20.00\"}', '{\"sku\": \"1012\", \"name\": \"Queso Fresco Criollo 500g\", \"is_active\": true, \"category_id\": \"6\", \"total_stock\": 130, \"price_institutional\": \"17.50\", \"suggested_price_public\": \"20.00\"}', '2025-11-19 02:56:29');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (18, 1, 'App\\Models\\Product', 13, 'update', 'Actualizacion de producto', '{\"sku\": \"1013\", \"name\": \"Queso Fresco Campesino 1kg\", \"is_active\": true, \"category_id\": 6, \"total_stock\": 120, \"price_institutional\": \"31.00\", \"suggested_price_public\": \"36.00\"}', '{\"sku\": \"1013\", \"name\": \"Queso Fresco Campesino 1kg\", \"is_active\": true, \"category_id\": \"6\", \"total_stock\": 120, \"price_institutional\": \"31.00\", \"suggested_price_public\": \"36.00\"}', '2025-11-19 02:58:44');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (19, 1, 'App\\Models\\Product', 14, 'update', 'Actualizacion de producto', '{\"sku\": \"1014\", \"name\": \"Queso Maduro Edam 400g\", \"is_active\": true, \"category_id\": 7, \"total_stock\": 110, \"price_institutional\": \"33.00\", \"suggested_price_public\": \"38.00\"}', '{\"sku\": \"1014\", \"name\": \"Queso Maduro Edam 400g\", \"is_active\": true, \"category_id\": \"7\", \"total_stock\": 110, \"price_institutional\": \"33.00\", \"suggested_price_public\": \"38.00\"}', '2025-11-19 02:59:38');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (20, 1, 'App\\Models\\Product', 15, 'update', 'Actualizacion de producto', '{\"sku\": \"1015\", \"name\": \"Queso Maduro Gouda 300g\", \"is_active\": true, \"category_id\": 7, \"total_stock\": 110, \"price_institutional\": \"29.00\", \"suggested_price_public\": \"34.00\"}', '{\"sku\": \"1015\", \"name\": \"Queso Maduro Gouda 300g\", \"is_active\": true, \"category_id\": \"7\", \"total_stock\": 110, \"price_institutional\": \"29.00\", \"suggested_price_public\": \"34.00\"}', '2025-11-19 03:00:04');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (21, 1, 'App\\Models\\Product', 16, 'update', 'Actualizacion de producto', '{\"sku\": \"1016\", \"name\": \"Mantequilla Tradicional 200g\", \"is_active\": true, \"category_id\": 8, \"total_stock\": 210, \"price_institutional\": \"9.00\", \"suggested_price_public\": \"10.50\"}', '{\"sku\": \"1016\", \"name\": \"Mantequilla Tradicional 200g\", \"is_active\": true, \"category_id\": \"8\", \"total_stock\": 210, \"price_institutional\": \"9.00\", \"suggested_price_public\": \"10.50\"}', '2025-11-19 03:00:28');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (22, 1, 'App\\Models\\Product', 17, 'update', 'Actualizacion de producto', '{\"sku\": \"1017\", \"name\": \"Mantequilla Sin Sal 200g\", \"is_active\": true, \"category_id\": 8, \"total_stock\": 195, \"price_institutional\": \"9.00\", \"suggested_price_public\": \"10.50\"}', '{\"sku\": \"1017\", \"name\": \"Mantequilla Sin Sal 200g\", \"is_active\": true, \"category_id\": \"8\", \"total_stock\": 195, \"price_institutional\": \"9.00\", \"suggested_price_public\": \"10.50\"}', '2025-11-19 03:00:56');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (23, 1, 'App\\Models\\Product', 19, 'update', 'Actualizacion de producto', '{\"sku\": \"1019\", \"name\": \"Crema para Batir 1L\", \"is_active\": true, \"category_id\": 9, \"total_stock\": 125, \"price_institutional\": \"23.00\", \"suggested_price_public\": \"27.00\"}', '{\"sku\": \"1019\", \"name\": \"Crema para Batir 1L\", \"is_active\": true, \"category_id\": \"9\", \"total_stock\": 125, \"price_institutional\": \"23.00\", \"suggested_price_public\": \"27.00\"}', '2025-11-19 03:01:45');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (24, 1, 'App\\Models\\Product', 20, 'update', 'Actualizacion de producto', '{\"sku\": \"1020\", \"name\": \"Flan de Vainilla 120g\", \"is_active\": true, \"category_id\": 10, \"total_stock\": 180, \"price_institutional\": \"3.60\", \"suggested_price_public\": \"4.20\"}', '{\"sku\": \"1020\", \"name\": \"Flan de Vainilla 120g\", \"is_active\": true, \"category_id\": \"10\", \"total_stock\": 180, \"price_institutional\": \"3.60\", \"suggested_price_public\": \"4.20\"}', '2025-11-19 03:02:24');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (25, 1, 'App\\Models\\Product', 18, 'update', 'Actualizacion de producto', '{\"sku\": \"1018\", \"name\": \"Crema de Leche 200ml\", \"is_active\": true, \"category_id\": 9, \"total_stock\": 205, \"price_institutional\": \"6.30\", \"suggested_price_public\": \"7.50\"}', '{\"sku\": \"1018\", \"name\": \"Crema de Leche 200ml\", \"is_active\": true, \"category_id\": \"9\", \"total_stock\": 205, \"price_institutional\": \"6.30\", \"suggested_price_public\": \"7.50\"}', '2025-11-19 03:03:23');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (26, 1, 'auth', 1, 'logout', 'Logout', NULL, NULL, '2025-11-19 03:03:36');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (27, 3, 'auth', 3, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 03:04:18');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (28, 3, 'auth', 3, 'logout', 'Logout', NULL, NULL, '2025-11-19 03:29:06');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (29, 2, 'auth', 2, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 03:30:17');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (30, 1, 'auth', 1, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 09:55:37');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `description`, `old_values`, `new_values`, `created_at`) VALUES (31, 2, 'auth', 2, 'login', 'Login exitoso', NULL, NULL, '2025-11-19 09:57:48');

DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_created_by_foreign` (`created_by`),
  CONSTRAINT `backups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backups` (`id`, `file_name`, `disk`, `size`, `status`, `message`, `created_by`, `created_at`, `updated_at`) VALUES (1, 'backup_20251119_121206.sql', 'local', 0, 'failed', '\"mysqldump\" no se reconoce como un comando interno o externo,\r\nprograma o archivo por lotes ejecutable.', 1, '2025-11-19 12:12:06', '2025-11-19 12:12:06');
INSERT INTO `backups` (`id`, `file_name`, `disk`, `size`, `status`, `message`, `created_by`, `created_at`, `updated_at`) VALUES (2, 'backup_20251119_121702.sql', 'local', 0, 'running', NULL, 1, '2025-11-19 12:17:02', '2025-11-19 12:17:02');

DROP TABLE IF EXISTS `buyer_order_items`;
CREATE TABLE `buyer_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `buyer_order_items_order_id_foreign` (`order_id`),
  KEY `buyer_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `buyer_order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `buyer_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `buyer_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `buyer_order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES (1, 1, 18, 'Crema de Leche 200ml', 1, 7.50, '2025-11-19 03:11:20', '2025-11-19 03:11:20');

DROP TABLE IF EXISTS `buyer_orders`;
CREATE TABLE `buyer_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `receipt_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `buyer_orders_receipt_number_unique` (`receipt_number`),
  KEY `buyer_orders_user_id_foreign` (`user_id`),
  CONSTRAINT `buyer_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `buyer_orders` (`id`, `user_id`, `receipt_number`, `payment_method`, `payment_status`, `status`, `subtotal`, `shipping`, `total`, `issued_at`, `created_at`, `updated_at`) VALUES (1, 3, 'RC-20251119031120-580', 'qr', 'completado', 'procesado', 7.50, 0.00, 7.50, '2025-11-19 03:11:20', '2025-11-19 03:11:20', '2025-11-19 03:11:20');

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_id_unique` (`cart_id`,`product_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_received` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reserved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_customer_id_foreign` (`customer_id`),
  KEY `carts_status_index` (`status`),
  KEY `carts_payment_status_index` (`payment_status`),
  CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'Leche UHT entera', 'Leches larga vida enteras.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'Leche UHT descremada', 'Leches larga vida descremadas.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'Leche fresca pasteurizada', 'Leches refrigeradas para consumo diario.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 'Yogurt bebible', 'Presentaciones bebibles para consumo a domicilio.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 'Yogurt griego', 'Yogurts tipo griego altos en proteinas.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 'Queso fresco', 'Quesos suaves ideales para mesa y cocina.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 'Queso maduro', 'Quesos curados con sabores intensos.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (8, 'Mantequilla', 'Mantequillas para cocina y reposteria.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (9, 'Crema de leche', 'Cremas para batir o cocinar.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (10, 'Postres lacteos', 'Postres listos basados en leche.', '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cities` (`id`, `name`, `code`, `department`, `created_at`, `updated_at`) VALUES (1, 'La Paz', 'LPZ', 'La Paz', '2025-11-19 02:17:39', '2025-11-19 02:17:39');
INSERT INTO `cities` (`id`, `name`, `code`, `department`, `created_at`, `updated_at`) VALUES (2, 'El Alto', 'EA', 'La Paz', '2025-11-19 02:17:39', '2025-11-19 02:17:39');

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nit` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_maps_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_last_name_paterno` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_last_name_materno` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_nit_unique` (`nit`),
  KEY `companies_created_by_foreign` (`created_by`),
  KEY `companies_company_type_index` (`company_type`),
  KEY `companies_city_index` (`city`),
  CONSTRAINT `companies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'empresa_institucional', 'Distribuidora Andina SRL', 900000001, 'contacto@andina.com', 70010001, 'Av. Busch 1234', NULL, 'La Paz', 'Marco', 'Rojas', 'Lopez', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'empresa_institucional', 'Lacteos Altiplano SA', 900000002, 'ventas@altiplano.bo', 70010002, 'Av. 6 de Marzo 210', NULL, 'El Alto', 'Lucia', 'Quispe', 'Huanca', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'empresa_institucional', 'Supermercados Cordillera', 900000003, 'compras@cordillera.bo', 70010003, 'Av. Costanera 455', NULL, 'La Paz', 'Ramiro', 'Aguilar', 'Soto', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 'empresa_institucional', 'Hospital Central La Paz', 900000004, 'compras@hclp.bo', 70010004, 'C. Bueno 789', NULL, 'La Paz', 'Claudia', 'Fernandez', 'Diaz', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 'empresa_institucional', 'Catering Delicias SRL', 900000005, 'catering@delicias.bo', 70010005, 'Av. America 1020', NULL, 'La Paz', 'Paola', 'Gonzales', 'Salazar', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 'empresa_institucional', 'Hotel Mirador Andino', 900000006, 'compras@mirador.bo', 70010006, 'Av. Naciones Unidas 350', NULL, 'El Alto', 'Gustavo', 'Rivera', 'Paredes', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 'empresa_institucional', 'Restaurante Sabores del Valle', 900000007, 'chef@saboresvalle.bo', 70010007, 'C. Mercado 560', NULL, 'La Paz', 'Emilio', 'Mercado', 'Vega', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (8, 'empresa_institucional', 'Colegio San Martin', 900000008, 'admin@sanmartin.edu.bo', 70010008, 'Av. Arce 321', NULL, 'El Alto', 'Rosa', 'Maldonado', 'Tapia', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (9, 'empresa_institucional', 'Fabrica Dulce Nieve', 900000009, 'contacto@dulcenieve.bo', 70010009, 'Parque Industrial 12', NULL, 'La Paz', 'Miguel', 'Suarez', 'Quiroga', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (10, 'empresa_institucional', 'Cooperativa Lechera Metropolitana', 900000010, 'compras@clemet.bo', 70010010, 'Av. Montes 800', NULL, 'La Paz', 'Veronica', 'Ortega', 'Perez', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (11, 'tienda_barrio', 'Tienda Las Lomas', 900000011, 'lomas@barrio.bo', 70010011, 'C. 1 Las Lomas', NULL, 'La Paz', 'Julia', 'Vargas', 'Linares', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (12, 'tienda_barrio', 'Minimarket El Puente', 900000012, 'puente@minimarket.bo', 70010012, 'Av. Panoramica 77', NULL, 'El Alto', 'Hugo', 'Lopez', 'Duran', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (13, 'tienda_barrio', 'Bodega Santa Rosa', 900000013, 'santarosa@bodega.bo', 70010013, 'C. Kollasuyo 30', NULL, 'La Paz', 'Rene', 'Gutierrez', 'Mamani', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (14, 'tienda_barrio', 'Kiosko Central', 900000014, 'kiosko@central.bo', 70010014, 'Plaza Central puesto 4', NULL, 'El Alto', 'Sofia', 'Arana', 'Pinto', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (15, 'tienda_barrio', 'Market Estrella', 900000015, 'estrella@market.bo', 70010015, 'Av. Montreal 17', NULL, 'La Paz', 'Patricia', 'Rivas', 'Cardenas', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (16, 'tienda_barrio', 'Super Ahorro Barrio', 900000016, 'ahorro@barrio.bo', 70010016, 'Av. Juan Pablo II 500', NULL, 'El Alto', 'Adrian', 'Campos', 'Yujra', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (17, 'tienda_barrio', 'Almacen San Pedro', 900000017, 'sanpedro@almacen.bo', 70010017, 'Barrio San Pedro 45', NULL, 'La Paz', 'Teresa', 'Flores', 'Callisaya', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (18, 'tienda_barrio', 'Tienda El Trigal', 900000018, 'trigal@tienda.bo', 70010018, 'C. Trigal 12', NULL, 'La Paz', 'Daniel', 'Romo', 'Beltran', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (19, 'tienda_barrio', 'Mini Super Aroma', 900000019, 'aroma@minisuper.bo', 70010019, 'Av. Aroma 88', NULL, 'El Alto', 'Luisa', 'Paz', 'Siles', 1, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `companies` (`id`, `company_type`, `name`, `nit`, `email`, `phone`, `address`, `google_maps_url`, `city`, `owner_first_name`, `owner_last_name_paterno`, `owner_last_name_materno`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (20, 'tienda_barrio', 'Despensa El Milagro', 900000020, 'milagro@despensa.bo', 70010020, 'El bosque', 'https://maps.app.goo.gl/LT4LTAJS2WVG2mmUA', 'La Paz', 'Nestor', 'Chavez', 'Alarcon', 1, '2025-11-19 02:17:40', '2025-11-19 12:00:27', NULL);

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_user_id_foreign` (`user_id`),
  KEY `customers_city_index` (`city`),
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '0',
  `min_quantity` int unsigned NOT NULL DEFAULT '0',
  `max_quantity` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_warehouse_id_product_id_unique` (`warehouse_id`,`product_id`),
  KEY `inventory_product_id_foreign` (`product_id`),
  CONSTRAINT `inventory_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 3, 1, 35, 0, NULL, '2025-11-19 10:06:51', '2025-11-19 10:06:51', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 1, 1, 120, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 3, 2, 83, 0, NULL, '2025-11-19 03:26:56', '2025-11-19 03:26:56', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 1, 2, 110, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 3, 3, 102, 0, NULL, '2025-11-19 03:19:32', '2025-11-19 03:19:32', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 1, 3, 100, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 3, 4, 18, 0, NULL, '2025-11-19 10:06:51', '2025-11-19 10:06:51', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (8, 1, 4, 90, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (9, 3, 5, 20, 0, NULL, '2025-11-19 10:13:22', '2025-11-19 10:13:22', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (10, 1, 5, 85, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (11, 3, 6, 21, 0, NULL, '2025-11-19 10:12:38', '2025-11-19 10:12:38', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (12, 1, 6, 80, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (13, 3, 7, 95, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (14, 1, 7, 70, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (15, 3, 8, 95, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (16, 1, 8, 70, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (17, 3, 9, 90, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (18, 1, 9, 65, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (19, 3, 10, 80, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (20, 1, 10, 60, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (21, 3, 11, 80, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (22, 1, 11, 60, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (23, 3, 12, 75, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (24, 1, 12, 55, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (25, 3, 13, 70, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (26, 1, 13, 50, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (27, 3, 14, 40, 0, NULL, '2025-11-19 03:10:19', '2025-11-19 03:10:19', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (28, 1, 14, 45, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (29, 3, 15, 65, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (30, 1, 15, 45, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (31, 3, 16, 120, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (32, 1, 16, 90, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (33, 3, 17, 110, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (34, 1, 17, 85, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (35, 3, 18, 49, 0, NULL, '2025-11-19 03:11:20', '2025-11-19 03:11:20', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (36, 1, 18, 90, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (37, 3, 19, 33, 0, NULL, '2025-11-19 03:22:08', '2025-11-19 03:22:08', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (38, 1, 19, 55, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (39, 3, 20, 33, 0, NULL, '2025-11-19 03:10:45', '2025-11-19 03:10:45', NULL);
INSERT INTO `inventory` (`id`, `warehouse_id`, `product_id`, `quantity`, `min_quantity`, `max_quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES (40, 1, 20, 80, 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2, '2014_10_12_100000_create_password_reset_tokens_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5, '2025_01_01_000100_create_companies_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6, '2025_01_01_000110_create_customers_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7, '2025_01_01_000120_create_categories_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8, '2025_01_01_000130_create_products_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9, '2025_01_01_000140_create_warehouses_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10, '2025_01_01_000150_create_inventory_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11, '2025_01_01_000160_create_transfers_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12, '2025_01_01_000170_create_transfer_items_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13, '2025_01_01_000180_create_carts_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14, '2025_01_01_000190_create_cart_items_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15, '2025_01_01_000200_create_sales_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16, '2025_01_01_000210_create_sale_items_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17, '2025_01_01_000220_create_quotations_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18, '2025_01_01_000230_create_quotation_items_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19, '2025_01_01_000240_create_restock_requests_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20, '2025_01_01_000250_create_restock_request_items_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21, '2025_01_01_000260_create_audit_logs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22, '2025_01_01_000270_add_image_url_to_products_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23, '2025_01_01_000280_update_product_images_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24, '2025_01_01_000290_add_payment_fields_to_carts_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25, '2025_01_01_000300_add_cash_fields_to_sales_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26, '2025_01_01_000310_create_cities_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27, '2025_01_01_000320_add_nit_to_customers_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28, '2025_01_01_000330_add_sale_type_to_quotations_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29, '2025_11_15_000001_create_roles_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30, '2025_11_15_000002_add_role_id_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31, '2025_11_15_000003_add_username_and_soft_deletes_to_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32, '2025_11_17_093809_add_deleted_at_to_categories_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33, '2025_11_18_000900_create_product_lots_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34, '2025_11_18_000901_create_product_lot_movements_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35, '2025_11_18_010000_create_buyer_orders_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36, '2025_11_18_020000_create_vendor_visits_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37, '2025_01_01_000410_add_google_maps_url_to_companies_table', 2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38, '2025_01_01_000400_create_backups_table', 3);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `product_lot_movements`;
CREATE TABLE `product_lot_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_lot_movements_lot_id_foreign` (`lot_id`),
  KEY `product_lot_movements_user_id_foreign` (`user_id`),
  KEY `product_lot_movements_type_index` (`type`),
  CONSTRAINT `product_lot_movements_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `product_lots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_lot_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (1, 1, 1, 'ingreso', 50, 'Alta manual de lote', '2025-11-19 03:07:36', '2025-11-19 03:07:36');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (3, 3, 1, 'ingreso', 30, 'Alta manual de lote', '2025-11-19 03:08:36', '2025-11-19 03:08:36');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (4, 1, 1, 'ajuste', 0, 'Ajuste lote', '2025-11-19 03:08:51', '2025-11-19 03:08:51');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (5, 4, 1, 'ingreso', 40, 'Alta manual de lote', '2025-11-19 03:10:19', '2025-11-19 03:10:19');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (6, 5, 1, 'ingreso', 33, 'Alta manual de lote', '2025-11-19 03:10:45', '2025-11-19 03:10:45');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (7, 1, 3, 'venta', -1, 'Checkout comprador', '2025-11-19 03:11:20', '2025-11-19 03:11:20');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (11, 9, 1, 'ingreso', 74, 'Alta manual de lote', '2025-11-19 03:18:53', '2025-11-19 03:18:53');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (13, 11, 1, 'ingreso', 33, 'Alta manual de lote', '2025-11-19 03:22:08', '2025-11-19 03:22:08');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (14, 12, 1, 'ingreso', 22, 'Alta manual de lote', '2025-11-19 03:22:59', '2025-11-19 03:22:59');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (15, 13, 1, 'ingreso', 23, 'Alta manual de lote', '2025-11-19 03:23:42', '2025-11-19 03:23:42');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (16, 14, 1, 'ingreso', 43, 'Alta manual de lote', '2025-11-19 03:26:11', '2025-11-19 03:26:11');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (17, 15, 1, 'ingreso', 83, 'Alta manual de lote', '2025-11-19 03:26:56', '2025-11-19 03:26:56');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (18, 14, 2, 'venta', -4, 'Venta #1', '2025-11-19 10:03:28', '2025-11-19 10:03:28');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (19, 3, 2, 'venta', -6, 'Venta #1', '2025-11-19 10:03:28', '2025-11-19 10:03:28');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (20, 14, 2, 'venta', -4, 'Venta #2', '2025-11-19 10:06:51', '2025-11-19 10:06:51');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (21, 3, 2, 'venta', -6, 'Venta #2', '2025-11-19 10:06:52', '2025-11-19 10:06:52');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (22, 13, 2, 'venta', -1, 'Venta #3', '2025-11-19 10:08:18', '2025-11-19 10:08:18');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (23, 13, 2, 'venta', -1, 'Venta #4', '2025-11-19 10:12:38', '2025-11-19 10:12:38');
INSERT INTO `product_lot_movements` (`id`, `lot_id`, `user_id`, `type`, `quantity`, `note`, `created_at`, `updated_at`) VALUES (24, 12, 2, 'venta', -2, 'Venta #5', '2025-11-19 10:13:22', '2025-11-19 10:13:22');

DROP TABLE IF EXISTS `product_lots`;
CREATE TABLE `product_lots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `lote_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `expires_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_lots_warehouse_id_foreign` (`warehouse_id`),
  KEY `product_lots_product_id_warehouse_id_index` (`product_id`,`warehouse_id`),
  KEY `product_lots_expires_at_index` (`expires_at`),
  CONSTRAINT `product_lots_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_lots_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (1, 18, 3, 1001, 49, '2025-12-23', '2025-11-19 03:07:36', '2025-11-19 03:11:20');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (3, 4, 3, 1003, 18, '2026-02-12', '2025-11-19 03:08:36', '2025-11-19 10:06:51');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (4, 14, 3, 1004, 40, '2026-02-23', '2025-11-19 03:10:19', '2025-11-19 03:10:19');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (5, 20, 3, 1005, 33, '2026-08-20', '2025-11-19 03:10:45', '2025-11-19 03:10:45');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (9, 3, 3, 1006, 74, '2026-01-21', '2025-11-19 03:18:53', '2025-11-19 03:18:53');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (11, 19, 3, 1002, 33, '2025-12-31', '2025-11-19 03:22:08', '2025-11-19 03:22:08');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (12, 5, 3, 1007, 20, '2026-01-28', '2025-11-19 03:22:59', '2025-11-19 10:13:22');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (13, 6, 3, 1008, 21, '2026-02-17', '2025-11-19 03:23:42', '2025-11-19 10:12:38');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (14, 1, 3, 1009, 35, '2026-02-02', '2025-11-19 03:26:11', '2025-11-19 10:06:51');
INSERT INTO `product_lots` (`id`, `product_id`, `warehouse_id`, `lote_code`, `quantity`, `expires_at`, `created_at`, `updated_at`) VALUES (15, 2, 3, 1010, 83, '2026-02-17', '2025-11-19 03:26:56', '2025-11-19 03:26:56');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sku` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggested_price_public` decimal(10,2) NOT NULL,
  `price_institutional` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_is_active_index` (`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 1, 'Leche UHT Entera 1L', 'Leche larga vida entera de un litro.', 1001, 8.50, 7.20, 1, 'products/hM2wpANKSos0gRrAkr3rLGh0wUXe752l3r3N53Jt.png', '2025-11-19 02:17:39', '2025-11-19 02:50:12', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 1, 'Leche UHT Entera 500ml', 'Presentación mediana de leche UHT entera.', 1002, 5.00, 4.30, 1, 'products/psSQJAC2bXLaoDqLeMw3Qbz2ZVbYBlBHKJoRX7TR.png', '2025-11-19 02:17:39', '2025-11-19 02:50:32', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 2, 'Leche UHT Descremada 1L', 'Leche UHT descremada formato familiar.', 1003, 8.50, 7.20, 1, 'products/mNMflmPZ6oBA7Cp5XhituxRgBUx9KuibuGgGCKzi.png', '2025-11-19 02:17:39', '2025-11-19 02:50:54', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 2, 'Leche UHT Descremada 500ml', 'Leche UHT descremada para consumo individual.', 1004, 5.00, 4.30, 1, 'products/chD4S8Y36zJXtPJb5v5roz9dlBfTeOg6l5GQQZ8t.png', '2025-11-19 02:17:39', '2025-11-19 02:51:44', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 3, 'Leche Fresca Pasteurizada 1L', 'Leche refrigerada pasteurizada lista para el desayuno.', 1005, 7.80, 6.60, 1, 'products/UnT2OzJDv21Z6T65RVFTMamTR8iN5cTXsn3Y2Opi.png', '2025-11-19 02:17:39', '2025-11-19 02:52:12', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (6, 3, 'Leche Fresca Pasteurizada 500ml', 'Envase medio refrigerado ideal para consumo diario.', 1006, 4.30, 3.70, 1, 'products/C9ZEhP4KHkKRe6loQY0zSEJ5ZLnvI6t9ED0kiWrZ.png', '2025-11-19 02:17:39', '2025-11-19 02:52:38', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (7, 4, 'Yogurt Bebible Frutilla 1L', 'Yogurt bebible sabor frutilla para toda la familia.', 1007, 12.00, 10.20, 1, 'products/QNqxh6WLVUpERNTCiiJZPMeiz4T12QodQIEhiatu.png', '2025-11-19 02:17:39', '2025-11-19 02:53:03', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (8, 4, 'Yogurt Bebible Durazno 1L', 'Yogurt bebible sabor durazno con probióticos.', 1008, 12.00, 10.20, 1, 'products/dBw8NE2LsZLAQsDx14n7Z7JCVNHkcbnRDOA3Unzo.png', '2025-11-19 02:17:39', '2025-11-19 02:53:33', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (9, 4, 'Yogurt Bebible Natural 500ml', 'Yogurt bebible natural sin azúcar añadida.', 1009, 6.90, 5.90, 1, 'products/DbDly39Btc1h00JTZXm1FaAYqymxCOEOsyaNQmDX.png', '2025-11-19 02:17:39', '2025-11-19 02:53:56', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (10, 5, 'Yogurt Griego Natural 150g', 'Yogurt griego cremoso natural alto en proteína.', 1010, 5.50, 4.70, 1, 'products/dXrCbBXIbgrsU0JtTzP03OFxCQkQdwR9W5e0lLi7.png', '2025-11-19 02:17:39', '2025-11-19 02:54:38', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (11, 5, 'Yogurt Griego Frutilla 150g', 'Yogurt griego con trozos de frutilla.', 1011, 5.90, 5.00, 1, 'products/e29moNCIFZIGSD7bWUVDLePnQIGhU3xJtiKB5xGd.png', '2025-11-19 02:17:39', '2025-11-19 02:55:39', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (12, 6, 'Queso Fresco Criollo 500g', 'Queso fresco criollo semidescremado.', 1012, 20.00, 17.50, 1, 'products/ytkWLA0uwU5snhePoA86xFsCO9rNVg81m51slDg4.png', '2025-11-19 02:17:39', '2025-11-19 02:56:29', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (13, 6, 'Queso Fresco Campesino 1kg', 'Queso fresco campesino para mesa y cocina.', 1013, 36.00, 31.00, 1, 'products/64ND1IF5pNGcnp2LIpYYUeNNpegySVDAqroDKjTj.png', '2025-11-19 02:17:39', '2025-11-19 02:58:44', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (14, 7, 'Queso Maduro Edam 400g', 'Queso maduro tipo Edam de sabor suave.', 1014, 38.00, 33.00, 1, 'products/G2pvQl49ZuTSV7ChEfEYBmxcHtnemYLEsoxvJsi6.png', '2025-11-19 02:17:39', '2025-11-19 02:59:38', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (15, 7, 'Queso Maduro Gouda 300g', 'Queso maduro tipo Gouda con notas caramelizadas.', 1015, 34.00, 29.00, 1, 'products/7cUqn9teLAjS2YYZchr0kQJt2eqahJNVbCIBAF9H.png', '2025-11-19 02:17:39', '2025-11-19 03:00:04', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (16, 8, 'Mantequilla Tradicional 200g', 'Mantequilla con sal para cocinar y untar.', 1016, 10.50, 9.00, 1, 'products/dXvmqA7ID5xPBXCwKUhlMsWgpOFx6CUmXbB6pXmV.png', '2025-11-19 02:17:39', '2025-11-19 03:00:28', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (17, 8, 'Mantequilla Sin Sal 200g', 'Mantequilla sin sal ideal para repostería.', 1017, 10.50, 9.00, 1, 'products/kW5c30V5FslIlVggRdl5ikbOUby0GaihewPiLQiv.png', '2025-11-19 02:17:39', '2025-11-19 03:00:56', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (18, 9, 'Crema de Leche 200ml', 'Crema pasteurizada para cocina diaria.', 1018, 7.50, 6.30, 1, 'products/t24E5c4yF3wkujzKUHk9Zl74nROrMUCA8Ns51xoF.png', '2025-11-19 02:17:39', '2025-11-19 03:03:23', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (19, 9, 'Crema para Batir 1L', 'Crema de leche con alto contenido de grasa para batir.', 1019, 27.00, 23.00, 1, 'products/TLEGe7kjkuXKoatfhYAGY9mLjzrqJKNaWIpzdFXI.png', '2025-11-19 02:17:39', '2025-11-19 03:01:45', NULL);
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `sku`, `suggested_price_public`, `price_institutional`, `is_active`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES (20, 10, 'Flan de Vainilla 120g', 'Postre lácteo sabor vainilla listo para servir.', 1020, 4.20, 3.60, 1, 'products/b9ZGskBYclR0jgjo4rKwqpYkQnrDtxDuGUM8woVD.png', '2025-11-19 02:17:39', '2025-11-19 03:02:24', NULL);

DROP TABLE IF EXISTS `quotation_items`;
CREATE TABLE `quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  KEY `quotation_items_product_id_foreign` (`product_id`),
  CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (1, 1, 2, 5, 5.00, 25.00, '2025-11-19 10:31:32', '2025-11-19 10:31:32');
INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (2, 2, 6, 12, 3.70, 44.40, '2025-11-19 10:32:48', '2025-11-19 10:32:48');

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `sale_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'empresa_institucional',
  `valid_until` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotations_company_id_foreign` (`company_id`),
  KEY `quotations_customer_id_foreign` (`customer_id`),
  KEY `quotations_seller_id_foreign` (`seller_id`),
  KEY `quotations_status_index` (`status`),
  KEY `quotations_sale_type_index` (`sale_type`),
  CONSTRAINT `quotations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotations_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quotations` (`id`, `company_id`, `customer_id`, `seller_id`, `sale_type`, `valid_until`, `status`, `total_amount`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 14, NULL, 1, 'tienda_barrio', '2025-11-27', 'borrador', 25.00, 'equis', '2025-11-19 10:31:32', '2025-11-19 10:31:32', NULL);
INSERT INTO `quotations` (`id`, `company_id`, `customer_id`, `seller_id`, `sale_type`, `valid_until`, `status`, `total_amount`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 9, NULL, 2, 'empresa_institucional', '2025-11-27', 'aceptada', 44.40, 'equisde', '2025-11-19 10:32:48', '2025-11-19 10:32:48', NULL);

DROP TABLE IF EXISTS `restock_request_items`;
CREATE TABLE `restock_request_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `restock_request_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `suggested_qty` int unsigned NOT NULL,
  `current_qty` int unsigned NOT NULL,
  `min_qty` int unsigned NOT NULL,
  `max_qty` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restock_request_items_restock_request_id_foreign` (`restock_request_id`),
  KEY `restock_request_items_product_id_foreign` (`product_id`),
  CONSTRAINT `restock_request_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `restock_request_items_restock_request_id_foreign` FOREIGN KEY (`restock_request_id`) REFERENCES `restock_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `restock_requests`;
CREATE TABLE `restock_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restock_requests_warehouse_id_foreign` (`warehouse_id`),
  KEY `restock_requests_requested_by_foreign` (`requested_by`),
  KEY `restock_requests_status_index` (`status`),
  KEY `restock_requests_reason_index` (`reason`),
  CONSTRAINT `restock_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restock_requests_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (1, 'Administrador', 'Control total del ecosistema Pil Andina', '2025-11-19 02:17:37', '2025-11-19 02:17:37');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (2, 'Vendedor', 'Gestión comercial y seguimiento de pedidos', '2025-11-19 02:17:37', '2025-11-19 02:17:37');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (3, 'Comprador', 'Clientes finales con acceso al catálogo y compras', '2025-11-19 02:17:37', '2025-11-19 02:17:37');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (4, 'Almacén', 'Control de inventario, lotes y despachos', '2025-11-19 02:17:37', '2025-11-19 02:17:37');

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (1, 1, 1, 4, 8.50, 34.00, '2025-11-19 10:03:28', '2025-11-19 10:03:28');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (2, 1, 4, 6, 5.00, 30.00, '2025-11-19 10:03:28', '2025-11-19 10:03:28');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (3, 2, 1, 4, 8.50, 34.00, '2025-11-19 10:06:51', '2025-11-19 10:06:51');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (4, 2, 4, 6, 5.00, 30.00, '2025-11-19 10:06:51', '2025-11-19 10:06:51');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (5, 3, 6, 1, 4.30, 4.30, '2025-11-19 10:08:18', '2025-11-19 10:08:18');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (6, 4, 6, 1, 4.30, 4.30, '2025-11-19 10:12:38', '2025-11-19 10:12:38');
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (7, 5, 5, 2, 7.80, 15.60, '2025-11-19 10:13:22', '2025-11-19 10:13:22');

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `sale_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_city` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_city_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_received` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_company_id_foreign` (`company_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_seller_id_foreign` (`seller_id`),
  KEY `sales_warehouse_id_foreign` (`warehouse_id`),
  KEY `sales_sale_type_index` (`sale_type`),
  KEY `sales_delivery_city_index` (`delivery_city`),
  KEY `sales_status_index` (`status`),
  KEY `sales_delivery_city_id_foreign` (`delivery_city_id`),
  CONSTRAINT `sales_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_delivery_city_id_foreign` FOREIGN KEY (`delivery_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  CONSTRAINT `sales_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sales` (`id`, `company_id`, `customer_id`, `seller_id`, `warehouse_id`, `sale_type`, `delivery_address`, `delivery_city`, `delivery_city_id`, `status`, `payment_method`, `amount_received`, `change_amount`, `total_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 15, NULL, 2, 3, 'tienda_barrio', 'https://maps.app.goo.gl/sZBfHKnpQaPJ3UAV6', 'La Paz', 1, 'sin_entregar', 'efectivo', NULL, NULL, 64.00, '2025-11-19 10:03:28', '2025-11-19 11:12:44', NULL);
INSERT INTO `sales` (`id`, `company_id`, `customer_id`, `seller_id`, `warehouse_id`, `sale_type`, `delivery_address`, `delivery_city`, `delivery_city_id`, `status`, `payment_method`, `amount_received`, `change_amount`, `total_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 15, NULL, 2, 3, 'tienda_barrio', 'https://maps.app.goo.gl/sZBfHKnpQaPJ3UAV6', 'La Paz', 1, 'entregado', 'efectivo', NULL, NULL, 64.00, '2025-11-19 10:06:51', '2025-11-19 11:12:38', NULL);
INSERT INTO `sales` (`id`, `company_id`, `customer_id`, `seller_id`, `warehouse_id`, `sale_type`, `delivery_address`, `delivery_city`, `delivery_city_id`, `status`, `payment_method`, `amount_received`, `change_amount`, `total_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 12, NULL, 2, 3, 'tienda_barrio', 'https://maps.app.goo.gl/sZBfHKnpQaPJ3UAV6', 'El Alto', 2, 'entregado', 'efectivo', NULL, NULL, 4.30, '2025-11-19 10:08:18', '2025-11-19 11:12:31', NULL);
INSERT INTO `sales` (`id`, `company_id`, `customer_id`, `seller_id`, `warehouse_id`, `sale_type`, `delivery_address`, `delivery_city`, `delivery_city_id`, `status`, `payment_method`, `amount_received`, `change_amount`, `total_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 11, NULL, 2, 3, 'tienda_barrio', 'https://maps.app.goo.gl/sZBfHKnpQaPJ3UAV6', 'La Paz', 1, 'sin_entregar', 'tarjeta_debito', NULL, NULL, 4.30, '2025-11-19 10:12:38', '2025-11-19 11:12:25', NULL);
INSERT INTO `sales` (`id`, `company_id`, `customer_id`, `seller_id`, `warehouse_id`, `sale_type`, `delivery_address`, `delivery_city`, `delivery_city_id`, `status`, `payment_method`, `amount_received`, `change_amount`, `total_amount`, `created_at`, `updated_at`, `deleted_at`) VALUES (5, 11, NULL, 2, 3, 'tienda_barrio', 'https://maps.app.goo.gl/sZBfHKnpQaPJ3UAV6', 'El Alto', 2, 'sin_entregar', 'tarjeta_debito', NULL, NULL, 15.60, '2025-11-19 10:13:22', '2025-11-19 11:12:19', NULL);

DROP TABLE IF EXISTS `transfer_items`;
CREATE TABLE `transfer_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transfer_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `requested_qty` int unsigned NOT NULL,
  `received_qty` int unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transfer_items_transfer_id_foreign` (`transfer_id`),
  KEY `transfer_items_product_id_foreign` (`product_id`),
  CONSTRAINT `transfer_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transfer_items_transfer_id_foreign` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `transfers`;
CREATE TABLE `transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_warehouse_id` bigint unsigned DEFAULT NULL,
  `to_warehouse_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `received_by` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  KEY `transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  KEY `transfers_requested_by_foreign` (`requested_by`),
  KEY `transfers_approved_by_foreign` (`approved_by`),
  KEY `transfers_received_by_foreign` (`received_by`),
  KEY `transfers_status_index` (`status`),
  CONSTRAINT `transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `username`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 1, 'Samuel Supervisor', 'admin@pil.com', 'samuel.admin', NULL, '$2y$12$3.yj0AyJNGtdm8hwPZ1uBOnvfldxZOakFqY32Lfl4NnUAyxxdXAJe', NULL, '2025-11-19 02:17:37', '2025-11-19 02:17:37', NULL);
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `username`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 2, 'Valeria Ventas', 'ventas@pil.com', 'valeria.ventas', NULL, '$2y$12$aJ7o1XymxEMFEOH4vgDZ6ejwuQ5uoUFw61aGrDaMj/MPkdgGNO4Lq', NULL, '2025-11-19 02:17:38', '2025-11-19 02:17:38', NULL);
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `username`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 3, 'Camila Cliente', 'comprador@pil.com', 'camila.cliente', NULL, '$2y$12$nztPp8Un6Alewt4ju59ojeVVAm3V5T9lhaIVJB7JPVp8nS4OU22EK', NULL, '2025-11-19 02:17:38', '2025-11-19 02:17:38', NULL);
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `username`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES (4, 4, 'Armando Almacén', 'almacen@pil.com', 'armando.almacen', NULL, '$2y$12$fbEk.1o7Z79UFzsqYKs66esvrHPrmaXtWNkVCYuBMH1bYe3qXtFUG', NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);

DROP TABLE IF EXISTS `vendor_visits`;
CREATE TABLE `vendor_visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `visit_date` date NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_visits_user_id_foreign` (`user_id`),
  KEY `vendor_visits_company_id_foreign` (`company_id`),
  CONSTRAINT `vendor_visits_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_visits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity_min` int unsigned NOT NULL DEFAULT '0',
  `capacity_max` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_code_unique` (`code`),
  KEY `warehouses_city_index` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `capacity_min`, `capacity_max`, `created_at`, `updated_at`, `deleted_at`) VALUES (1, 'Centro Logístico Santa Cruz', 'SCZ', 'Av. Cristo Redentor Km 9', 'Santa Cruz', 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `capacity_min`, `capacity_max`, `created_at`, `updated_at`, `deleted_at`) VALUES (2, 'Planta Cochabamba', 'CBA', 'Av. Blanco Galindo Km 5', 'Cochabamba', 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);
INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `capacity_min`, `capacity_max`, `created_at`, `updated_at`, `deleted_at`) VALUES (3, 'Depósito La Paz', 'LPZ', 'Zona Achocalla S/N', 'La Paz', 0, NULL, '2025-11-19 02:17:39', '2025-11-19 02:17:39', NULL);

SET FOREIGN_KEY_CHECKS=1;
