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
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('type', 30)->change();
            $table->string('status', 13)->change();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 5)->change();
            $table->string('username', 30)->change();
            $table->string('password', 60)->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->string('activity_type', 9)->change();
            $table->string('date', 10)->nullable()->change();
            $table->string('time', 13)->nullable()->change();
        });

        Schema::table('activity_categories', function (Blueprint $table) {
            $table->string('activity_type', 9)->change();
        });

        Schema::table('activity_checkins', function (Blueprint $table) {
            $table->string('activity_type', 9)->change();
        });

        Schema::table('activity_periods', function (Blueprint $table) {
            $table->string('from_date', 10)->change();
            $table->string('to_date', 10)->change();
            $table->string('start_time', 5)->change();
            $table->string('end_time', 5)->change();
            $table->string('rule', 33)->change();
        });

        Schema::table('church_roles', function (Blueprint $table) {
            $table->string('text_color', 9)->change();
            $table->string('border_color', 9)->change();
            $table->string('background_color', 9)->change();
        });

        Schema::table('country_codes', function (Blueprint $table) {
            $table->string('code', 3)->change();
        });

        Schema::table('dedications', function (Blueprint $table) {
            $table->string('identify_id', 10)->change();
            $table->string('receipt_number', 10)->change();
            $table->string('dedicate_date', 10)->change();
            $table->string('method', 6)->change();
        });

        Schema::table('intercessions', function (Blueprint $table) {
            $table->string('card_id', 6)->change();
            $table->string('card_type', 9)->change();
            $table->string('country_code', 3)->change();
            $table->string('phone', 10)->change();
            $table->string('apply_date', 10)->change();
            $table->string('status', 10)->change();
            $table->string('ministry', 5)->nullable()->change();
            $table->string('prayer_answered_date', 10)->nullable()->change();
        });

        Schema::table('permission_constraints', function (Blueprint $table) {
            $table->string('page', 12)->change();
            $table->string('action', 6)->change();
        });

        Schema::table('user_church_info', function (Blueprint $table) {
            $table->string('membership_status', 27)->nullable()->change();
            $table->integer('participation_status')->nullable()->change();
            $table->string('membership_location', 11)->nullable()->change();
            $table->unsignedBigInteger('zone')->nullable()->change();
            $table->string('adulthood_christened_at', 10)->nullable()->change();
            $table->string('childhood_christened_at', 10)->nullable()->change();
            $table->string('confirmed_at', 10)->nullable()->change();
            $table->string('skill', 50)->nullable()->change();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('identify_id', 10)->nullable()->change();
            $table->string('birthday', 10)->change();
            $table->string('country_code', 3)->change();
            $table->string('phone_number', 20)->change();
            $table->string('gender', 6)->nullable()->change();
            $table->string('company_area_code', 3)->nullable()->change();
            $table->string('company_phone_number', 20)->nullable()->change();
            $table->string('home_area_code', 3)->nullable()->change();
            $table->string('home_phone_number', 20)->nullable()->change();
        });

        Schema::table('user_relatives', function (Blueprint $table) {
            $table->string('relationship', 6)->change();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->string('status', 10)->change();
            $table->string('visit_date', 10)->change();
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->string('church_type', 11)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('type')->comment('')->change();
            $table->string('status')->change();
            $table->string('reviewed_by_user_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->change();
            $table->string('username')->change();
            $table->string('password')->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->string('activity_type')->change();
            $table->string('date')->nullable()->change();
            $table->string('time')->nullable()->change();
        });

        Schema::table('activity_categories', function (Blueprint $table) {
            $table->string('activity_type')->change();
        });

        Schema::table('activity_checkins', function (Blueprint $table) {
            $table->string('activity_type')->change();
        });

        Schema::table('activity_periods', function (Blueprint $table) {
            $table->string('from_date')->change();
            $table->string('to_date')->change();
            $table->string('start_time')->change();
            $table->string('end_time')->change();
            $table->string('rule')->change();
        });

        Schema::table('church_roles', function (Blueprint $table) {
            $table->string('text_color')->change();
            $table->string('border_color')->change();
            $table->string('background_color')->change();
        });

        Schema::table('country_codes', function (Blueprint $table) {
            $table->string('code')->change();
        });

        Schema::table('dedications', function (Blueprint $table) {
            $table->string('identify_id')->change();
            $table->string('receipt_number')->change();
            $table->string('dedicate_date')->change();
            $table->string('method')->change();
        });

        Schema::table('intercessions', function (Blueprint $table) {
            $table->string('card_id')->change();
            $table->string('card_type')->change();
            $table->string('country_code')->change();
            $table->string('phone')->change();
            $table->string('apply_date')->change();
            $table->string('status')->change();
            $table->string('ministry')->nullable()->change();
            $table->string('prayer_answered_date')->nullable()->change();
        });

        Schema::table('permission_constraints', function (Blueprint $table) {
            $table->string('page')->change();
            $table->string('action')->change();
        });

        Schema::table('user_church_info', function (Blueprint $table) {
            $table->string('membership_status')->nullable()->change();
            $table->string('participation_status')->nullable()->change();
            $table->string('membership_location')->nullable()->change();
            $table->string('zone')->nullable()->change();
            $table->string('adulthood_christened_at')->nullable()->change();
            $table->string('childhood_christened_at')->nullable()->change();
            $table->string('confirmed_at')->nullable()->change();
            $table->string('skill')->nullable()->change();
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('identify_id')->nullable()->change();
            $table->string('birthday')->change();
            $table->string('country_code')->change();
            $table->string('phone_number')->change();
            $table->string('gender')->nullable()->change();
            $table->string('company_area_code')->nullable()->change();
            $table->string('company_phone_number')->nullable()->change();
            $table->string('home_area_code')->nullable()->change();
            $table->string('home_phone_number')->nullable()->change();
        });

        Schema::table('user_relatives', function (Blueprint $table) {
            $table->string('relationship')->change();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->string('status')->change();
            $table->string('visit_date')->change();
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->string('church_type')->change();
        });
    }
};
