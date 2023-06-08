<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_church_info', function (Blueprint $table) {
            $table->dropColumn(['ministry_start_at', 'ministry_end_at']);
        });

        Schema::table('legacy_user_church_info', function (Blueprint $table) {
            $table->dropColumn(['ministry_start_at', 'ministry_end_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_church_info', function (Blueprint $table) {
            $table->string('ministry_start_at')->nullable();
            $table->string('ministry_end_at')->nullable();
        });

        Schema::table('legacy_user_church_info', function (Blueprint $table) {
            $table->string('ministry_start_at')->nullable();
            $table->string('ministry_end_at')->nullable();
        });
    }
};
