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
        Schema::create('email_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('document_history_id')->nullable();
            $table->string('recipient_email');
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'failed', 'bounced'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('tracking_token')->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('document_history_id');
            $table->index('status');
            $table->index('tracking_token');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_history_id')->references('id')->on('document_histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_trackings');
    }
};
