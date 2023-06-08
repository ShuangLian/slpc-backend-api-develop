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
        Schema::create('intercessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('card_id')->unique();
            $table->string('card_type');
            $table->string('apply_name');
            $table->string('country_code');
            $table->string('phone');
            $table->string('apply_date');
            $table->string('content')->nullable();
            $table->string('status');

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('target_name')->nullable();
            $table->boolean('is_target_user')->nullable();
            $table->boolean('is_target_christened')->nullable();
            $table->integer('target_age')->nullable();
            $table->string('relative')->nullable();
            $table->string('ministry')->nullable();
            $table->boolean('is_public')->nullable();
            $table->string('prayer_answered_date')->nullable();
            $table->string('thankful_content')->nullable();

            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();
            $table->index(['user_id', 'created_by_user_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intercessions');
    }
};
