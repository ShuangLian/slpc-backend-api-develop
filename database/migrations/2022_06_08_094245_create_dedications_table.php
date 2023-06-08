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
        Schema::create('dedications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identify_id');
            $table->unsignedBigInteger('account_title_id');
            $table->string('amount');
            $table->string('receipt_number')->unique();
            $table->string('dedicate_date');
            $table->string('summary');
            $table->timestamps();
            $table->index(['identify_id', 'account_title_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dedications');
    }
};
