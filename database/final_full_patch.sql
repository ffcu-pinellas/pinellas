-- PINELLAS FCU - FINAL CUMULATIVE DB PATCH (FEB 2026)
-- Run these commands in phpMyAdmin if you cannot run migrations.

-- 1. Update USERS table with new Profile & Savings fields
ALTER TABLE `users` 
    ADD COLUMN IF NOT EXISTS `ssn` VARCHAR(20) NULL AFTER `ref_id`,
    ADD COLUMN IF NOT EXISTS `preferred_first_name` VARCHAR(255) NULL AFTER `first_name`,
    ADD COLUMN IF NOT EXISTS `savings_account_number` VARCHAR(255) NULL UNIQUE AFTER `account_number`,
    ADD COLUMN IF NOT EXISTS `savings_balance` DECIMAL(28,8) DEFAULT 0.00000000 AFTER `balance`,
    ADD COLUMN IF NOT EXISTS `dashboard_order` JSON NULL AFTER `savings_balance`;

-- 2. Create USER_CARDS table for the new Card Management system
CREATE TABLE IF NOT EXISTS `user_cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `card_number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry_month` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry_year` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cvv` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Visa',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_virtual` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_cards_user_id_foreign` (`user_id`),
  CONSTRAINT `user_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: The `scheduled_transfers` table was already included in your `deploy_robust_transfer.sql`.
