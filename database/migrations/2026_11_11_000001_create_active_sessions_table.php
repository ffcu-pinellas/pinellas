<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('active_sessions')) {
            Schema::create('active_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_key', 128)->unique();
                $table->string('session_id', 120)->nullable()->index();
                $table->unsignedBigInteger('token_id')->nullable()->index();
                $table->string('guard', 30)->index();
                $table->unsignedBigInteger('authenticatable_id')->index();
                $table->string('display_name', 150)->nullable();
                $table->string('username', 100)->nullable();
                $table->string('role_label', 60)->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('device_summary', 120)->nullable();
                $table->string('current_url', 255)->nullable();
                $table->boolean('is_kicked')->default(false)->index();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->timestamps();

                $table->index(['guard', 'authenticatable_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('active_sessions');
    }
};
