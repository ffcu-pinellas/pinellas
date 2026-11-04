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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'dividend_status')) {
                    $table->boolean('dividend_status')->default(0)->after('savings_balance');
                }
                if (!Schema::hasColumn('users', 'dividend_apy')) {
                    $table->decimal('dividend_apy', 5, 2)->nullable()->after('dividend_status');
                }
                if (!Schema::hasColumn('users', 'dividend_wallet')) {
                    $table->string('dividend_wallet', 50)->default('primary_savings')->after('dividend_apy');
                }
                if (!Schema::hasColumn('users', 'last_dividend_at')) {
                    $table->timestamp('last_dividend_at')->nullable()->after('dividend_wallet');
                }
            });
        }

        if (!Schema::hasTable('dividend_logs')) {
            Schema::create('dividend_logs', function (Blueprint $table) {
                $table->id();
                $table->string('batch_id', 64)->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->decimal('balance_before', 20, 2)->default(0.00);
                $table->decimal('apy_rate', 5, 2)->default(0.00);
                $table->decimal('amount', 20, 2)->default(0.00);
                $table->decimal('balance_after', 20, 2)->default(0.00);
                $table->string('wallet_type', 50)->default('primary_savings');
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->string('triggered_by', 50)->default('system');
                $table->string('status', 20)->default('success');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['dividend_status', 'dividend_apy', 'dividend_wallet', 'last_dividend_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('dividend_logs');
    }
};

