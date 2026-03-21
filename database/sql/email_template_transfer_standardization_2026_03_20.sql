-- Standardize transfer-related user email templates.
-- Goal: keep existing visual theme (backend.mail.user-mail-send) while improving message content consistency.
-- Safe to run multiple times.

START TRANSACTION;

-- Fix known code mismatch (trailing space) so admin withdrawal emails resolve correct template.
UPDATE `email_templates`
SET `code` = 'withdraw_request', `updated_at` = NOW()
WHERE `code` = 'withdraw_request ';

-- 29: fund_transfer_request (submitted / pending)
UPDATE `email_templates`
SET
  `subject` = 'Fund Transfer Confirmation: [[amount]]',
  `title` = 'Transfer Confirmation',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your transfer request has been received and is currently being processed.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total: [[total_amount]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Thank you for banking with Pinellas FCU.',
  `updated_at` = NOW()
WHERE `code` = 'fund_transfer_request';

-- 129: member_transfer_approved
UPDATE `email_templates`
SET
  `subject` = 'Member Transfer Approved: [[amount]]',
  `title` = 'Transfer Approved',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your member transfer has been approved and completed.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total Deducted: [[total_amount]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Reliable. Secure. Pinellas.',
  `updated_at` = NOW()
WHERE `code` = 'member_transfer_approved';

-- 130: member_transfer_rejected
UPDATE `email_templates`
SET
  `subject` = 'Member Transfer Update: Action Needed',
  `title` = 'Transfer Not Approved',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'We were unable to approve your member transfer request.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Amount: [[amount]]<br>Fee Reversed: [[charge]]<br>Total Returned: [[total_amount]]<br>Reason: [[message]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Questions? We are here to help.',
  `updated_at` = NOW()
WHERE `code` = 'member_transfer_rejected';

-- 131: external_transfer_approved
UPDATE `email_templates`
SET
  `subject` = 'External Transfer Approved: [[amount]]',
  `title` = 'External Transfer Processed',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your external transfer request has been approved and completed.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Receiving Institution: [[bank_name]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total: [[total_amount]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Thank you for choosing Pinellas FCU.',
  `updated_at` = NOW()
WHERE `code` = 'external_transfer_approved';

-- 132: external_transfer_rejected
UPDATE `email_templates`
SET
  `subject` = 'External Transfer Update: Action Needed',
  `title` = 'External Transfer Not Approved',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your external transfer request could not be approved at this time.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Receiving Institution: [[bank_name]]<br>Amount: [[amount]]<br>Reason: [[message]]<br>Memo: [[memo]]<br><br>Any held funds have been returned to your available balance.',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Please contact us for more information.',
  `updated_at` = NOW()
WHERE `code` = 'external_transfer_rejected';

-- 50: wire_transfer (result)
UPDATE `email_templates`
SET
  `subject` = 'Wire Transfer Status: [[status]]',
  `title` = 'Wire Transfer Update',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'We have an update on your wire transfer request.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>Status: [[status]]<br>To: [[to_account]]<br>Bank: [[bank_name]]<br>Routing: [[routing_number]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total: [[total_amount]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Global Banking, Local Care.',
  `updated_at` = NOW()
WHERE `code` = 'wire_transfer';

-- Optional: legacy fund_transfer template (49), if still referenced by any flow.
UPDATE `email_templates`
SET
  `subject` = 'Transfer Success: [[amount]]',
  `title` = 'Transfer Completed',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your transfer has been completed successfully.<br><br><strong>Transaction Details:</strong><br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>From: [[from_account]]<br>To: [[to_account]]<br>Amount: [[amount]]<br>Total Deducted: [[total_amount]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Secure. Simple. Pinellas.',
  `updated_at` = NOW()
WHERE `code` = 'fund_transfer';

COMMIT;

