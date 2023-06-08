<?php

namespace App\Console\Commands;

use App\Models\LegacyUser;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MergeUserTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:merge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge Users Table and Legacy User Table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $legacyUsers = LegacyUser::query()
            ->with(['legacyProfile', 'legacyChurchInfo', 'legacyRelatives', 'churchRoles'])
            ->get();

        $bar = $this->output->createProgressBar(count($legacyUsers));

        $profileColumns = [
            'name',
            'birthday',
            'country_code',
            'phone_number',
            'gender',
            'is_married',
            'company_phone_number',
            'home_phone_number',
            'email',
            'line_id',
            'job_title',
            'highest_education',
            'city',
            'region',
            'address',
            'emergency_name',
            'emergency_relationship',
            'emergency_contact',
        ];
        $infoColumns = [
            'membership_status',
            'participation_status',
            'membership_location',
            'serving_experience',
            'adulthood_christened_at',
            'adulthood_christened_church',
            'childhood_christened_at',
            'childhood_christened_church',
            'confirmed_at',
            'confirmed_church',

        ];

        foreach ($legacyUsers as $legacyUser) {
            $user = new User();
            $user['role'] = User::ROLE_USER;
            $user['is_legacy'] = true;
            $user['is_matched'] = false;
            $user->save();

            try {
                $userProfile = new UserProfile();
                $userProfile['user_id'] = $user['id'];
                foreach ($profileColumns as $profileColumn) {
                    $userProfile[$profileColumn] = $legacyUser['legacyProfile'][$profileColumn];
                }
                $userProfile['identify_id'] = strlen($legacyUser['legacyProfile']['identify_id']) > 10 ? null : $legacyUser['legacyProfile']['identify_id'];
                $userProfile->save();
            } catch (\Exception $exception) {
                echo $legacyUser['legacyProfile'];
                Log::error($exception);
            }

            try {
                $churchInfo = new UserChurchInfo();
                $churchInfo['user_id'] = $user['id'];
                foreach ($infoColumns as $infoColumn) {
                    $churchInfo[$infoColumn] = $legacyUser['legacyChurchInfo'][$infoColumn];
                }
                $churchInfo['zone'] = $legacyUser['legacyChurchInfo']['zone']['id'] ?? null;
                $churchInfo->save();
            } catch (\Exception $exception) {
                echo $legacyUser['legacyChurchInfo'];
                Log::error($exception);
            }

            foreach ($legacyUser['legacyRelatives'] as $legacyRelative) {
                try {
                    $relative = new UserRelative();
                    $relative['user_id'] = $user['id'];
                    $relative['relationship'] = $legacyRelative['relationship'];
                    $relative['name'] = $legacyRelative['name'];
                    $relative['is_alive'] = $legacyRelative['is_alive'];
                    $relative['is_christened'] = $legacyRelative['is_christened'];
                    $relative['christened_church'] = $legacyRelative['christened_church'];
                    $relative->save();
                } catch (\Exception $exception) {
                    echo $legacyRelative;
                    Log::error($exception);
                }
            }

            foreach ($legacyUser['churchRoles'] as $churchRole) {
                try {
                    $tag = new UserTag();
                    $tag['user_id'] = $user['id'];
                    $tag['tag_key'] = UserTag::TAG_CHURCH_ROLE;
                    $tag['value'] = $churchRole['id'];
                    $tag->save();
                } catch (\Exception $exception) {
                    echo $churchRole;
                    Log::error($exception);
                }
            }
            UserTag::renewLastVisitDateTag($user['id']);
            UserTag::renewCountVisitTag($user['id']);

            $bar->advance();
        }

        $bar->finish();
        return 0;
    }
}
