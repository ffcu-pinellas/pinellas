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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ira_status')->default(0)->after('savings_balance');
            $table->string('ira_account_number')->nullable()->after('ira_status');
            $table->decimal('ira_balance', 28, 8)->default(0)->after('ira_account_number');
            $table->boolean('heloc_status')->default(0)->after('ira_balance');
            $table->string('heloc_account_number')->nullable()->after('heloc_status');
            $table->decimal('heloc_balance', 28, 8)->default(0)->after('heloc_account_number');
            $table->decimal('heloc_credit_limit', 28, 8)->default(0)->after('heloc_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ira_status',
                'ira_account_number',
                'ira_balance',
                'heloc_status',
                'heloc_account_number',
                'heloc_balance',
                'heloc_credit_limit'
            ]);
        });
    }
};
