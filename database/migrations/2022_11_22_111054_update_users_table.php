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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false);
            $table->boolean('is_matched')->nullable();
            $table->dateTime('matched_at')->nullable();
            $table->unsignedBigInteger('matched_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_legacy', 'is_matched', 'matched_at', 'matched_user_id']);
        });
    }
};
