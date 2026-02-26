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
                'for' => 'Admin',
                'title' => 'New Remote Deposit from [[full_name]]',
                'message_body' => 'A new remote deposit of [[amount]] has been submitted by [[full_name]] for review.',
                'short_codes' => '["[[full_name]]","[[amount]]","[[account_number]]"]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fund Transfer Submitted',
                'code' => 'fund_transfer_submitted',
                'for' => 'Admin',
                'title' => 'New [[type]] Transfer from [[full_name]]',
                'message_body' => 'A [[type]] transfer of [[amount]] to [[recipient]] has been initiated by [[full_name]].',
                'short_codes' => '["[[full_name]]","[[amount]]","[[type]]","[[recipient]]"]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wire Transfer Submitted',
                'code' => 'wire_transfer_submitted',
                'for' => 'Admin',
                'title' => 'New Wire Transfer from [[full_name]]',
                'message_body' => 'A new wire transfer request of [[amount]] has been submitted by [[full_name]].',
                'short_codes' => '["[[full_name]]","[[amount]]","[[swift_code]]"]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Card Activity Alert',
                'code' => 'card_activity_alert',
                'for' => 'Admin',
                'title' => 'Card Activity: [[message]]',
                'message_body' => 'Card activity alert for user [[full_name]]: [[message]]',
                'short_codes' => '["[[full_name]]","[[message]]","[[card_number]]"]',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('push_notification_templates')->updateOrInsert(
                ['code' => $template['code'], 'for' => 'Admin'],
                $template
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('push_notification_templates')
            ->whereIn('code', [
                'remote_deposit_submitted',
                'fund_transfer_submitted',
                'wire_transfer_submitted',
                'card_activity_alert'
            ])
            ->where('for', 'Admin')
            ->delete();
    }
};
