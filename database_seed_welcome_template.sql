-- SQL Script to seed the FrontField Credit Union Welcome Letter template into `document_templates`
-- You can run this directly in your database manager (e.g. phpMyAdmin)

INSERT INTO `document_templates` (
    `name`,
    `email_from_name`,
    `category`,
    `description`,
    `email_subject`,
    `email_content`,
    `content`,
    `is_active`,
    `created_by`,
    `created_at`,
    `updated_at`
) VALUES (
    'FrontField Credit Union Welcome Letter',
    'FrontField Credit Union',
    'welcome_letter',
    'Automatic welcome email template sent to new users upon email verification containing account credentials.',
    'Welcome to FrontField Credit Union - Your Account Details',
    '<!DOCTYPE html>\n<html lang="en">\n<head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <title>Welcome to [[SITE_TITLE]]</title>\n    <style>\n        body { margin: 0; padding: 0; background: #f0f4f8; font-family: \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }\n        .wrap { width: 100%; padding: 24px 12px; box-sizing: border-box; }\n        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); }\n        .header { background: linear-gradient(135deg, #00549b 0%, #002e5b 100%); padding: 24px 28px; text-align: left; }\n        .logo { max-height: 40px; max-width: 240px; }\n        .content { padding: 30px 28px; font-size: 15px; line-height: 1.65; color: #334155; }\n        h1 { font-size: 22px; color: #0f172a; margin: 0 0 16px; font-weight: 700; }\n        .intro { margin-bottom: 20px; color: #475569; }\n        \n        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; margin-bottom: 15px; }\n        table.meta td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }\n        table.meta td:first-child { color: #64748b; width: 45%; font-weight: 500; }\n        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; text-align: right; }\n        \n        .btn-wrap { text-align: center; margin: 28px 0 12px; }\n        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 6px rgba(0, 84, 155, 0.15); }\n        .footer { padding: 20px 28px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }\n        .muted { font-size: 11px; color: #94a3b8; margin-top: 14px; }\n    </style>\n</head>\n<body>\n<div class="wrap">\n    <div class="card">\n        <div class="header">\n            <a href="[[HOME_URL]]"><img src="[[LOGO_URL]]" alt="[[SITE_TITLE]]" class="logo"></a>\n        </div>\n        <div class="content">\n            <h1>Official Membership & Account Activation</h1>\n            <p class="intro">Dear [[FULL_NAME]],</p>\n            <p class="intro">We are pleased to welcome you as a member of FrontField Credit Union. Your membership has been verified and your digital banking profile is now active. Below is your official account structure and routing transit credentials. Please secure this information for your records.</p>\n            \n            <table class="meta" cellpadding="0" cellspacing="0">\n                <tr>\n                    <td>Account Holder</td>\n                    <td>[[FULL_NAME]]</td>\n                </tr>\n                <tr>\n                    <td>Primary Checking Account</td>\n                    <td>[[CHECKING_ACCOUNT_NUMBER]]</td>\n                </tr>\n                <tr>\n                    <td>Primary Savings Account</td>\n                    <td>[[SAVINGS_ACCOUNT_NUMBER]]</td>\n                </tr>\n                <tr>\n                    <td>ABA Routing Transit Number</td>\n                    <td>[[ROUTING_NUMBER]]</td>\n                </tr>\n            </table>\n\n            <p style="font-size: 14px; color: #64748b; margin-top: 20px;">\n                You can manage your balances, send funds via Zelle®, pay bills, and access electronic statements by logging into the secure portal.\n            </p>\n\n            <div class="btn-wrap">\n                <a href="[[LOGIN_URL]]" class="btn">Access Online Banking</a>\n            </div>\n        </div>\n        <div class="footer">\n            <strong>[[SITE_TITLE]]</strong>\n            <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>\n        </div>\n    </div>\n</div>\n</body>\n</html>',
    'Welcome Member Letter',
    1,
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    `email_content` = VALUES(`email_content`),
    `email_subject` = VALUES(`email_subject`),
    `email_from_name` = VALUES(`email_from_name`),
    `description` = VALUES(`description`),
    `updated_at` = NOW();
