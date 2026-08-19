<?php

namespace App\Services;

use App\Models\ZelleSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ZelleSettingAutoSync
{
    public static function sync(): void
    {
        try {
            // 1. Ensure `zelle_settings` table exists
            if (! Schema::hasTable('zelle_settings')) {
                Schema::create('zelle_settings', function (Blueprint $table) {
                    $table->id();
                    $table->boolean('status')->default(1);
                    $table->decimal('minimum_transfer', 20, 2)->default(1.00);
                    $table->decimal('maximum_transfer', 20, 2)->default(2500.00);
                    $table->decimal('daily_limit_maximum_amount', 20, 2)->default(2500.00);
                    $table->integer('daily_limit_maximum_count')->default(10);
                    $table->decimal('monthly_limit_maximum_amount', 20, 2)->default(10000.00);
                    $table->integer('monthly_limit_maximum_count')->default(50);
                    $table->decimal('charge', 20, 2)->default(0.00);
                    $table->string('charge_type')->default('fixed');
                    $table->text('instructions')->nullable();
                    $table->timestamps();
                });
            }

            // 2. Ensure initial default record exists in `zelle_settings`
            if (Schema::hasTable('zelle_settings')) {
                ZelleSetting::getSettings();
            }

            // 3. Ensure custom limits and status columns exist in `users` table
            if (Schema::hasTable('users')) {
                Schema::table('users', function (Blueprint $table) {
                    if (! Schema::hasColumn('users', 'zelle_transfer_status')) {
                        $table->tinyInteger('zelle_transfer_status')->default(1)->after('wire_transfer_status');
                    }
                    if (! Schema::hasColumn('users', 'custom_zelle_min_limit')) {
                        $table->decimal('custom_zelle_min_limit', 20, 2)->nullable()->after('zelle_transfer_status');
                    }
                    if (! Schema::hasColumn('users', 'custom_zelle_max_limit')) {
                        $table->decimal('custom_zelle_max_limit', 20, 2)->nullable()->after('custom_zelle_min_limit');
                    }
                    if (! Schema::hasColumn('users', 'custom_zelle_daily_limit')) {
                        $table->decimal('custom_zelle_daily_limit', 20, 2)->nullable()->after('custom_zelle_max_limit');
                    }
                    if (! Schema::hasColumn('users', 'custom_zelle_monthly_limit')) {
                        $table->decimal('custom_zelle_monthly_limit', 20, 2)->nullable()->after('custom_zelle_daily_limit');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('ZelleSettingAutoSync warning: ' . $e->getMessage());
        }
    }
}
