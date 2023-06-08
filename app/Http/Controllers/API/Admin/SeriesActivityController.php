<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Managers\ActivityManager;
use App\Models\Activity;
use App\Models\ActivityPeriod;
use App\Models\ActivityPeriodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SeriesActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'activity_type' => Rule::in(Activity::ACTIVITY_TYPE_EVENT, Activity::ACTIVITY_TYPE_EQUIPMENT, Activity::ACTIVITY_TYPE_SUNDAY),
        ]);

        $seriesActivities = Activity::query();

        if ($request->has('activity_type')) {
            if ($request['activity_type'] !== Activity::ACTIVITY_TYPE_EQUIPMENT) {
                $seriesActivities->whereIn('activity_type', [Activity::ACTIVITY_TYPE_SUNDAY, Activity::ACTIVITY_TYPE_EVENT]);
            } else {
                $seriesActivities->where('activity_type', $request['activity_type']);
            }
        }

        if ($request->has('activity_categories')) {
            $seriesActivities->where(function ($query) use ($request) {
                foreach ($request['activity_categories'] as $activityCategory) {
                    $query->orWhere('type', 'like', '%' . $activityCategory . '%');
                }
            });
        }

        if ($request->has('title')) {
            $seriesActivities->where('title', 'like', '%' . $request['title'] . '%');
        }

        $seriesActivities->whereNull('date')
            ->orderByDesc('id')
            ->with('activityPeriods');

        // return response()->json($seriesActivities->paginate());
        return response()->json($seriesActivities->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity_type' => ['required', Rule::in([Activity::ACTIVITY_TYPE_EVENT, Activity::ACTIVITY_TYPE_EQUIPMENT, Activity::ACTIVITY_TYPE_SUNDAY])],
            'period' => ['required', Rule::in(ActivityPeriodType::DAILY, ActivityPeriodType::WEEKLY, ActivityPeriodType::MONTHLY)],
            'from_date' => 'required',
            'to_date' => 'required',
            'period_info.*.start_time' => 'required',
            'period_info.*.end_time' => 'required',
            'period_info' => 'required|array',
            'type' => 'required',
            'title' => 'required',
            'presenter' => 'required',
            'description' => 'required',
        ], [
            'from_date.required' => '聚會日期為必填',
            'to_date.required' => '聚會日期為必填',
            'period_info.*.start_time.required' => '聚會開始時間為必填',
            'period_info.*.end_time.required' => '聚會結束時間為必填',
            'type.required' => '聚會類型為必填',
            'title.required' => '聚會名稱為必填',
            'presenter.required' => '主領人/主講人為必填',
            'description.required' => '聚會活動為必填',
        ]);

        try {
            $activity = new Activity();
            $activityColumns = ['activity_type', 'type', 'title', 'presenter', 'description', 'registered_url'];

            foreach ($activityColumns as $activityColumn) {
                if ($request->has($activityColumn)) {
                    $activity[$activityColumn] = $request[$activityColumn];
                }
            }
            $activity->save();

            if ($request['period'] == ActivityPeriodType::DAILY) {
                $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString($request['period'], null, null);

                $activityPeriod = ActivityPeriod::createActivityPeriodFromRequest($activity['id'], $request, $activityPeriodRuleString, $request['period_info'][0]);
                ActivityManager::createActivitiesByActivityPeriod($activityPeriod);

                return response()->json($activity);
            }

            foreach ($request['period_info'] as $periodInfo) {
                $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString($request['period'], $periodInfo['day_of_week'], $periodInfo['week_of_month']);

                $activityPeriod = ActivityPeriod::createActivityPeriodFromRequest($activity['id'], $request, $activityPeriodRuleString, $periodInfo);
                ActivityManager::createActivitiesByActivityPeriod($activityPeriod);
            }

            return response()->json($activity);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            abort(400, $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $seriesActivity = Activity::query()
            ->where('id', $id)
            ->with('activityPeriods')
            ->first();

        $activities = Activity::query()
            ->where('parent_id', $id)
            ->orderByDesc('id')
            ->paginate();

        return response()->json([
            'series_activity' => $seriesActivity,
            'activities' => $activities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $seriesActivity = Activity::query()
            ->where('id', $id)
            ->whereNull('parent_id')
            ->whereNull('date')
            ->first();

        if ($seriesActivity == null) {
            abort(400, '找不到該活動');
        }

        $activityPeriod = ActivityPeriod::query()
            ->where('activity_id', $seriesActivity['id'])
            ->first();

        if ($activityPeriod == null) {
            abort(400, '找不到該活動');
        }

        $columns = [
            'presenter',
            'description',
            'registered_url',
        ];

        foreach ($columns as $column) {
            if ($request->has($column)) {
                $seriesActivity[$column] = $request[$column];
                $activityPeriod[$column] = $request[$column];
            }
        }

        if ($request->has(['from_date', 'to_date'])) {
            $activityPeriod['from_date'] = $request['from_date'];
            $activityPeriod['to_date'] = $request['to_date'];
        }

        if ($request->has(['start_time', 'end_time'])) {
            $activityPeriod['start_time'] = $request['start_time'];
            $activityPeriod['end_time'] = $request['end_time'];
        }

        $seriesActivity->save();
        $activityPeriod->save();

        return response()->json($seriesActivity);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Activity::query()
            ->where('id', $id)
            ->orWhere('parent_id', $id)
            ->delete();

        ActivityPeriod::query()
            ->where('activity_id', $id)
            ->delete();

        return response(null);
    }
}
