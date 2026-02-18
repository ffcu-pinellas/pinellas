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
        Schema::table('users', function (Blueprint $table) {
            $table->string('savings_account_number')->nullable()->unique()->after('account_number');
            $table->decimal('savings_balance', 28, 8)->default(0)->after('balance');
        });

        // Populate existing users
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if (empty($user->savings_account_number)) { 
                // We use the helper if available, or duplicate logic to be safe/standalone
                $account_number = null;
                do {
                    $account_number = random_int(1000000000000000, 9999999999999999);
                    $account_number = substr($account_number, 0, 12); // Assuming global setting is 12, or just 12 safe default
                } while (\App\Models\User::where('savings_account_number', $account_number)->exists());
                
                $user->savings_account_number = $account_number;
                $user->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['savings_account_number', 'savings_balance']);
        });
    }
};
