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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('name');
            $table->string('identify_id')->nullable()->unique();
            $table->string('birthday');
            $table->string('avatar_url')->nullable();
            $table->string('country_code');
            $table->string('phone_number');
            $table->string('gender');
            $table->boolean('is_married')->nullable();
            $table->string('company_phone_number')->nullable();
            $table->string('home_phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('line_id')->nullable();
            $table->string('job_title')->nullable();
            $table->string('highest_education')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('address')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->index('user_id');
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
        Schema::dropIfExists('user_profiles');
    }
};
