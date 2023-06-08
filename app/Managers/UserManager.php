<?php

namespace App\Managers;

use App\Models\ActivityCheckin;
use App\Models\Dedication;
use App\Models\Intercession;
use App\Models\Review;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserStatisticalTag;
use App\Models\UserTag;
use App\Models\Visit;
use App\Utils\PhoneFormatUtil;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserManager
{
    public static function deleteUser($userId, bool $forceDelete)
    {
        $user = User::withTrashed()
            ->where('id', $userId);

        $userProfile = UserProfile::withTrashed()
            ->where('user_id', $userId)
            ->first();

        $userRelatives = UserRelative::withTrashed()
            ->where('user_id', $userId);

        $userChurchInfo = UserChurchInfo::withTrashed()
            ->where('user_id', $userId);

        $activityCheckins = ActivityCheckin::withTrashed()
            ->where('user_id', $userId);

        $intercessions = Intercession::withTrashed()
            ->where('user_id', $userId);

        $visit = Visit::withTrashed()
            ->where('target_user_id', $userId);

        $userTags = UserTag::query()
            ->where('user_id', $userId);

        $reviews = Review::query()
            ->where('user_id', $userId);

        $identifyId = $userProfile['identify_id'] ?? 'empty identify id';
        if (!empty($identifyId)) {
            $dedications = Dedication::withTrashed()
                ->where('identify_id', $identifyId);

            $userStatisticalTag = UserStatisticalTag::query()
                ->where('account_code', $identifyId);
        }

        if ($forceDelete) {
            $user->forceDelete();
            $userRelatives->forceDelete();
            $userChurchInfo->forceDelete();
            $activityCheckins->forceDelete();
            $intercessions->forceDelete();
            $visit->forceDelete();
            $userTags->forceDelete();
            $reviews->forceDelete();
            $userProfile?->forceDelete();

            if (!empty($identifyId)) {
                $dedications->forceDelete();
                $userStatisticalTag->forceDelete();
            }
        } else {
            $user->delete();
            $userProfile->delete();
            $userRelatives->delete();
            $userChurchInfo->delete();
            $activityCheckins->delete();
            $intercessions->delete();
            $visit->delete();

            $reviews->where('status', Review::PENDING)
                ->update([
                    'status' => Review::USER_STOPPED,
                ]);

            if (!empty($identifyId)) {
                $dedications->delete();
            }
        }
    }

    public static function restoreUser($userId)
    {
        User::onlyTrashed()
            ->where('id', $userId)
            ->restore();

        UserProfile::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        UserRelative::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        UserChurchInfo::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        ActivityCheckin::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        Intercession::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        $userProfile = UserProfile::query()
            ->where('user_id', $userId)
            ->firstOrFail();

        Dedication::onlyTrashed()
            ->where('identify_id', $userProfile['identify_id'])
            ->restore();

        Visit::onlyTrashed()
            ->where('target_user_id', $userId)
            ->restore();
    }

    public static function getAfterValueIfColumnIsReviewing($value, $userId, $type): ?string
    {
        $review = Review::query()
            ->where('user_id', $userId)
            ->where('status', Review::PENDING)
            ->where('type', $type)
            ->first();

        if ($review === null) {
            return $value;
        }

        return $review['after_value'];
    }

    public static function mapUserFromLegacyTable(Request $request, $lineUser, $defaultRole): bool|User
    {
        // try to map user profile
        $legacyUserProfile = UserProfile::query()
            ->selectRaw('user_profiles.*')
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('users.is_legacy', true)
            ->where('users.is_matched', false)
            ->where('user_profiles.name', $request['name'])
            ->where('user_profiles.birthday', $request['birthday'])
            ->first();

        if ($legacyUserProfile === null) {
            return false;
        }

        $user = new User();
        $user['line_uid'] = $lineUser['id'];
        $user['role'] = User::ROLE_USER;
        $user->save();

        $legacyUserId = $legacyUserProfile['user_id'];

        $newUserProfile = new UserProfile();
        $newUserProfile['user_id'] = $user['id'];
        $newUserProfile['name'] = $legacyUserProfile['name'];
        $newUserProfile['birthday'] = $legacyUserProfile['birthday'];
        $newUserProfile['country_code'] = $request['country_code'];
        $newUserProfile['phone_number'] = PhoneFormatUtil::getPhoneNumber($request['phone_number']);
        $newUserProfile->save();

        UserTag::mapOrCreateUserTag($legacyUserId, $user['id'], $defaultRole['id']);
        UserChurchInfo::mapOrCreateUserChurchInfo($legacyUserId, $user['id']);

        try {
            Visit::migrateDataToNewUser($legacyUserProfile['user_id'], $user['id']);
            User::query()
                ->where('id', $legacyUserProfile['user_id'])
                ->update([
                    'is_matched' => true,
                    'matched_at' => Carbon::now(),
                    'matched_user_id' => $user['id']
                ]);
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
            abort(400, '使用者比對錯誤');
        }

        return $user;
    }

    public static function addLineUIDIfUserExistInUserTable(Request $request, $lineUser): \Illuminate\Database\Eloquent\Model|bool|\Illuminate\Database\Eloquent\Builder|null
    {
        $userProfile = UserProfile::query()
            ->join('users', 'users.id', '=', 'user_profiles.user_id')
            ->where('users.is_legacy', false)
            ->whereNull('users.line_uid')
            ->where('name', $request['name'])
            ->where('birthday', $request['birthday'])
            ->first();

        if ($userProfile === null) {
            return false;
        }

        $user = User::query()
            ->where('id', $userProfile['user_id'])
            ->first();

        if ($user === null) {
            return false;
        }

        if (empty($user['line_uid'])) {
            $user['line_uid'] = $lineUser['id'];
            $user->save();
        }

        return $user;
    }
}
