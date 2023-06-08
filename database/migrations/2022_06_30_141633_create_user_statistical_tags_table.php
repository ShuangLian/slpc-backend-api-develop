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
        Schema::create('user_statistical_tags', function (Blueprint $table) {
            $table->id();
            $table->string('account_code');
            $table->string('tag_key');
            $table->integer('amount');
            $table->timestamps();
            $table->index(['account_code', 'tag_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_statistical_tags');
    }
};
