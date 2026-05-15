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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_history_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->enum('signature_type', ['electronic', 'digital', 'wet_ink'])->default('electronic');
            $table->string('signature_provider')->nullable(); // DocuSign, HelloSign, etc.
            $table->string('external_signature_id')->nullable();
            $table->text('signature_data')->nullable(); // Base64 signature image or digital signature data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['pending', 'signed', 'declined', 'expired'])->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('metadata')->nullable(); // Additional signing metadata
            $table->softDeletes();
            $table->timestamps();

            $table->index('document_history_id');
            $table->index('user_id');
            $table->index('signed_by');
            $table->index('status');
            $table->index('external_signature_id');
            $table->foreign('document_history_id')->references('id')->on('document_histories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('signed_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
