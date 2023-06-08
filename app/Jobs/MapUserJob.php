<?php

namespace App\Jobs;

use App\Models\ChurchRole;
use App\Models\LegacyUser;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserTag;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MapUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $nameToUserId;
    private $birthdayToUserId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $userProfiles = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('users.is_legacy', false)
            ->get(['user_profiles.user_id as id', 'user_profiles.name as name', 'user_profiles.birthday as birthday']);

        $this->nameToUserId = $userProfiles->groupBy(fn($item) => $item['name'])
            ->map(function ($item) {
                return $item->map(fn($item) => $item['id']);
            })->toArray();

        $this->birthdayToUserId = $userProfiles->groupBy(fn($item) => $item['birthday'])
            ->map(function ($item) {
                return $item->map(fn($item) => $item['id']);
            })->toArray();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $legacyUsers = User::query()
            ->where('is_legacy', true)
            ->where('is_matched', false)
            ->with(['profile'])
            ->get();

        foreach ($legacyUsers as $legacyUser) {
            $legacyUserId = $legacyUser['id'];
            $name = $legacyUser['profile']['name'];
            $birthday = $legacyUser['profile']['birthday'];

            $idsByMappedName = $this->nameToUserId[$name] ?? [];
            $idsByMappedBirthday = $this->birthdayToUserId[$birthday] ?? [];

            $ids = array_intersect($idsByMappedName, $idsByMappedBirthday);

            // Legacy User Mapped no data, skip
            if (empty($ids)) {
//                Log::info("legacy user => id: $legacyUserId, name: $name, birthday: $birthday \n 比對不到資料");
                continue;
            }

            // Legacy User Mapped Multiple user, skip
            if (count($ids) > 1) {
                $idsStr = json_encode($ids);
                Log::info("legacy user => id: $legacyUserId, name: $name, birthday: $birthday \n 對應上這些 user id: $idsStr");
                continue;
            }

            // Legacy User get only 1 user data, start migrating data
            try {
                $newUserId = $ids[0];

                UserChurchInfo::query()
                    ->where('user_id', $newUserId)
                    ->forceDelete();
                UserTag::query()
                    ->where('user_id', $newUserId)
                    ->forceDelete();
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
            }

            // Map Church Info, UserTag
            UserChurchInfo::mapOrCreateUserChurchInfo($legacyUserId, $newUserId);
            $defaultChurchRole = ChurchRole::query()
                ->where('is_default_role', true)
                ->first();
            UserTag::mapOrCreateUserTag($legacyUserId, $newUserId, $defaultChurchRole['id']);
            Visit::migrateDataToNewUser($legacyUserId, $newUserId);

            // Update Legacy User Matched Status
            User::query()
                ->where('id', $legacyUserId)
                ->update([
                    'is_matched' => true,
                    'matched_at' => Carbon::now(),
                    'matched_user_id' => $newUserId
                ]);
            Log::info("
                Map User Successful!!
                Legacy User => id: $legacyUserId, name: $name, birthday: $birthday
                New User => id: $newUserId, name: $name, birthday: $birthday
            ");
        }
    }
}
