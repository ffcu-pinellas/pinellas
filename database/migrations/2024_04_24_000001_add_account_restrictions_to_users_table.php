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
        Schema::table('users', function (Blueprint $box) {
            if (!Schema::hasColumn('users', 'checking_restricted')) $box->boolean('checking_restricted')->default(0);
            if (!Schema::hasColumn('users', 'savings_restricted')) $box->boolean('savings_restricted')->default(0);
            if (!Schema::hasColumn('users', 'ira_restricted')) $box->boolean('ira_restricted')->default(0);
            if (!Schema::hasColumn('users', 'heloc_restricted')) $box->boolean('heloc_restricted')->default(0);
            if (!Schema::hasColumn('users', 'cc_restricted')) $box->boolean('cc_restricted')->default(0);
            if (!Schema::hasColumn('users', 'loan_restricted')) $box->boolean('loan_restricted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $box) {
            $box->dropColumn([
                'checking_restricted',
                'savings_restricted',
                'ira_restricted',
                'heloc_restricted',
                'cc_restricted',
                'loan_restricted'
            ]);
        });
    }
};
