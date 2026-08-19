<?php

namespace App\Services;

use App\Models\ZelleSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                try {
                    if (! DB::table('zelle_settings')->exists()) {
                        DB::table('zelle_settings')->insert([
                            'status' => 1,
                            'minimum_transfer' => 1.00,
                            'maximum_transfer' => 100000.00,
                            'daily_limit_maximum_amount' => 100000.00,
                            'daily_limit_maximum_count' => 100,
                            'monthly_limit_maximum_amount' => 2500000.00,
                            'monthly_limit_maximum_count' => 500,
                            'charge' => 0.00,
                            'charge_type' => 'fixed',
                            'instructions' => '<p>Zelle® payments are sent directly from your account. Payments sent to registered recipients typically arrive in minutes.</p>',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {}
            }

            // 3. Ensure custom limits and status columns exist in `users` table safely
            if (Schema::hasTable('users')) {
                $columnsToAdd = [];
                if (! Schema::hasColumn('users', 'zelle_transfer_status')) {
                    $columnsToAdd['zelle_transfer_status'] = 'TINYINT(1) NOT NULL DEFAULT 1';
                }
                if (! Schema::hasColumn('users', 'custom_zelle_min_limit')) {
                    $columnsToAdd['custom_zelle_min_limit'] = 'DECIMAL(20,2) NULL DEFAULT NULL';
                }
                if (! Schema::hasColumn('users', 'custom_zelle_max_limit')) {
                    $columnsToAdd['custom_zelle_max_limit'] = 'DECIMAL(20,2) NULL DEFAULT NULL';
                }
                if (! Schema::hasColumn('users', 'custom_zelle_daily_limit')) {
                    $columnsToAdd['custom_zelle_daily_limit'] = 'DECIMAL(20,2) NULL DEFAULT NULL';
                }
                if (! Schema::hasColumn('users', 'custom_zelle_monthly_limit')) {
                    $columnsToAdd['custom_zelle_monthly_limit'] = 'DECIMAL(20,2) NULL DEFAULT NULL';
                }

                foreach ($columnsToAdd as $col => $sqlType) {
                    try {
                        DB::statement("ALTER TABLE `users` ADD COLUMN `{$col}` {$sqlType}");
                    } catch (\Throwable $e) {
                        // In case DB::statement has permission constraints, fallback to Schema::table
                        try {
                            Schema::table('users', function (Blueprint $table) use ($col) {
                                if ($col === 'zelle_transfer_status') {
                                    $table->tinyInteger('zelle_transfer_status')->default(1)->nullable();
                                } elseif (str_starts_with($col, 'custom_zelle_')) {
                                    $table->decimal($col, 20, 2)->nullable();
                                }
                            });
                        } catch (\Throwable $ex) {}
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ZelleSettingAutoSync warning: ' . $e->getMessage());
        }
    }
}
