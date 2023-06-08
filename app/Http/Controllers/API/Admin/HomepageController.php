<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCheckin;
use App\Models\ChurchRole;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserTag;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'birthday_from' => 'date',
            'birthday_to' => 'date',
            'activity_from' => 'date',
            'activity_to' => 'date',
        ]);

        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d H:i:s');
        $weekEndDate = $now->endOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d H:i:s');

        // Sunday weekly summary
        $sundayAttendedCountWeeklySummary = ActivityCheckin::query()
            ->where('activity_type', Activity::ACTIVITY_TYPE_SUNDAY)
            ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
            ->count();

        $userCount = User::query()
            ->where('role', User::ROLE_USER)
            ->onlyNewUsers()
            ->count();

        $sundayAbsentCountWeeklySummary = $userCount - $sundayAttendedCountWeeklySummary;

        // User weekly summary
        $newUserCountWeeklySummary = User::query()
            ->where('role', User::ROLE_USER)
            ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
            ->onlyNewUsers()
            ->count();

        $customerRole = ChurchRole::query()
            ->where('name', User::GUEST_NAME)
            ->firstOrFail();

        $customerUserIds = UserTag::query()
            ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
            ->where('value', $customerRole['id'])
            ->pluck('user_id');

        $newCustomerCountWeeklySummary = User::query()
            ->where('role', User::ROLE_USER)
            ->whereBetween('created_at', [$weekStartDate, $weekEndDate])
            ->whereIn('id', $customerUserIds)
            ->onlyNewUsers()
            ->count();

        return response()->json([
            'sunday_attended_count' => $sundayAttendedCountWeeklySummary,
            'sunday_absent_count' => $sundayAbsentCountWeeklySummary,
            'new_user_count' => $newUserCountWeeklySummary,
            'new_customer_count' => $newCustomerCountWeeklySummary,
        ]);
    }

    public function birthday(Request $request)
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');
        $weekEndDate = $now->endOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d');

        //Filter User by birthday
        if ($request->has(['from', 'to'])) {
            $from = Carbon::parse($request['from'])->format('m-d');
            $to = Carbon::parse($request['to'])->format('m-d');
        } else {
            $from = Carbon::parse($weekStartDate)->format('m-d');
            $to = Carbon::parse($weekEndDate)->format('m-d');
        }

        $ids = UserProfile::query()
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') >= '$from'")
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') <= '$to'");

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $ids->orderByRaw("DATE_FORMAT(birthday, '%m-%d') $direction");
        }

        $ids = $ids->pluck('user_id');

        $implodeIds = implode(',', $ids->toArray());
        $perPage = 10;
        $users = User::query()
            ->onlyNewUsers()
            ->whereIn('id', $ids)
            ->with(['profile:id,user_id,name,birthday', 'churchInfo:id,user_id,zone'])
            ->orderByRaw("FIELD(id, $implodeIds)")
            ->get();
            // ->paginate($perPage);

        return response()->json($users);
    }

    public function activities(Request $request)
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');
        $weekEndDate = $now->endOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d');

        //Filter Activity by date
        $activities = Activity::query()
            ->where('activity_type', Activity::ACTIVITY_TYPE_EQUIPMENT)
            ->with('checkins');

        if ($request->has(['from', 'to'])) {
            $activities->whereBetween('date', [$request['from'], $request['to']]);
        } else {
            $activities->whereBetween('date', [$weekStartDate, $weekEndDate]);
        }

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $activities->orderBy('date', $direction);
        }

        $perPage = 10;

        // return response()->json($activities->paginate($perPage));
        return response()->json($activities->get());
    }

    public function activityCheckinsCount(Request $request)
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');
        $weekEndDate = $now->endOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d');

        //Filter Activity by date
        $activities = Activity::query()
            ->where('activity_type', Activity::ACTIVITY_TYPE_EQUIPMENT)
            ->with('checkins');

        if ($request->has(['from', 'to'])) {
            $activities->whereBetween('date', [$request['from'], $request['to']]);
        } else {
            $activities->whereBetween('date', [$weekStartDate, $weekEndDate]);
        }

        $activities = $activities->get();

        $checkinsSum = 0;

        foreach ($activities as $activity) {
            $checkinsSum += count($activity['checkins']);
        }

        return response()->json(['checkins_sum' => $checkinsSum]);
    }
}
