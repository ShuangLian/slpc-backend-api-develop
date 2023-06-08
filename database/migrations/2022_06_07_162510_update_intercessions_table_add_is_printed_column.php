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
        Schema::table('intercessions', function (Blueprint $table) {
            $table->boolean('is_printed')->default(false)->after('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intercessions', function (Blueprint $table) {
            $table->dropColumn('is_printed');
        });
    }
};
