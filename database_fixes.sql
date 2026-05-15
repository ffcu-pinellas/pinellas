-- Fix missing deleted_at column in document_histories table
ALTER TABLE `document_histories` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL;

-- Add missing indexes to document_histories table
ALTER TABLE `document_histories` ADD INDEX `idx_document_histories_user_id` (`user_id`);
ALTER TABLE `document_histories` ADD INDEX `idx_document_histories_created_at` (`created_at`);

-- Create document_templates table
CREATE TABLE IF NOT EXISTS `document_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category` ENUM('general', 'account_statement', 'loan_letter', 'welcome_letter', 'notification', 'compliance', 'marketing') DEFAULT 'general',
    `description` TEXT NULL,
    `content` LONGTEXT NULL,
    `email_subject` VARCHAR(255) NULL,
    `email_salutation` VARCHAR(255) NULL,
    `email_content` LONGTEXT NULL,
    `is_active` BOOLEAN DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_document_templates_category` (`category`),
    INDEX `idx_document_templates_is_active` (`is_active`),
    INDEX `idx_document_templates_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create email_trackings table
CREATE TABLE IF NOT EXISTS `email_trackings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `document_history_id` BIGINT UNSIGNED NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'sent', 'delivered', 'opened', 'clicked', 'failed', 'bounced') DEFAULT 'pending',
    `retry_count` INT DEFAULT 0,
    `sent_at` TIMESTAMP NULL DEFAULT NULL,
    `delivered_at` TIMESTAMP NULL DEFAULT NULL,
    `opened_at` TIMESTAMP NULL DEFAULT NULL,
    `clicked_at` TIMESTAMP NULL DEFAULT NULL,
    `error_message` TEXT NULL,
    `tracking_token` VARCHAR(255) NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_email_trackings_user_id` (`user_id`),
    INDEX `idx_email_trackings_document_history_id` (`document_history_id`),
    INDEX `idx_email_trackings_status` (`status`),
    INDEX `idx_email_trackings_tracking_token` (`tracking_token`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_history_id`) REFERENCES `document_histories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create document_signatures table
CREATE TABLE IF NOT EXISTS `document_signatures` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_history_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `signed_by` BIGINT UNSIGNED NULL,
    `signature_type` ENUM('electronic', 'digital', 'wet_ink') DEFAULT 'electronic',
    `signature_provider` VARCHAR(255) NULL,
    `external_signature_id` VARCHAR(255) NULL,
    `signature_data` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `status` ENUM('pending', 'signed', 'declined', 'expired') DEFAULT 'pending',
    `signed_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `metadata` TEXT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_document_signatures_document_history_id` (`document_history_id`),
    INDEX `idx_document_signatures_user_id` (`user_id`),
    INDEX `idx_document_signatures_signed_by` (`signed_by`),
    INDEX `idx_document_signatures_status` (`status`),
    INDEX `idx_document_signatures_external_signature_id` (`external_signature_id`),
    FOREIGN KEY (`document_history_id`) REFERENCES `document_histories`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`signed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
