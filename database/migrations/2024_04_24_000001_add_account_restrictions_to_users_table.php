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
            $box->boolean('checking_restricted')->default(0);
            $box->boolean('savings_restricted')->default(0);
            $box->boolean('ira_restricted')->default(0);
            $box->boolean('heloc_restricted')->default(0);
            $box->boolean('cc_restricted')->default(0);
            $box->boolean('loan_restricted')->default(0);
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
