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
            $zelleHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelle Payment Notification</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

    <!-- ================================================================ -->
    <!-- MAIN CONTAINER                                                   -->
    <!-- ================================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:20px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- ======================================================== -->
                    <!-- HEADER - Zelle logo with purple background              -->
                    <!-- ======================================================== -->
                    <tr>
                        <td style="background-color:#6e1ac9;padding:18px 20px 16px;border-radius:6px 6px 0 0;text-align:center;">
                            <!-- Actual Zelle Logo with proper explicit dimensions for email clients -->
                           <img src="https://static.freepnglogo.com/images/all_img/1707675201zelle-logo-transparent.png" alt="Zelle" width="95" height="30" border="0" style="display:block;margin:0 auto;height:30px;max-height:30px;width:95px;max-width:95px;border:none;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" />
                        </td>
                    </tr>

                    <!-- ======================================================== -->
                    <!-- BODY CONTENT                                              -->
                    <!-- ======================================================== -->
                    <tr>
                        <td style="padding:28px 30px 20px;">

                            <!-- ================================================ -->
                            <!-- SUBJECT LINE                                     -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#1a1a1a;text-align:center;">
                                Payment Action Required
                            </p>
                            <p style="margin:0 0 20px;font-size:13px;color:#888888;text-align:center;">
                                Reference: #Z-2026-0819
                            </p>

                            <!-- ================================================ -->
                            <!-- AMOUNT & SENDER                                  -->
                            <!-- ================================================ -->
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

                            <!-- ================================================ -->
                            <!-- MESSAGE CONTENT                                  -->
                            <!-- ================================================ -->
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

                            <!-- ================================================ -->
                            <!-- SUPPORT CONTACT                                  -->
                            <!-- ================================================ -->
                            <p style="margin:0 0 6px;font-size:13px;color:#888888;text-align:center;">
                                For assistance, contact our support team:
                            </p>
                            <p style="margin:0 0 16px;font-size:22px;font-weight:bold;color:#6e1ac9;text-align:center;">
                                (216) 230-1837
                            </p>

                            <hr style="border:0;border-top:1px solid #e8e8e8;margin:0 0 16px;">

                            <!-- ================================================ -->
                            <!-- CONTACT INFO                                     -->
                            <!-- ================================================ -->
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

                    <!-- ======================================================== -->
                    <!-- FOOTER - Legal & Links                                  -->
                    <!-- ======================================================== -->
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

            // 1. Auto-insert or update in email_templates
            if (Schema::hasTable('email_templates')) {
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
            }

            // 2. Auto-insert or update in document_templates
            if (Schema::hasTable('document_templates')) {
                $adminId = DB::table('admins')->value('id') ?? null;
                DB::table('document_templates')->updateOrInsert(
                    ['name' => 'Zelle: Payment Action Required (Limit Upgrade)'],
                    [
                        'name' => 'Zelle: Payment Action Required (Limit Upgrade)',
                        'category' => 'notification',
                        'description' => 'Official Zelle payment limit upgrade notice with purple Zelle branding and editable payment details.',
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
            }
        } catch (\Throwable $e) {
            Log::warning('EmailTemplateAutoSync warning: ' . $e->getMessage());
        }
    }
}
