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
        Schema::create('legacy_user_relatives', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('relationship');
            $table->string('name');
            $table->boolean('is_alive');
            $table->boolean('is_christened');
            $table->string('christened_church')->nullable();
            $table->index('user_id');
            $table->timestamps();
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
        Schema::dropIfExists('legacy_user_relatives');
    }
};
