-- SQL Script to seed the Pinellas FCU Welcome Letter template into `document_templates`
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
    'Pinellas FCU Welcome Letter',
    'Pinellas Federal Credit Union',
    'welcome_letter',
    'Automatic welcome email template sent to new users upon email verification containing account credentials.',
    'Welcome to Pinellas FCU - Your Account Details',
    '<div style="background-color: #f4f6f9; padding: 20px 0; font-family: \'Outfit\', \'Inter\', \'Helvetica Neue\', Helvetica, Arial, sans-serif;">\n    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eef2f5;">\n        <!-- Header Gradient -->\n        <div style="background: linear-gradient(135deg, #00549b 0%, #002d62 100%); padding: 35px 30px; text-align: center;">\n            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -0.5px;">Welcome to Pinellas FCU</h1>\n            <p style="color: #a0c2e2; margin: 8px 0 0 0; font-size: 15px;">Your financial journey begins here</p>\n        </div>\n\n        <!-- Body Content -->\n        <div style="padding: 40px 30px; color: #333333; line-height: 1.6;">\n            <p style="font-size: 18px; margin-top: 0; font-weight: 600; color: #002d62;">Hello [[FULL_NAME]],</p>\n            <p style="font-size: 15px; color: #555555;">Thank you for opening an account with Pinellas Federal Credit Union. We are excited to welcome you to our community. Below are your essential account details for your records and reference.</p>\n            \n            <!-- Details Card -->\n            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin: 30px 0;">\n                <h3 style="margin-top: 0; margin-bottom: 18px; font-size: 16px; color: #00549b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Your Account Credentials</h3>\n                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">\n                    <tr>\n                        <td style="padding: 8px 0; color: #64748b; width: 45%;"><strong>Checking Account #:</strong></td>\n                        <td style="padding: 8px 0; color: #0f172a; font-family: monospace; font-weight: bold; font-size: 16px;">[[CHECKING_ACCOUNT_NUMBER]]</td>\n                    </tr>\n                    <tr>\n                        <td style="padding: 8px 0; color: #64748b;"><strong>Savings Account #:</strong></td>\n                        <td style="padding: 8px 0; color: #0f172a; font-family: monospace; font-weight: bold; font-size: 16px;">[[SAVINGS_ACCOUNT_NUMBER]]</td>\n                    </tr>\n                    <tr>\n                        <td style="padding: 8px 0; color: #64748b;"><strong>Routing Transit #:</strong></td>\n                        <td style="padding: 8px 0; color: #00549b; font-family: monospace; font-weight: bold; font-size: 16px;">[[ROUTING_NUMBER]]</td>\n                    </tr>\n                </table>\n            </div>\n\n            <p style="font-size: 15px; color: #555555;">With your new digital banking access, you can manage your balances, send funds instantly via Zelle®, pay bills, and apply for loans right from your dashboard.</p>\n            \n            <div style="text-align: center; margin: 35px 0 15px 0;">\n                <a href="[[LOGIN_URL]]" target="_blank" style="background: linear-gradient(135deg, #00549b 0%, #003b70 100%); color: #ffffff; text-decoration: none; padding: 14px 30px; font-weight: 600; font-size: 15px; border-radius: 6px; display: inline-block; box-shadow: 0 4px 10px rgba(0, 84, 155, 0.25);">\n                    Access Online Banking\n                </a>\n            </div>\n        </div>\n\n        <!-- Footer -->\n        <div style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #eef2f5; font-size: 12px; color: #64748b;">\n            <p style="margin: 0 0 10px 0;">Federally Insured by NCUA | Equal Housing Lender</p>\n            <p style="margin: 0;">© 2026 Pinellas Federal Credit Union. All rights reserved.</p>\n        </div>\n    </div>\n</div>',
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
