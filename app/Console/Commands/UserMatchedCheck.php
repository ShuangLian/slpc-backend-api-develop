<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserMatchedCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:matched-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check User Matched or Not and Update Match status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $newUserProfiles = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('users.is_legacy', false)
            ->get(['user_id', 'name', 'birthday', 'country_code', 'phone_number']);

        $legacyUserProfiles = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('users.is_legacy', true)
            ->where('is_matched', false)
            ->get(['user_id', 'name', 'birthday', 'country_code', 'phone_number']);

        $bar = $this->output->createProgressBar(count($legacyUserProfiles));

        $legacyUserIdToUserId = [];

        $matchedLegacyUsers = $legacyUserProfiles->filter(function ($legacyUserProfile) use ($newUserProfiles, $bar, &$legacyUserIdToUserId) {
            $isMatched = $newUserProfiles->first(function ($item) use ($legacyUserProfile) {
                return $legacyUserProfile['name'] == $item['name']
                    && $legacyUserProfile['birthday'] == $item['birthday'];
            });


            $bar->advance();

            if (!empty($isMatched)) {
                $legacyUserIdToUserId[$legacyUserProfile['user_id']] = $isMatched['user_id'];
                return $legacyUserProfile;
            }
            return false;
        });

        $matchedLegacyUserIds = $matchedLegacyUsers->map(fn($item) => $item['user_id'])->values();
        echo '已配對的總數：' . count($matchedLegacyUserIds);

        foreach ($matchedLegacyUserIds as $legacyUserId) {
            User::query()
                ->where('id', $legacyUserId)
                ->update(['is_matched' => true, 'matched_at' => Carbon::now(), 'matched_user_id' => $legacyUserIdToUserId[$legacyUserId]]);
        }

        $bar->finish();
        return 0;
    }
}
