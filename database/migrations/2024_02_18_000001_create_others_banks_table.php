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
        Schema::create('others_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('processing_time')->nullable();
            $table->string('processing_type')->nullable(); // 'unspecified'
            $table->decimal('charge', 15, 2)->default(0);
            $table->string('charge_type')->default('percent');
            $table->decimal('minimum_transfer', 15, 2)->default(0);
            $table->decimal('maximum_transfer', 15, 2)->default(0);
            $table->decimal('daily_limit_maximum_amount', 15, 2)->default(0);
            $table->integer('daily_limit_maximum_count')->default(0);
            $table->decimal('monthly_limit_maximum_amount', 15, 2)->default(0);
            $table->integer('monthly_limit_maximum_count')->default(0);
            $table->text('field_options')->nullable();
            $table->text('details')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('others_banks');
    }
};
