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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_relatives', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_church_info', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('intercessions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('activity_checkins', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('dedications', function (Blueprint $table) {
            $table->softDeletes();
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
            $table->dropSoftDeletes();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('user_relatives', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('user_church_info', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('intercessions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('activity_checkins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('dedications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
