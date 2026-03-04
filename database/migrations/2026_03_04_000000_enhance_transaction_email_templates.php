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
                'name' => 'Member Transfer Approved',
                'code' => 'member_transfer_approved',
                'subject' => 'Transaction Receipt: Member Transfer Successful',
                'title' => 'Transfer Successful',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your internal transfer to another Pinellas FCU member has been processed successfully.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Transaction Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Recipient Name:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[recipient_name]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Recipient Account:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[recipient_account]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;">[[amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Fee:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[charge]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Total Debited:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[total_amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Date:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[date]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Memo:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[memo]]</td></tr>
                                        </table>
                                    </div>',
                'status' => 1,
            ],
            [
                'name' => 'Member Transfer Rejected',
                'code' => 'member_transfer_rejected',
                'subject' => 'Transaction Alert: Member Transfer Rejected',
                'title' => 'Transfer Rejected',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your internal transfer request has been rejected. The funds have been returned to your balance.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #d92b1c;">Rejection Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Reason:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #d92b1c;">[[message]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Recipient:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[recipient_name]]</td></tr>
                                        </table>
                                    </div>',
                'status' => 1,
            ],
            [
                'name' => 'External Transfer Approved',
                'code' => 'external_transfer_approved',
                'subject' => 'Transaction Receipt: External Bank Transfer Successful',
                'title' => 'External Transfer Successful',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your transfer to an external bank account has been processed successfully.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Transaction Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Bank Name:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[bank_name]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Account Name:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[account_name]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Account Number:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[account_number]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Routing Number:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[routing_number]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;">[[amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Total Debited:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[total_amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Memo:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[memo]]</td></tr>
                                        </table>
                                    </div>',
                'status' => 1,
            ],
            [
                'name' => 'External Transfer Rejected',
                'code' => 'external_transfer_rejected',
                'subject' => 'Transaction Alert: External Transfer Rejected',
                'title' => 'External Transfer Rejected',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your external bank transfer request has been rejected. The funds have been returned to your balance.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #d92b1c;">Rejection Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Reason:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #d92b1c;">[[message]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Bank Name:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[bank_name]]</td></tr>
                                        </table>
                                    </div>',
                'status' => 1,
            ],
            [
                'name' => 'Remote Deposit Approved',
                'code' => 'remote_deposit_approved',
                'subject' => 'Deposit Receipt: Mobile Check Deposit Successful',
                'title' => 'Check Deposit Successful',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your mobile check deposit has been processed and the funds are now available in your account.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #00549b;">Deposit Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Check Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;">[[check_amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Capture Date:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[capture_date]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Transaction ID:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[txn]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Status:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #28a745;">Approved</td></tr>
                                        </table>
                                    </div>',
                'status' => 1,
            ],
            [
                'name' => 'Remote Deposit Rejected',
                'code' => 'remote_deposit_rejected',
                'subject' => 'Deposit Alert: Mobile Check Deposit Rejected',
                'title' => 'Check Deposit Rejected',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your mobile check deposit has been rejected. Please review the reason below.</p>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                        <h4 style="margin-top: 0; color: #d92b1c;">Rejection Details</h4>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr><td style="padding: 5px 0; color: #666;">Reason:</td><td style="padding: 5px 0; font-weight: bold; text-align: right; color: #d92b1c;">[[message]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Check Amount:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[check_amount]]</td></tr>
                                            <tr><td style="padding: 5px 0; color: #666;">Capture Date:</td><td style="padding: 5px 0; font-weight: bold; text-align: right;">[[capture_date]]</td></tr>
                                        </table>
                                    </div>
                                    <p style="color: #666; font-size: 13px;">Note: A returned check fee may have been applied to your account as per our fee schedule.</p>',
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
        $codes = [
            'member_transfer_approved',
            'member_transfer_rejected',
            'external_transfer_approved',
            'external_transfer_rejected',
            'remote_deposit_approved',
            'remote_deposit_rejected'
        ];
        
        DB::table('email_templates')->whereIn('code', $codes)->delete();
    }
};
