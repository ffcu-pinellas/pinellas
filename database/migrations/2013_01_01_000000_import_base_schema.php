<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        // Only import if the database is empty/fresh (meaning the core 'users' table doesn't exist yet)
        if (!Schema::hasTable('users')) {
            $path = database_path('sql/base_schema.sql');
            
            if (file_exists($path)) {
                // Temporarily disable constraint checks for bulk import
                Schema::disableForeignKeyConstraints();
                DB::statement('SET UNIQUE_CHECKS=0;');

                // Run SQL base schema
                $sql = file_get_contents($path);
                DB::unprepared($sql);

                // Re-enable constraint checks
                DB::statement('SET UNIQUE_CHECKS=1;');
                Schema::enableForeignKeyConstraints();
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
        // Reversing a base schema import is not typically performed table-by-table.
        // It can be bypassed or handled by refreshing the database.
    }
};
