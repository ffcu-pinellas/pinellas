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
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'onboarding_code')) {
                    $table->string('onboarding_code', 100)->nullable()->unique()->after('status');
                }
                if (!Schema::hasColumn('admins', 'onboarding_views_count')) {
                    $table->unsignedInteger('onboarding_views_count')->default(0)->after('onboarding_code');
                }
                if (!Schema::hasColumn('admins', 'onboarding_signups_count')) {
                    $table->unsignedInteger('onboarding_signups_count')->default(0)->after('onboarding_views_count');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $columns = ['onboarding_code', 'onboarding_views_count', 'onboarding_signups_count'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('admins', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
