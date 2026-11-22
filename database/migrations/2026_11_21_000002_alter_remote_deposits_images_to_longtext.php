<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Compatible with MySQL/MariaDB (Render, Hostinger, cPanel) and SQLite.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('remote_deposits')) {
            try {
                // Direct native statement handles MySQL/MariaDB without requiring doctrine/dbal
                DB::statement("ALTER TABLE `remote_deposits` MODIFY COLUMN `front_image` LONGTEXT NULL, MODIFY COLUMN `back_image` LONGTEXT NULL");
            } catch (\Throwable $e) {
                try {
                    Schema::table('remote_deposits', function (Blueprint $table) {
                        $table->longText('front_image')->nullable()->change();
                        $table->longText('back_image')->nullable()->change();
                    });
                } catch (\Throwable $e2) {
                    \Log::warning('alter_remote_deposits_images_to_longtext fallback notice: ' . $e2->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('remote_deposits')) {
            try {
                DB::statement("ALTER TABLE `remote_deposits` MODIFY COLUMN `front_image` VARCHAR(255) NULL, MODIFY COLUMN `back_image` VARCHAR(255) NULL");
            } catch (\Throwable $e) {}
        }
    }
};
