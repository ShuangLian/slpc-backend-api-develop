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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('country_code')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->dropUnique('user_profiles_identify_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('country_code')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->string('identify_id')->unique()->change();
        });
    }
};
