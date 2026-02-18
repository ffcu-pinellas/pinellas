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
        Schema::create('scheduled_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50)->comment('self, member, other');
            $table->string('wallet_type', 50)->default('default');
            $table->double('amount', 20, 2);
            $table->string('currency', 10);
            $table->double('charge', 20, 2)->default(0.00);
            $table->string('status', 20)->default('active')->comment('active, completed, cancelled, failed');
            $table->string('frequency', 20)->default('once')->comment('once, daily, weekly, monthly');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->json('meta_data')->nullable()->comment('beneficiary_id, manual_data, purpose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_transfers');
    }
};
