<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'passkey_credentials')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('passkey_credentials')->nullable()->after('security_preference');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'passkey_credentials')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('passkey_credentials');
            });
        }
    }
};
