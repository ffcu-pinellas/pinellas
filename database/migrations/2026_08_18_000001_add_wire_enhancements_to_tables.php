<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wire_transfars')) {
            Schema::table('wire_transfars', function (Blueprint $table) {
                if (!Schema::hasColumn('wire_transfars', 'status')) {
                    $table->tinyInteger('status')->default(1)->after('id');
                }
                if (!Schema::hasColumn('wire_transfars', 'international_charge')) {
                    $table->decimal('international_charge', 20, 2)->nullable()->after('charge');
                }
                if (!Schema::hasColumn('wire_transfars', 'international_charge_type')) {
                    $table->string('international_charge_type', 50)->nullable()->default('fixed')->after('charge_type');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'wire_transfer_status')) {
                    $table->tinyInteger('wire_transfer_status')->default(1)->after('transfer_status');
                }
                if (!Schema::hasColumn('users', 'custom_wire_min_limit')) {
                    $table->decimal('custom_wire_min_limit', 20, 2)->nullable()->after('wire_transfer_status');
                }
                if (!Schema::hasColumn('users', 'custom_wire_max_limit')) {
                    $table->decimal('custom_wire_max_limit', 20, 2)->nullable()->after('custom_wire_min_limit');
                }
                if (!Schema::hasColumn('users', 'custom_wire_daily_limit')) {
                    $table->decimal('custom_wire_daily_limit', 20, 2)->nullable()->after('custom_wire_max_limit');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('wire_transfars')) {
            Schema::table('wire_transfars', function (Blueprint $table) {
                $columns = ['status', 'international_charge', 'international_charge_type'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('wire_transfars', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['wire_transfer_status', 'custom_wire_min_limit', 'custom_wire_max_limit', 'custom_wire_daily_limit'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
