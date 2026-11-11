<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_signatures')) {
            try {
                DB::statement('ALTER TABLE `document_signatures` MODIFY COLUMN `signature_data` LONGTEXT NULL');
                DB::statement('ALTER TABLE `document_signatures` MODIFY COLUMN `metadata` LONGTEXT NULL');
            } catch (\Throwable $e) {
                \Log::warning('document_signatures column widen skipped: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Preserve widened columns safely
    }
};
