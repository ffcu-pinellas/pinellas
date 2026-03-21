-- Update the Fund Transfer Request email template with more details
UPDATE email_templates 
SET message_body = 'Your request to transfer <strong>[[amount]]</strong> from <strong>[[from_account]]</strong> to <strong>[[to_account]]</strong> has been received and is being processed.<br><br><strong>Transaction Details:</strong><br>Reference ID: <strong>[[tnx]]</strong><br>From Account: [[from_account]]<br>To Account: [[to_account]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total Debited: [[total_amount]]<br>Status: [[status]]<br>Initiated at: [[date]]<br>Memo: [[memo]]'
WHERE code = 'fund_transfer_request';
