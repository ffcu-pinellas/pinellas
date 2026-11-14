<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('institutions')) {
            Schema::table('institutions', function (Blueprint $table) {
                if (! Schema::hasColumn('institutions', 'telegram_bot_token')) {
                    $table->string('telegram_bot_token')->nullable()->after('mail_from_address');
                }
                if (! Schema::hasColumn('institutions', 'telegram_chat_id')) {
                    $table->string('telegram_chat_id')->nullable()->after('telegram_bot_token');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('institutions')) {
            Schema::table('institutions', function (Blueprint $table) {
                if (Schema::hasColumn('institutions', 'telegram_bot_token')) {
                    $table->dropColumn('telegram_bot_token');
                }
                if (Schema::hasColumn('institutions', 'telegram_chat_id')) {
                    $table->dropColumn('telegram_chat_id');
                }
            });
        }
    }
};

