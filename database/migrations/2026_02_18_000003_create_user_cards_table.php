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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_number', 16);
            $table->string('card_holder_name');
            $table->string('expiry_month', 2);
            $table->string('expiry_year', 4);
            $table->string('cvv', 4);
            $table->string('type')->default('Visa'); // Visa, Mastercard, etc.
            $table->string('status')->default('active'); // active, inactive, blocked
            $table->decimal('balance', 15, 2)->default(0); // If separate from main balance, otherwise usage limit
            $table->boolean('is_virtual')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
