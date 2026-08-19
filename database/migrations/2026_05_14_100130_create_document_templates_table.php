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
        if (! Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->default('general');
                $table->text('description')->nullable();
                $table->longText('content');
                $table->string('email_subject')->nullable();
                $table->string('email_salutation')->nullable();
                $table->longText('email_content')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('category');
                $table->index('created_by');
                $table->index('is_active');
                $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
