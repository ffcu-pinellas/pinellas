-- Create scheduled_transfers table
CREATE TABLE IF NOT EXISTS `scheduled_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'self, member, other',
  `wallet_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `amount` double(20,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `charge` double(20,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active, completed, cancelled, failed',
  `frequency` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'once' COMMENT 'once, daily, weekly, monthly',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `meta_data` json DEFAULT NULL COMMENT 'beneficiary_id, manual_data, purpose',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_transfers_user_id_foreign` (`user_id`),
  CONSTRAINT `scheduled_transfers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
