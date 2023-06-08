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
        Schema::create('user_church_info', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('membership_status')->nullable();
            $table->string('participation_status')->nullable();
            $table->string('membership_location')->nullable();
            $table->string('serving_experience')->nullable();
            $table->string('ministry_start_at')->nullable();
            $table->string('ministry_end_at')->nullable();
            $table->string('church_role')->nullable();
            $table->string('adulthood_christened_at')->nullable();
            $table->string('adulthood_christened_church')->nullable();
            $table->string('childhood_christened_at')->nullable();
            $table->string('childhood_christened_church')->nullable();
            $table->string('confirmed_at')->nullable();
            $table->string('confirmed_church')->nullable();
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
        Schema::dropIfExists('user_church_info');
    }
};
