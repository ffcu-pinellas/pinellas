-- Admin Push Notification Templates (Phase 28)
-- Run this in phpMyAdmin to seed the missing templates

INSERT INTO `push_notification_templates` 
(`name`, `code`, `for`, `title`, `message_body`, `short_codes`, `status`, `created_at`, `updated_at`) 
VALUES 
(
    'Remote Deposit Submitted', 
    'remote_deposit_submitted', 
    'Admin', 
    'New Remote Deposit from [[full_name]]', 
    'A new remote deposit of [[amount]] has been submitted by [[full_name]] for review.', 
    '["[[full_name]]","[[amount]]","[[account_number]]"]', 
    1, 
    NOW(), 
    NOW()
),
(
    'Fund Transfer Submitted', 
    'fund_transfer_submitted', 
    'Admin', 
    'New [[type]] Transfer from [[full_name]]', 
    'A [[type]] transfer of [[amount]] to [[recipient]] has been initiated by [[full_name]].', 
    '["[[full_name]]","[[amount]]","[[type]]","[[recipient]]"]', 
    1, 
    NOW(), 
    NOW()
),
(
    'Wire Transfer Submitted', 
    'wire_transfer_submitted', 
    'Admin', 
    'New Wire Transfer from [[full_name]]', 
    'A new wire transfer request of [[amount]] has been submitted by [[full_name]].', 
    '["[[full_name]]","[[amount]]","[[swift_code]]"]', 
    1, 
    NOW(), 
    NOW()
),
(
    'Card Activity Alert', 
    'card_activity_alert', 
    'Admin', 
    'Card Activity: [[message]]', 
    'Card activity alert for user [[full_name]]: [[message]]', 
    '["[[full_name]]","[[message]]","[[card_number]]"]', 
    1, 
    NOW(), 
    NOW()
)
ON DUPLICATE KEY UPDATE 
`name` = VALUES(`name`),
`title` = VALUES(`title`),
`message_body` = VALUES(`message_body`),
`short_codes` = VALUES(`short_codes`),
`status` = VALUES(`status`),
`updated_at` = NOW();
