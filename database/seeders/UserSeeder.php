<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user['username'] = 'admin1';
        $user['password'] = Hash::make('123456');
        $user['role'] = 'admin';
        $user->save();

        $userProfile = new UserProfile();
        $userProfile['user_id'] = $user['id'];
        $userProfile['name'] = 'UserNo' . $user['id'];
        $userProfile['identify_id'] = 'A123456789';
        $userProfile['birthday'] = '2000-01-01';
        $userProfile['avatar_url'] = 'smmJmKMNI.jpg';
        $userProfile['country_code'] = '886';
        $userProfile['phone_number'] = '886987654321';
        $userProfile['gender'] = 'male';
        $userProfile['is_married'] = true;
        $userProfile['company_phone_number'] = '0800092000';
        $userProfile['home_phone_number'] = '0654321';
        $userProfile['email'] = 'aaa@bb.cc';
        $userProfile['line_id'] = 'evis';
        $userProfile['job_title'] = '董事長';
        $userProfile['highest_education'] = '美國哈哈哈佛大學';
        $userProfile['city'] = '台南市';
        $userProfile['region'] = '東區';
        $userProfile['address'] = '北門路二段';
        $userProfile['emergency_name'] = '我爸爸';
        $userProfile['emergency_relationship'] = '父';
        $userProfile['emergency_contact'] = '0123456789';
        $userProfile->save();

        $userRelative = new UserRelative();
        $userRelative['user_id'] = $user['id'];
        $userRelative['relationship'] = '我是她媽媽';
        $userRelative['name'] = 'Mother\'s name';
        $userRelative['is_alive'] = true;
        $userRelative['is_christened'] = true;
        $userRelative['christened_church'] = '雙聯教會';
        $userRelative->save();

        $userChurchInfo = new UserChurchInfo();
        $userChurchInfo['user_id'] = $user['id'];
        $userChurchInfo['membership_status'] = '籍不在人在';
        $userChurchInfo['participation_status'] = '旅居海外';
        $userChurchInfo['membership_location'] = '雙聯本堂';
        $userChurchInfo['serving_experience'] = '插花';
        $userChurchInfo['ministry_start_at'] = '09:00';
        $userChurchInfo['ministry_end_at'] = '15:00';
        $userChurchInfo['adulthood_christened_at'] = '2000-10-01';
        $userChurchInfo['adulthood_christened_church'] = '雙聯教會';
        $userChurchInfo['childhood_christened_at'] = '1990-10-01';
        $userChurchInfo['childhood_christened_church'] = '雙聯教會';
        $userChurchInfo['confirmed_at'] = '2010-10-01';
        $userChurchInfo['confirmed_church'] = '雙聯教會';
        $userChurchInfo->save();

        $userTag = new UserTag();
        $userTag['user_id'] = $user['id'];
        $userTag['tag_key'] = '/MINISTRY';
        $userTag['value'] = '插花';
        $userTag->save();
    }
}
