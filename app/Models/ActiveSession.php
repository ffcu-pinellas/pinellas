<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ActiveSession extends Model
{
    protected $fillable = [
        'session_key',
        'session_id',
        'token_id',
        'guard',
        'authenticatable_id',
        'display_name',
        'username',
        'role_label',
        'ip_address',
        'city',
        'user_agent',
        'device_summary',
        'current_url',
        'is_kicked',
        'last_activity_at',
    ];

    protected $casts = [
        'is_kicked' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    protected static bool $schemaChecked = false;

    /**
     * Self-healing schema check so production never crashes before artisan migrate runs.
     */
    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        try {
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
            self::$schemaChecked = true;
        } catch (\Throwable $e) {
            Log::warning('ActiveSession::ensureSchema warning: ' . $e->getMessage());
        }
    }
}
