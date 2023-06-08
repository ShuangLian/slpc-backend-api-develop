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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('avatar_url', 'dashboard_avatar_url');
            $table->string('liff_avatar_url')->nullable()->after('avatar_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('dashboard_avatar_url', 'avatar_url');
            $table->dropColumn('liff_avatar_url');
        });
    }
};
