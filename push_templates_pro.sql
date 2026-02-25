-- Pinellas FCU Professional Push Notification Templates
-- This SQL replaces generic templates with Pinellas-branded alerts.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Clean existing templates to avoid duplicates
TRUNCATE TABLE `push_notification_templates`;

INSERT INTO `push_notification_templates` (`id`, `icon`, `name`, `code`, `for`, `title`, `message_body`, `short_codes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'user-plus', 'New User Registration', 'new_user', 'Admin', 'New Member Joined', 'A new member [[full_name]] has registered an account.', '[\"[[full_name]]\",\"[[message]]\"]', 1, NOW(), NOW()),
(3, 'university', 'Deposit Request', 'manual_deposit_request', 'Admin', 'Deposit Pending Review', 'New deposit request of [[deposit_amount]] via [[gateway_name]]. Txn: [[txn]]', '[\"[[txn]]\",\"[[gateway_name]]\",\"[[deposit_amount]]\"]', 1, NOW(), NOW()),
(4, 'wallet', 'Withdrawal Request', 'withdraw_request', 'Admin', 'Withdrawal Alert', 'Member [[full_name]] requested a withdrawal of [[withdraw_amount]].', '[\"[[txn]]\",\"[[method_name]]\",\"[[withdraw_amount]]\",\"[[full_name]]\"]', 1, NOW(), NOW()),
(5, 'id-card', 'KYC Submission', 'kyc_request', 'Admin', 'Identity Verification Pending', '[[full_name]] has submitted identity documents for review.', '[\"[[full_name]]\",\"[[email]]\",\"[[kyc_type]]\"]', 1, NOW(), NOW()),
(6, 'check-circle', 'KYC Status Update', 'kyc_action', 'User', 'Identity Verification Updated', 'Your identity verification status is now [[status]].', '[\"[[status]]\"]', 1, NOW(), NOW()),
(10, 'wallet', 'Withdrawal Status', 'withdraw_request_user', 'User', 'Withdrawal Notification', 'Your withdrawal request [[txn]] for [[withdraw_amount]] is [[status]].', '[\"[[full_name]]\",\"[[message]]\",\"[[txn]]\",\"[[method_name]]\",\"[[withdraw_amount]]\",\"[[status]]\"]', 1, NOW(), NOW()),
(11, 'university', 'Deposit Status', 'user_manual_deposit_request', 'User', 'Deposit Confirmation', 'Your deposit of [[deposit_amount]] via [[gateway_name]] is [[status]].', '[\"[[full_name]]\",\"[[message]]\",\"[[txn]]\",\"[[gateway_name]]\",\"[[deposit_amount]]\",\"[[status]]\"]', 1, NOW(), NOW()),
(13, 'user-check', 'Welcome Message', 'new_user', 'User', 'Welcome to Pinellas FCU', 'Hello [[full_name]], thank you for joining our community. Your account is ready.', '[\"[[full_name]]\",\"[[message]]\"]', 1, NOW(), NOW()),
(18, 'exchange-alt', 'Transfer Request', 'fund_transfer_request', 'Admin', 'Transfer Pending', 'Internal transfer request from [[full_name]] for [[amount]].', '[\"[[full_name]]\",\"[[charge]]\",\"[[account_number]]\",\"[[account_name]]\",\"[[branch_name]]\",\"[[amount]]\",\"[[total_amount]]\"]', 1, NOW(), NOW()),
(19, 'exchange-alt', 'Transfer Status', 'fund_transfer_request', 'User', 'Transfer Notification', 'Your transfer of [[amount]] to [[account_name]] is [[status]].', '[\"[[full_name]]\",\"[[status]]\",\"[[charge]]\",\"[[account_number]]\",\"[[account_name]]\",\"[[branch_name]]\",\"[[amount]]\",\"[[total_amount]]\"]', 1, NOW(), NOW()),
(20, 'globe', 'Wire Transfer Alert', 'wire_transfer_request', 'Admin', 'Wire Transfer Pending', 'Member [[full_name]] requested a wire transfer of [[amount]].', '[\"[[full_name]]\",\"[[charge]]\",\"[[account_number]]\",\"[[name_of_account]]\",\"[[swift_code]]\",\"[[phone_number]]\",\"[[amount]]\",\"[[total_amount]]\"]', 1, NOW(), NOW()),
(21, 'globe', 'Wire Transfer Status', 'wire_transfer_request', 'User', 'Wire Transfer Update', 'Your wire transfer of [[amount]] is [[status]]. Status: [[status]]', '[\"[[full_name]]\",\"[[status]]\",\"[[charge]]\",\"[[account_number]]\",\"[[name_of_account]]\",\"[[swift_code]]\",\"[[phone_number]]\",\"[[amount]]\",\"[[total_amount]]\"]', 1, NOW(), NOW()),
(40, 'shield-alt', 'Security Login Alert', 'security_alert_login', 'User', 'Security Alert: New Login', 'A new login was detected for your account. If this wasn\'t you, please contact support.', '[\"[[browser]]\",\"[[platform]]\",\"[[ip]]\"]', 1, NOW(), NOW()),
(41, 'camera', 'Remote Deposit Submitted', 'remote_deposit_submitted', 'User', 'Check Deposit Received', 'We have received your mobile check deposit for [[amount]]. It is currently under review.', '[\"[[amount]]\",\"[[txn]]\"]', 1, NOW(), NOW()),
(42, 'check-double', 'Remote Deposit Approved', 'remote_deposit_approved', 'User', 'Check Deposit Approved', 'Great news! Your mobile check deposit of [[amount]] has been approved.', '[\"[[amount]]\",\"[[txn]]\"]', 1, NOW(), NOW()),
(43, 'times-circle', 'Remote Deposit Rejected', 'remote_deposit_rejected', 'User', 'Check Deposit Rejected', 'Your mobile check deposit of [[amount]] was rejected. Please check your email for details.', '[\"[[amount]]\",\"[[txn]]\",\"[[reason]]\"]', 1, NOW(), NOW()),
(44, 'credit-card', 'Card Alert', 'card_management_alert', 'User', 'Card Security Alert', 'A status change was detected on your card (...[[card_last_4]]).', '[\"[[status]]\",\"[[card_last_4]]\"]', 1, NOW(), NOW());

COMMIT;
