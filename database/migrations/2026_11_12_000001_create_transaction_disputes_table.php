<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transaction_disputes')) {
            Schema::create('transaction_disputes', function (Blueprint $table) {
                $table->id();
                $table->string('claim_number', 32)->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('transaction_id')->index();
                $table->string('tnx', 64)->index();
                $table->string('wallet_type', 32)->default('default');
                $table->decimal('disputed_amount', 20, 2);
                $table->string('category', 64);
                $table->string('other_reason', 255)->nullable();
                $table->text('description');
                $table->string('merchant_name')->nullable();
                $table->string('evidence_path')->nullable();
                $table->string('status', 32)->default('open')->index();
                $table->string('provisional_credit_tnx', 64)->nullable();
                $table->string('clawback_tnx', 64)->nullable();
                $table->timestamp('provisional_credited_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->string('reviewed_via', 32)->nullable();
                $table->text('resolution_notes')->nullable();
                $table->json('timeline')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_disputes');
    }
};
