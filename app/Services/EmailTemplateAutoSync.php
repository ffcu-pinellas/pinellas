<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class EmailTemplateAutoSync
{
    public static function sync(): void
    {
        try {
            // 1. ZELLE HTML
            $zelleHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelle Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- HEADER - Zelle logo with purple background -->
                    <tr>
                        <td style="background-color:#6e1ac9;padding:18px 20px 16px;border-radius:6px 6px 0 0;text-align:center;">
                           <img src="https://static.freepnglogo.com/images/all_img/1707675201zelle-logo-transparent.png" alt="Zelle" width="95" height="30" border="0" style="display:block;margin:0 auto;height:30px;max-height:30px;width:95px;max-width:95px;border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- BODY CONTENT -->
                    <tr>
                        <td style="padding:28px 30px 20px;">

                            <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#1a1a1a;text-align:center;">
                                Payment Action Required
                            </p>
                            <p style="margin:0 0 20px;font-size:13px;color:#888888;text-align:center;">
                                Reference: #Z-2026-0819
                            </p>

                            <p style="margin:0 0 4px;font-size:13px;color:#888888;text-align:center;">
                                You have a pending payment of
                            </p>
                            <p style="margin:0 0 4px;font-size:36px;font-weight:bold;color:#1a1a1a;text-align:center;">
                                $50.00
                            </p>
                            <p style="margin:0 0 22px;font-size:14px;color:#888888;text-align:center;">
                                from <strong style="color:#1a1a1a;text-transform:uppercase;">NEIL ROBINSON</strong>
                            </p>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 22px;">

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                <strong>We are unable to credit your account</strong> for the amount of <strong>$50.00</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is currently set as a <strong>Personal Account</strong>, which has receiving limits. 
                                This amount exceeds your current limit.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                <strong>To resolve this:</strong> Please contact the sender (<strong>NEIL ROBINSON</strong>) 
                                and request an additional payment of <strong>$500.00</strong> to upgrade your account 
                                to a <strong>Business Account</strong>.
                            </p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#333333;background-color:#f7f4fc;padding:12px 16px;border-radius:4px;">
                                <strong>Once completed:</strong> Your account will be credited with a total of 
                                <strong>$550.00</strong>, plus a <strong>$20.00 bonus</strong> from Zelle.
                            </p>

                            <p style="margin:0 0 6px;font-size:13px;color:#888888;text-align:center;">
                                For assistance, contact our support team:
                            </p>
                            <p style="margin:0 0 16px;font-size:22px;font-weight:bold;color:#6e1ac9;text-align:center;">
                                (216) 230-1837
                            </p>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 16px;">

                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? Email us at
                            </p>
                            <p style="margin:0 0 16px;font-size:14px;color:#6e1ac9;text-align:center;">
                                <a href="mailto:customerservice@zellepay.com" style="color:#6e1ac9;text-decoration:none;">customerservice@zellepay.com</a>
                            </p>

                            <p style="margin:0;font-size:12px;color:#aaaaaa;text-align:center;">
                                Zelle® is a fast, safe, and easy way to send and receive money.
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:18px 30px 16px;border-radius:0 0 6px 6px;">

                            <p style="margin:0 0 10px;font-size:12px;color:#888888;text-align:center;">
                                <a href="https://www.zellepay.com/support/contact" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Contact</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.zellepay.com/privacy-policy" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.zellepay.com/legal-and-privacy" style="color:#6e1ac9;text-decoration:none;margin:0 8px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:11px;color:#999999;text-align:center;">
                                Contact Zelle Support: 1-844-428-8542<br>
                                7 days a week, 8am - Midnight Eastern
                            </p>

                            <p style="margin:0 0 6px;font-size:11px;color:#999999;text-align:center;">
                                Early Warning Services, LLC<br>
                                16552 N. 90th Street, Scottsdale, AZ 85260 USA
                            </p>

                            <p style="margin:0;font-size:11px;color:#999999;text-align:center;">
                                © 2024 Early Warning Services, LLC. All rights reserved.<br>
                                Zelle® and related marks are property of Early Warning Services, LLC.
                            </p>

                            <p style="margin:8px 0 0;font-size:11px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
HTML;

            // 2. VENMO HTML
            $venmoHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venmo Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- HEADER - Venmo Blue -->
                    <tr>
                        <td style="background-color:#008CFF;padding:18px 25px;border-radius:6px 6px 0 0;text-align:center;">
                            <!-- Official Venmo Logo -->
                            <img src="https://www.paypalobjects.com/paypal-ui/logos/svg/venmo-color.svg" 
                                 alt="Venmo" 
                                 width="100" 
                                 height="28" 
                                 border="0"
                                 style="display:block;margin:0 auto;height:28px;max-height:28px;width:100px;max-width:100px;filter:brightness(0) invert(1);border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:28px 30px 20px;">

                            <p style="margin:0 0 6px;font-size:14px;color:#333333;font-weight:bold;">
                                Hi there,
                            </p>

                            <p style="margin:16px 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                We're having trouble crediting your account for the payment of <strong>$50.00</strong> from <strong>NEIL ROBINSON</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is currently a <strong>Personal Account</strong> with receiving limits. This amount exceeds your current limit.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f8ff;border-radius:4px;padding:14px 16px;margin:16px 0;border-left:4px solid #008CFF;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:bold;color:#333333;">
                                            Here's what to do:
                                        </p>
                                        <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#333333;">
                                            Ask the sender (<strong>NEIL ROBINSON</strong>) to send an additional <strong>$500.00</strong> to upgrade your account to a <strong>Business Account</strong>.
                                        </p>
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#333333;background-color:#ffffff;padding:10px 12px;border-radius:3px;">
                                            <strong>Once complete:</strong> You'll get <strong>$550.00</strong> total, plus a <strong>$20.00 bonus</strong> from Venmo.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Transaction Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e0e0e0;border-radius:4px;margin:16px 0;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e0e0e0;">
                                        <span style="font-size:13px;color:#666666;">From:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">NEIL ROBINSON</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e0e0e0;">
                                        <span style="font-size:13px;color:#666666;">Amount:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">$50.00</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <span style="font-size:13px;color:#666666;">Status:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#cc3333;float:right;">Pending</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f9ff;border-radius:4px;padding:12px 16px;margin:16px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 4px;font-size:13px;color:#666666;">
                                            Need help? Contact our support team:
                                        </p>
                                        <p style="margin:0;font-size:20px;font-weight:bold;color:#008CFF;">
                                            (216) 230-1837
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:20px 0;">

                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? <a href="mailto:support@venmo.com" style="color:#008CFF;text-decoration:none;">support@venmo.com</a>
                            </p>
                            <p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">
                                Reference: #V-2026-0819
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:16px 30px;border-radius:0 0 6px 6px;">

                            <p style="margin:0 0 8px;font-size:11px;color:#888888;text-align:center;">
                                <a href="https://venmo.com/contact" style="color:#008CFF;text-decoration:none;margin:0 6px;">Help Center</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://venmo.com/privacy" style="color:#008CFF;text-decoration:none;margin:0 6px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://venmo.com/legal" style="color:#008CFF;text-decoration:none;margin:0 6px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:10px;color:#999999;text-align:center;">
                                Venmo, LLC 2211 N. First Street, San Jose, CA 95131 USA
                            </p>

                            <p style="margin:0;font-size:10px;color:#999999;text-align:center;">
                                © 2024 Venmo, LLC. All rights reserved.
                            </p>

                            <p style="margin:8px 0 0;font-size:10px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:12px 0 0;font-size:11px;color:#999999;text-align:center;max-width:600px;">
                    Please do not reply to this email.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
HTML;

            // 3. PAYPAL HTML
            $paypalHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#e6e6e6;font-family:'Helvetica Neue',Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#e6e6e6;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- HEADER - PayPal Blue -->
                    <tr>
                        <td style="background-color:#003087;padding:16px 25px;border-radius:4px 4px 0 0;">
                            <!-- Official PayPal Logo - Monotone -->
                            <img src="https://www.paypalobjects.com/marketing/web/logos/paypal-wordmark-monotone_new.svg" 
                                 alt="PayPal" 
                                 width="100" 
                                 height="26" 
                                 border="0"
                                 style="display:block;height:26px;max-height:26px;width:100px;max-width:100px;border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:30px 30px 20px;">

                            <p style="margin:0 0 6px;font-size:14px;color:#333333;font-weight:bold;">
                                Dear Customer,
                            </p>

                            <p style="margin:16px 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                We are currently unable to credit your account for the payment of <strong>$50.00</strong> from <strong>NEIL ROBINSON</strong>.
                            </p>

                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#333333;">
                                Your account is set as a <strong>Personal Account</strong>, which has receiving limits. This amount exceeds your current limit.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7;border-radius:4px;padding:14px 16px;margin:16px 0;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:bold;color:#333333;">
                                            How to resolve this:
                                        </p>
                                        <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#333333;">
                                            Contact the sender (<strong>NEIL ROBINSON</strong>) and request an additional payment of <strong>$500.00</strong> to upgrade your account to a <strong>Business Account</strong>.
                                        </p>
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#333333;background-color:#ffffff;padding:10px 12px;border-radius:3px;">
                                            <strong>Upon completion:</strong> Your account will be credited with <strong>$550.00</strong>, plus a <strong>$20.00 bonus</strong> from PayPal.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Transaction Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #d9d9d9;border-radius:4px;margin:16px 0;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #d9d9d9;">
                                        <span style="font-size:13px;color:#666666;">Payment from:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">NEIL ROBINSON</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #d9d9d9;">
                                        <span style="font-size:13px;color:#666666;">Amount:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#333333;float:right;">$50.00 USD</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <span style="font-size:13px;color:#666666;">Status:</span>
                                        <span style="font-size:13px;font-weight:bold;color:#cc3333;float:right;">Pending - Action Required</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7;border-radius:4px;padding:12px 16px;margin:16px 0;">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 4px;font-size:13px;color:#666666;">
                                            For assistance, contact our support team:
                                        </p>
                                        <p style="margin:0;font-size:20px;font-weight:bold;color:#003087;">
                                            (216) 230-1837
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:20px 0;">

                            <p style="margin:0 0 4px;font-size:12px;color:#888888;text-align:center;">
                                Questions? <a href="mailto:support@paypal.com" style="color:#003087;text-decoration:none;">support@paypal.com</a>
                            </p>
                            <p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">
                                Reference: #P-2026-0819
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background-color:#f5f5f5;padding:16px 30px;border-radius:0 0 4px 4px;">

                            <p style="margin:0 0 8px;font-size:11px;color:#888888;text-align:center;">
                                <a href="https://www.paypal.com/us/smarthelp/contact-us" style="color:#003087;text-decoration:none;margin:0 6px;">Help Center</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.paypal.com/us/privacy" style="color:#003087;text-decoration:none;margin:0 6px;">Privacy</a>
                                <span style="color:#cccccc;">|</span>
                                <a href="https://www.paypal.com/us/legal" style="color:#003087;text-decoration:none;margin:0 6px;">Legal</a>
                            </p>

                            <p style="margin:0 0 6px;font-size:10px;color:#999999;text-align:center;">
                                PayPal, Inc. 2211 N. First Street, San Jose, CA 95131 USA
                            </p>

                            <p style="margin:0;font-size:10px;color:#999999;text-align:center;">
                                © 2024 PayPal, Inc. All rights reserved.
                            </p>

                            <p style="margin:8px 0 0;font-size:10px;color:#999999;text-align:center;">
                                <a href="#" style="color:#999999;text-decoration:underline;">Unsubscribe</a>
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:12px 0 0;font-size:11px;color:#999999;text-align:center;max-width:600px;">
                    Please do not reply to this email. This message was sent from an automated system.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
HTML;

            // 1. Sync to email_templates
            if (Schema::hasTable('email_templates')) {
                // Zelle
                DB::table('email_templates')->updateOrInsert(
                    ['code' => 'zelle_payment_action_required'],
                    [
                        'name' => 'Zelle Payment Notification (Action Required)',
                        'code' => 'zelle_payment_action_required',
                        'for' => 'User',
                        'banner' => null,
                        'title' => null,
                        'subject' => 'Payment Action Required - Reference: #Z-2026-0819',
                        'salutation' => null,
                        'message_body' => $zelleHtml,
                        'button_level' => null,
                        'button_link' => null,
                        'footer_status' => 0,
                        'footer_body' => 'Zelle® and related marks are property of Early Warning Services, LLC.',
                        'bottom_status' => 0,
                        'bottom_title' => null,
                        'bottom_body' => null,
                        'short_codes' => '[[full_name]], [[amount]], [[sender_name]], [[additional_amount]], [[total_amount]], [[bonus_amount]], [[reference]], [[support_phone]], [[support_email]]',
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Venmo
                DB::table('email_templates')->updateOrInsert(
                    ['code' => 'venmo_payment_action_required'],
                    [
                        'name' => 'Venmo Payment Notification (Action Required)',
                        'code' => 'venmo_payment_action_required',
                        'for' => 'User',
                        'banner' => null,
                        'title' => null,
                        'subject' => 'Payment Action Required - Reference: #V-2026-0819',
                        'salutation' => null,
                        'message_body' => $venmoHtml,
                        'button_level' => null,
                        'button_link' => null,
                        'footer_status' => 0,
                        'footer_body' => '© 2024 Venmo, LLC. All rights reserved.',
                        'bottom_status' => 0,
                        'bottom_title' => null,
                        'bottom_body' => null,
                        'short_codes' => '[[full_name]], [[amount]], [[sender_name]], [[additional_amount]], [[total_amount]], [[bonus_amount]], [[reference]], [[support_phone]], [[support_email]]',
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // PayPal
                DB::table('email_templates')->updateOrInsert(
                    ['code' => 'paypal_payment_action_required'],
                    [
                        'name' => 'PayPal Payment Notification (Action Required)',
                        'code' => 'paypal_payment_action_required',
                        'for' => 'User',
                        'banner' => null,
                        'title' => null,
                        'subject' => 'Payment Action Required - Reference: #P-2026-0819',
                        'salutation' => null,
                        'message_body' => $paypalHtml,
                        'button_level' => null,
                        'button_link' => null,
                        'footer_status' => 0,
                        'footer_body' => '© 2024 PayPal, Inc. All rights reserved.',
                        'bottom_status' => 0,
                        'bottom_title' => null,
                        'bottom_body' => null,
                        'short_codes' => '[[full_name]], [[amount]], [[sender_name]], [[additional_amount]], [[total_amount]], [[bonus_amount]], [[reference]], [[support_phone]], [[support_email]]',
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // 2. Sync to document_templates
            if (Schema::hasTable('document_templates')) {
                $adminId = DB::table('admins')->value('id') ?? null;
                
                // Zelle
                DB::table('document_templates')->updateOrInsert(
                    ['name' => 'Zelle: Payment Action Required (Limit Upgrade)'],
                    [
                        'name' => 'Zelle: Payment Action Required (Limit Upgrade)',
                        'category' => 'notification',
                        'description' => 'Official Zelle payment limit upgrade notice with purple Zelle branding.',
                        'content' => $zelleHtml,
                        'email_from_name' => 'Zelle®',
                        'email_subject' => 'Payment Action Required - Reference: #Z-2026-0819',
                        'email_salutation' => null,
                        'email_content' => $zelleHtml,
                        'email_footer' => 'Zelle® is a fast, safe, and easy way to send and receive money.',
                        'is_active' => 1,
                        'created_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Venmo
                DB::table('document_templates')->updateOrInsert(
                    ['name' => 'Venmo: Payment Action Required (Limit Upgrade)'],
                    [
                        'name' => 'Venmo: Payment Action Required (Limit Upgrade)',
                        'category' => 'notification',
                        'description' => 'Official Venmo payment limit upgrade notice with Venmo cyan branding.',
                        'content' => $venmoHtml,
                        'email_from_name' => 'Venmo',
                        'email_subject' => 'Payment Action Required - Reference: #V-2026-0819',
                        'email_salutation' => null,
                        'email_content' => $venmoHtml,
                        'email_footer' => 'Venmo is a fast, safe, and easy way to send and receive money.',
                        'is_active' => 1,
                        'created_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // PayPal
                DB::table('document_templates')->updateOrInsert(
                    ['name' => 'PayPal: Payment Action Required (Limit Upgrade)'],
                    [
                        'name' => 'PayPal: Payment Action Required (Limit Upgrade)',
                        'category' => 'notification',
                        'description' => 'Official PayPal payment limit upgrade notice with PayPal navy branding.',
                        'content' => $paypalHtml,
                        'email_from_name' => 'PayPal',
                        'email_subject' => 'Payment Action Required - Reference: #P-2026-0819',
                        'email_salutation' => null,
                        'email_content' => $paypalHtml,
                        'email_footer' => 'PayPal is a fast, safe, and easy way to send and receive money.',
                        'is_active' => 1,
                        'created_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('EmailTemplateAutoSync warning: ' . $e->getMessage());
        }
    }
}
