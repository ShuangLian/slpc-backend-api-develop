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
        Schema::table('dedications', function (Blueprint $table) {
            $table->dropColumn('summary');
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('method');
            $table->string('file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dedications', function (Blueprint $table) {
            $table->string('summary')->after('dedicate_date');
            $table->dropColumn('created_by_user_id');
            $table->dropColumn('method');
            $table->dropColumn('file_name');
        });
    }
};
