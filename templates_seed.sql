-- Seed Remote Deposit Submitted Template
INSERT INTO email_templates (name, code, subject, title, salutation, message_body, status, created_at, updated_at)
VALUES (
    'Remote Deposit Submitted',
    'remote_deposit_submitted',
    'Check Deposit Received - [[site_title]]',
    'Check Received',
    'Hello [[full_name]],',
    '<p>We have received your mobile check deposit of [[amount]] to your [[account_name]].</p>
<div style=\"background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;\">
<h4 style=\"margin-top: 0; color: #00549b;\">Deposit Details</h4>
<table style=\"width: 100%; border-collapse: collapse;\">
<tr><td style=\"padding: 5px 0; color: #666;\">Amount:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;\">[[amount]]</td></tr>
<tr><td style=\"padding: 5px 0; color: #666;\">Account:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[account_name]] ([[account_number]])</td></tr>
<tr><td style=\"padding: 5px 0; color: #666;\">Transaction ID:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[txn]]</td></tr>
<tr><td style=\"padding: 5px 0; color: #666;\">Status:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right; color: #ffc107;\">Pending Review</td></tr>
</table>
</div>
<p>Our team is now reviewing the deposit. You will receive another notification once it is processed and completed or if more information is needed.</p>',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Seed Card Status Update Template
INSERT INTO email_templates (name, code, subject, title, salutation, message_body, status, created_at, updated_at)
VALUES (
    'Card Status Update',
    'card_status_update',
    'Security Alert: Card Status Changed',
    'Card Update',
    'Hello [[full_name]],',
    '<p>A status change was detected for your card ending in [[card_number]].</p>
<div style=\"background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;\">
<h4 style=\"margin-top: 0; color: #00549b;\">Update Details</h4>
<table style=\"width: 100%; border-collapse: collapse;\">
<tr><td style=\"padding: 5px 0; color: #666;\">Card Number:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[card_number]]</td></tr>
<tr><td style=\"padding: 5px 0; color: #666;\">New Status:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[status]]</td></tr>
</table>
</div>
<p>[[message]]</p>
<p style=\"color: #666; font-size: 13px;\">If you did not authorize this change, please contact support immediately.</p>',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Seed Card Security Update Template
INSERT INTO email_templates (name, code, subject, title, salutation, message_body, status, created_at, updated_at)
VALUES (
    'Card Security Update',
    'card_security_update',
    'Security Alert: Card Security Action',
    'Security Update',
    'Hello [[full_name]],',
    '<p>A security action was performed on your card ending in [[card_number]].</p>
<div style=\"background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;\">
<h4 style=\"margin-top: 0; color: #00549b;\">Action Details</h4>
<table style=\"width: 100%; border-collapse: collapse;\">
<tr><td style=\"padding: 5px 0; color: #666;\">Card Number:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[card_number]]</td></tr>
<tr><td style=\"padding: 5px 0; color: #666;\">Action:</td><td style=\"padding: 5px 0; font-weight: bold; text-align: right;\">[[action]]</td></tr>
</table>
</div>
<p>[[message]]</p>
<p style=\"color: #666; font-size: 13px;\">If you did not authorize this action, please contact support immediately.</p>',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE updated_at = NOW();
