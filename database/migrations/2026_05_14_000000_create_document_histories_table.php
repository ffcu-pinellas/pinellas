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
        if (! Schema::hasTable('document_histories')) {
            Schema::create('document_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('title');
                $table->longText('content');
                $table->string('email_subject')->nullable();
                $table->string('email_salutation')->nullable();
                $table->longText('email_content')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('user_id');
                $table->index('created_at');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('document_histories');
    }
};
