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
        Schema::table('church_roles', function (Blueprint $table) {
            $table->string('background_color')->after('color');
            $table->string('border_color')->after('color');
            $table->renameColumn('color', 'text_color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('church_roles', function (Blueprint $table) {
            $table->dropColumn('background_color');
            $table->dropColumn('border_color');
            $table->renameColumn('text_color', 'color');
        });
    }
};
