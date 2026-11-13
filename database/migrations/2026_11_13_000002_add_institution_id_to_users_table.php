<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'institution_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Nullable so existing users are not broken; backfilled below
                $table->unsignedBigInteger('institution_id')->nullable()->after('id')->index();
            });

            // Backfill every existing user to FrontField (the default institution)
            $defaultId = \DB::table('institutions')->where('is_default', true)->value('id');
            if ($defaultId) {
                \DB::table('users')->whereNull('institution_id')->update(['institution_id' => $defaultId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('institution_id');
        });
    }
};

