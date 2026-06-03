<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DocumentTemplate;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminId = Admin::first()->id ?? 1;

        $content = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to [[SITE_TITLE]]</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f4f8; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrap { width: 100%; padding: 24px 12px; box-sizing: border-box; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); }
        .header { background: linear-gradient(135deg, #00549b 0%, #002e5b 100%); padding: 24px 28px; text-align: left; }
        .logo { max-height: 40px; max-width: 240px; }
        .content { padding: 30px 28px; font-size: 15px; line-height: 1.65; color: #334155; }
        h1 { font-size: 22px; color: #0f172a; margin: 0 0 16px; font-weight: 700; }
        .intro { margin-bottom: 20px; color: #475569; }
        
        table.meta { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; margin-bottom: 15px; }
        table.meta td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.meta td:first-child { color: #64748b; width: 45%; font-weight: 500; }
        table.meta td:last-child { font-weight: 600; color: #0f172a; word-break: break-word; text-align: right; }
        
        .btn-wrap { text-align: center; margin: 28px 0 12px; }
        a.btn { display: inline-block; background: #00549b; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 6px rgba(0, 84, 155, 0.15); }
        .footer { padding: 20px 28px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        .muted { font-size: 11px; color: #94a3b8; margin-top: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="header">
            <a href="[[HOME_URL]]"><img src="[[LOGO_URL]]" alt="[[SITE_TITLE]]" class="logo"></a>
        </div>
        <div class="content">
            <h1>Official Membership & Account Activation</h1>
            <p class="intro">Dear [[FULL_NAME]],</p>
            <p class="intro">We are pleased to welcome you as a member of Pinellas Federal Credit Union. Your membership has been verified and your digital banking profile is now active. Below is your official account structure and routing transit credentials. Please secure this information for your records.</p>
            
            <table class="meta" cellpadding="0" cellspacing="0">
                <tr>
                    <td>Account Holder</td>
                    <td>[[FULL_NAME]]</td>
                </tr>
                <tr>
                    <td>Primary Checking Account</td>
                    <td>[[CHECKING_ACCOUNT_NUMBER]]</td>
                </tr>
                <tr>
                    <td>Primary Savings Account</td>
                    <td>[[SAVINGS_ACCOUNT_NUMBER]]</td>
                </tr>
                <tr>
                    <td>ABA Routing Transit Number</td>
                    <td>[[ROUTING_NUMBER]]</td>
                </tr>
            </table>

            <p style="font-size: 14px; color: #64748b; margin-top: 20px;">
                You can manage your balances, send funds via Zelle®, pay bills, and access electronic statements by logging into the secure portal.
            </p>

            <div class="btn-wrap">
                <a href="[[LOGIN_URL]]" class="btn">Access Online Banking</a>
            </div>
        </div>
        <div class="footer">
            <strong>[[SITE_TITLE]]</strong>
            <div class="muted">For your security, we will never ask for your full account number, password, or PIN by email.</div>
        </div>
    </div>
</div>
</body>
</html>
HTML;

        DocumentTemplate::updateOrCreate(
            ['category' => 'welcome_letter'],
            [
                'name' => 'Pinellas FCU Welcome Letter',
                'email_from_name' => 'Pinellas Federal Credit Union',
                'category' => 'welcome_letter',
                'description' => 'Automatic welcome email template sent to new users upon email verification containing account credentials.',
                'email_subject' => 'Welcome to Pinellas FCU - Your Account Details',
                'email_content' => $content,
                'content' => 'Welcome Member Letter',
                'is_active' => true,
                'created_by' => $adminId,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DocumentTemplate::where('category', 'welcome_letter')->delete();
    }
};
