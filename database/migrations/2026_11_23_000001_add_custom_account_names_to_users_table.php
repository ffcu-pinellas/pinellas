<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'checking_custom_name')) {
                $table->string('checking_custom_name', 150)->nullable()->after('account_number');
            }
            if (!Schema::hasColumn('users', 'savings_custom_name')) {
                $table->string('savings_custom_name', 150)->nullable()->after('savings_account_number');
            }
            if (!Schema::hasColumn('users', 'ira_custom_name')) {
                $table->string('ira_custom_name', 150)->nullable()->after('ira_account_number');
            }
            if (!Schema::hasColumn('users', 'heloc_custom_name')) {
                $table->string('heloc_custom_name', 150)->nullable()->after('heloc_account_number');
            }
            if (!Schema::hasColumn('users', 'cc_custom_name')) {
                $table->string('cc_custom_name', 150)->nullable()->after('cc_account_number');
            }
            if (!Schema::hasColumn('users', 'loan_custom_name')) {
                $table->string('loan_custom_name', 150)->nullable()->after('loan_account_number');
            }
            if (!Schema::hasColumn('users', 'account_apy_badges')) {
                $table->json('account_apy_badges')->nullable()->after('loan_custom_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'checking_custom_name',
                'savings_custom_name',
                'ira_custom_name',
                'heloc_custom_name',
                'cc_custom_name',
                'loan_custom_name',
                'account_apy_badges',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
