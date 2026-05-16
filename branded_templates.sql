-- 1. Add the email_from_name column (If migration failed)
ALTER TABLE `document_templates` ADD COLUMN `email_from_name` VARCHAR(255) NULL AFTER `name`;

-- 2. Insert Zelle Template
INSERT INTO `document_templates` (`name`, `email_from_name`, `category`, `description`, `email_subject`, `email_content`, `content`, `is_active`, `created_by`, `created_at`, `updated_at`)
VALUES ('Zelle Official Network Notification', 'Zelle Payment Service', 'external_bank_notification', 'Official Zelle network branding for any recipient', 'Payment Alert: [[USER_NAME]] sent you $[[AMOUNT]]', '<!DOCTYPE html><html><body style=\"margin:0;padding:0;background:#fff;font-family:Arial;\"><div style=\"background:#6e1ac9;padding:40px;text-align:center;\"><img src=\"https://register.zellepay.com/email_assets/logoPurplenotext.png\" width=\"125\"><div style=\"margin:20px;color:#fff;font-size:24px;\">[[INITIALS]]</div><h2 style=\"color:#fff;\">Status: [[STATUS]]</h2><h1 style=\"color:#fff;\">$[[AMOUNT]]</h1><p style=\"color:#fff;\">from [[USER_NAME]]</p></div><div style=\"padding:20px;text-align:center;\"><p>This payment is being sent to: <b>[[RECIPIENT_EMAIL]]</b></p><p>Ref: [[TNX]]</p></div></body></html>', 'Zelle Template', 1, 1, NOW(), NOW());

-- 3. Insert Wells Fargo Template
INSERT INTO `document_templates` (`name`, `email_from_name`, `category`, `description`, `email_subject`, `email_content`, `content`, `is_active`, `created_by`, `created_at`, `updated_at`)
VALUES ('Wells Fargo Recipient Alert', 'Wells Fargo Online', 'external_bank_notification', 'High-fidelity Wells Fargo branded notification', 'Wells Fargo: You have an incoming transfer of $[[AMOUNT]]', '<div style=\"background:#d71e28;padding:20px;color:white;font-family:Arial;\"><h1>Wells Fargo</h1></div><div style=\"padding:20px;border:1px solid #ccc;\"><h3>Hello [[RECIPIENT_NAME]],</h3><p>[[USER_NAME]] has sent you $[[AMOUNT]].</p><p>Status: <strong>[[STATUS]]</strong></p><p>Bank: [[BANK_NAME]]</p></div>', 'Wells Fargo Template', 1, 1, NOW(), NOW());

-- 4. Insert Chase Template
INSERT INTO `document_templates` (`name`, `email_from_name`, `category`, `description`, `email_subject`, `email_content`, `content`, `is_active`, `created_by`, `created_at`, `updated_at`)
VALUES ('Chase Bank Payment Notification', 'Chase Bank Support', 'external_bank_notification', 'Official Chase Bank style notification', 'Payment Alert: [[USER_NAME]] sent you $[[AMOUNT]]', '<div style=\"background:#117aca;padding:20px;color:white;font-family:Arial;\"><h1>CHASE</h1></div><div style=\"padding:20px;border:1px solid #ccc;\"><h3>Payment from [[USER_NAME]]</h3><p>Amount: $[[AMOUNT]]</p><p>Status: [[STATUS]]</p></div>', 'Chase Template', 1, 1, NOW(), NOW());
