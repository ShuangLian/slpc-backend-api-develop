<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCheckin;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStatisticalTag;
use Illuminate\Support\Facades\Auth;

class HomepageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userProfile = UserProfile::query()
            ->where('user_id', $user['id'])
            ->first();

        $attendSundayCount = ActivityCheckin::query()
            ->where('user_id', $user['id'])
            ->where('activity_type', Activity::ACTIVITY_TYPE_SUNDAY)
            ->count();

        $attendActivityCount = ActivityCheckin::query()
            ->where('user_id', $user['id'])
            ->count();

        $identifyId = $userProfile['identify_id'] ?? '';
        $dedicationCount = UserStatisticalTag::query()
            ->where('account_code', $identifyId)
            ->where('tag_key', 'like', UserStatisticalTag::TAG_COUNT_DEDICATION . '%')
            ->sum('amount');

        $dedicationCount = empty($identifyId) ? 0 : $dedicationCount;

        $user = User::query()
            ->where('id', $user['id'])
            ->with(['profile', 'churchInfo', 'churchRoles'])
            ->first();

        return response()->json([
            'attend_sunday_count' => $attendSundayCount,
            'attend_activity_count' => $attendActivityCount,
            'dedication_count' => $dedicationCount,
            'user' => $user,
        ]);
    }
}
