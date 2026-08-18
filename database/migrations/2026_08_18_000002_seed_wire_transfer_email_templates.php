<?php

use Illuminate\Database\Migrations\Migration;
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
                'name' => 'Wire Transfer Submitted',
                'code' => 'wire_transfer',
                'for' => 'User',
                'subject' => 'Wire Transfer Order Submitted - Ref #[[tnx]]',
                'title' => 'Wire Transfer Submitted',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your wire transfer instruction has been successfully recorded and is pending final clearing and funds settlement across the wire network.</p>
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0;">
                                        <h3 style="color: #00549b; margin-top: 0; margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Wire Transfer Summary</h3>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; line-height: 1.6;">
                                            <tr><td style="padding: 4px 0; color: #64748b;">Reference / Tracking ID:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; font-family: monospace; color: #1e293b;">[[tnx]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Funding Account:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[source_account]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Beneficiary Entity:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[beneficiary_name]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Receiving Bank:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[bank_name]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Routing / SWIFT:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[routing_or_swift]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Account / IBAN:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; font-family: monospace; color: #1e293b;">[[account_number]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Wire Principal:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[amount]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Wire Fee:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #00549b;">[[charge]]</td></tr>
                                            <tr style="border-top: 1px solid #cbd5e1;"><td style="padding: 8px 0 4px 0; font-weight: bold; color: #0f172a;">Total Debited:</td><td style="padding: 8px 0 4px 0; font-weight: bold; font-size: 15px; text-align: right; color: #00549b;">[[total_amount]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Settlement Status:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #d97706;">Pending Clearance</td></tr>
                                        </table>
                                    </div>
                                    <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 14px; border-radius: 4px; font-size: 12px; color: #1e40af; line-height: 1.4;">
                                        <strong>Settlement Notice:</strong> Outgoing domestic wire orders authorized prior to 3:00 PM EST on business days are processed same-day. International SWIFT transfers typically settle within 1–3 business days.
                                    </div>',
                'short_codes' => '[[full_name]], [[tnx]], [[source_account]], [[beneficiary_name]], [[bank_name]], [[routing_or_swift]], [[account_number]], [[amount]], [[charge]], [[total_amount]], [[date]], [[site_title]]',
                'status' => 1,
            ],
            [
                'name' => 'Wire Transfer Approved',
                'code' => 'wire_transfer_approved',
                'for' => 'User',
                'subject' => 'Wire Transfer Dispatched & Settlement Complete - Ref #[[tnx]]',
                'title' => 'Wire Transfer Completed',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your wire transfer instruction has been reviewed, authorized, and successfully dispatched across the wire clearing network.</p>
                                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 18px; margin: 20px 0;">
                                        <h3 style="color: #15803d; margin-top: 0; margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid #bbf7d0; padding-bottom: 8px;">Dispatched Wire Details</h3>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; line-height: 1.6;">
                                            <tr><td style="padding: 4px 0; color: #64748b;">Confirmation / Tracking ID:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; font-family: monospace; color: #1e293b;">[[tnx]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Beneficiary Entity:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[beneficiary_name]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Receiving Institution:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[bank_name]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Routing / SWIFT:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[routing_or_swift]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Dispatched Principal:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #15803d;">[[amount]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Settlement Fee:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[charge]]</td></tr>
                                            <tr style="border-top: 1px solid #bbf7d0;"><td style="padding: 8px 0 4px 0; font-weight: bold; color: #0f172a;">Total Debited:</td><td style="padding: 8px 0 4px 0; font-weight: bold; font-size: 15px; text-align: right; color: #15803d;">[[total_amount]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Execution Status:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #15803d;">Completed / Dispatched</td></tr>
                                        </table>
                                    </div>
                                    <p style="font-size: 12px; color: #64748b;">The receiving financial institution has been credited. Final availability of funds is subject to the receiving bank’s posting schedule.</p>',
                'short_codes' => '[[full_name]], [[tnx]], [[beneficiary_name]], [[bank_name]], [[routing_or_swift]], [[amount]], [[charge]], [[total_amount]], [[date]], [[site_title]]',
                'status' => 1,
            ],
            [
                'name' => 'Wire Transfer Rejected',
                'code' => 'wire_transfer_rejected',
                'for' => 'User',
                'subject' => 'Wire Transfer Order Cancelled & Refunded - Ref #[[tnx]]',
                'title' => 'Wire Order Cancelled',
                'salutation' => 'Hello [[full_name]],',
                'message_body' => '<p>Your wire transfer request could not be executed and has been cancelled. All previously held funds (principal plus processing fees) have been refunded directly to your account balance.</p>
                                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 18px; margin: 20px 0;">
                                        <h3 style="color: #b91c1c; margin-top: 0; margin-bottom: 12px; font-size: 15px; border-bottom: 1px solid #fecaca; padding-bottom: 8px;">Cancellation & Refund Notice</h3>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; line-height: 1.6;">
                                            <tr><td style="padding: 4px 0; color: #64748b;">Tracking ID:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; font-family: monospace; color: #1e293b;">[[tnx]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Beneficiary Entity:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #1e293b;">[[beneficiary_name]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Refunded Amount:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; font-size: 15px; color: #b91c1c;">[[total_amount]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Reason:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #b91c1c;">[[message]]</td></tr>
                                            <tr><td style="padding: 4px 0; color: #64748b;">Account Status:</td><td style="padding: 4px 0; font-weight: bold; text-align: right; color: #15803d;">Funds Restored to Balance</td></tr>
                                        </table>
                                    </div>
                                    <p style="font-size: 12px; color: #64748b;">If you need further assistance or wish to submit revised instructions, please contact member support.</p>',
                'short_codes' => '[[full_name]], [[tnx]], [[beneficiary_name]], [[total_amount]], [[message]], [[date]], [[site_title]]',
                'status' => 1,
            ],
        ];

        foreach ($templates as $tpl) {
            DB::table('email_templates')->updateOrInsert(
                ['code' => $tpl['code']],
                array_merge($tpl, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->whereIn('code', [
            'wire_transfer',
            'wire_transfer_approved',
            'wire_transfer_rejected',
        ])->delete();
    }
};
