-- User transfer email cleanup:
-- 1) Ensure transaction reference shortcode uses [[tnx]] (alias-safe)
-- 2) Add submitted timestamp and memo
-- 3) Keep a simple credit-union style body

UPDATE `email_templates`
SET
  `subject` = 'Fund Transfer Confirmation: [[amount]]',
  `title` = 'Transfer Confirmation',
  `salutation` = 'Hi [[full_name]]',
  `message_body` = 'Your transfer request has been received and is being processed.<br><br><strong>Transaction Details:</strong><br>From: [[from_account]]<br>To: [[to_account]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total: [[total_amount]]<br>Reference: [[tnx]]<br>Date/Time: [[date]]<br>Memo: [[memo]]',
  `button_level` = 'View Activity',
  `button_link` = '[[site_url]]/user/fund-transfer/transfer-log',
  `footer_status` = 1,
  `footer_body` = 'Thank you for banking with Pinellas FCU.',
  `updated_at` = NOW()
WHERE `code` = 'fund_transfer_request';

