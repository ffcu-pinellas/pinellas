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
        DB::table('email_templates')
            ->where('code', 'fund_transfer_request')
            ->update([
                'message_body' => 'Your request to transfer <strong>[[amount]]</strong> from <strong>[[from_account]]</strong> to <strong>[[to_account]]</strong> has been received and is being processed.<br><br><strong>Transaction Details:</strong><br>Reference ID: <strong>[[tnx]]</strong><br>From Account: [[from_account]]<br>To Account: [[to_account]]<br>Amount: [[amount]]<br>Fee: [[charge]]<br>Total Debited: [[total_amount]]<br>Status: [[status]]<br>Initiated at: [[date]]<br>Memo: [[memo]]'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')
            ->where('code', 'fund_transfer_request')
            ->update([
                'message_body' => 'This is an official notification regarding your recent fund transfer request for <strong>[[amount]]</strong>. Your request status has been updated to: <strong>[[status]]</strong>.<br><br><strong>Transaction Details:</strong><br>From: [[from_account]]<br>To: [[to_account]]<br>Subtotal: [[amount]]<br>Deducted Amount: [[total_amount]]'
            ]);
    }
};
