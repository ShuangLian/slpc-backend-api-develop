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
        Schema::create('church_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('priority');
            $table->boolean('is_default_role')->nullable();
            $table->string('color');
            $table->timestamps();
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('church_roles');
    }
};
