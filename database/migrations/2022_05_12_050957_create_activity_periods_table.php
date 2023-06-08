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
        Schema::create('activity_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->string('from_date');
            $table->string('to_date');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('rule');
            $table->string('type');
            $table->string('title');
            $table->string('presenter');
            $table->text('description');
            $table->string('registered_url')->nullable();
            $table->timestamps();
            $table->index('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_periods');
    }
};
