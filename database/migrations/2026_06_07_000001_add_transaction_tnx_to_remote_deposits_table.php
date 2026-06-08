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
        if (Schema::hasTable('remote_deposits')) {
            Schema::table('remote_deposits', function (Blueprint $table) {
                if (!Schema::hasColumn('remote_deposits', 'transaction_tnx')) {
                    $table->string('transaction_tnx')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('remote_deposits')) {
            Schema::table('remote_deposits', function (Blueprint $table) {
                if (Schema::hasColumn('remote_deposits', 'transaction_tnx')) {
                    $table->dropColumn('transaction_tnx');
                }
            });
        }
    }
};
