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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_user_id');
            $table->unsignedBigInteger('visit_reason_id');
            $table->string('status');
            $table->string('visit_date');
            $table->string('visit_title')->nullable();
            $table->string('visit_type')->nullable();
            $table->string('created_by')->nullable();
            $table->string('attend_people')->nullable();
            $table->text('detail_record')->nullable();
            $table->string('image1_url')->nullable();
            $table->string('image2_url')->nullable();
            $table->string('image3_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('target_user_id');
            $table->index('visit_reason_id');
            $table->index('status');
            $table->index('visit_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
};
