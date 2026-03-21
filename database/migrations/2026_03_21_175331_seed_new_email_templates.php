<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $templates = [
            [
                'name' => 'Remote Deposit Submitted',
                'code' => 'remote_deposit_submitted',
                'subject' => 'Check Deposit Received - [[site_title]]',
                'title' => 'Check Received',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>We have received your mobile check deposit of [[amount]] to your [[account_name]].</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Deposit Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;">[[amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Account:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[account_name]] ([[account_number]])</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Transaction ID:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[txn]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Status:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #ffc107;">Pending Review</td></tr>
                                        </table>
                                    </div>
                                    <p>Our team is now reviewing the deposit. You will receive another notification once it is processed and completed or if more information is needed.</p>',
                'status' => 1,
            ],
            [
                'name' => 'Card Status Update',
                'code' => 'card_status_update',
                'subject' => 'Security Alert: Card Status Changed',
                'title' => 'Card Update',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>A status change was detected for your card ending in [[card_number]].</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Update Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Card Number:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[card_number]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">New Status:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[status]]</td></tr>
                                        </table>
                                    </div>
                                    <p>[[message]]</p>
                                    <p style="color: #666; font-size: 13px;">If you did not authorize this change, please contact support immediately.</p>',
                'status' => 1,
            ],
            [
                'name' => 'Card Security Update',
                'code' => 'card_security_update',
                'subject' => 'Security Alert: Card Security Action',
                'title' => 'Security Update',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>A security action was performed on your card ending in [[card_number]].</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Action Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Card Number:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[card_number]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Action:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[action]]</td></tr>
                                        </table>
                                    </div>
                                    <p>[[message]]</p>
                                    <p style="color: #666; font-size: 13px;">If you did not authorize this action, please contact support immediately.</p>',
                'status' => 1,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['code' => $template['code']],
                $template
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->whereIn('code', [
            'remote_deposit_submitted',
            'card_status_update',
            'card_security_update'
        ])->delete();
    }
};
