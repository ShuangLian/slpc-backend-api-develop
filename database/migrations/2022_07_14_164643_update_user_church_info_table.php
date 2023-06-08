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
            $table->dropColumn('church_role');
            $table->string('skill')->nullable();
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
            $table->string('church_role');
            $table->dropColumn('skill');
        });
    }
};
