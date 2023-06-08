<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCheckin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'activity_type' => Rule::in(Activity::ACTIVITY_TYPE_EVENT, Activity::ACTIVITY_TYPE_EQUIPMENT),
        ]);

        $activities = Activity::query();

        if ($request->has('activity_type')) {
            $activities->where('activity_type', $request['activity_type']);
        }

        if ($request->has('activity_categories')) {
            $activities->where(function ($query) use ($request) {
                foreach ($request['activity_categories'] as $activityCategory) {
                    $query->orWhere('type', 'like', '%' . $activityCategory . '%');
                }
            });
        }

        if ($request->has('titles')) {
            $activities->where(function ($query) use ($request) {
                foreach ($request['titles'] as $title) {
                    $query->orWhere('title', 'like', '%' . $title . '%');
                }
            });
        }

        $activities->whereNull('parent_id')
            ->whereNotNull('date');

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $activities->orderBy('date', $direction);
        } else {
            $activities->orderByDesc('date');
        }

        // error_log($activities->get());
        return response()->json($activities->get());
        // return response()->json($activities->paginate());
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
            'activity_type' => ['required', Rule::in([Activity::ACTIVITY_TYPE_EVENT, Activity::ACTIVITY_TYPE_EQUIPMENT])],
            'date' => 'required',
            'time' => 'required',
            'type' => 'required',
            'title' => 'required',
            'presenter' => 'required',
            'description' => 'required',
        ], [
            'date.required' => '聚會日期為必填',
            'time.required' => '聚會時間為必填',
            'type.required' => '聚會類型為必填',
            'title.required' => '聚會名稱為必填',
            'presenter.required' => '主領人/主講人為必填',
            'description.required' => '聚會內容為必填',
        ]);
        $columns = [
            'activity_type', 'date', 'time', 'type', 'title', 'presenter', 'description', 'registered_url',
        ];

        $activity = new Activity();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $activity[$column] = $request[$column];
            }
        }
        $activity->save();

        return response()->json($activity);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $activity = Activity::query()
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($activity);
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
        $activity = Activity::query()
            ->where('id', $id)
            ->whereNull('parent_id')
            ->whereNotNull('date')
            ->first();

        if ($activity == null) {
            abort(400, '找不到該活動');
        }

        $columns = [
            'presenter',
            'description',
            'registered_url',
        ];

        foreach ($columns as $column) {
            if ($request->has($column)) {
                $activity[$column] = $request[$column];
            }
        }

        if ($request->has('date')) {
            $activityDate = Carbon::parse($activity['date']);
            if ($activityDate->subDays(2)->isPast() && $activity['date'] !== $request['date']) {
                abort(400, '已超過可以修改日期的期限');
            } else {
                $activity['date'] = $request['date'];
            }
        }

        $activity->save();

        return response()->json($activity);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Activity::destroy($id);

        ActivityCheckin::query()
            ->where('activity_id', $id)
            ->delete();

        return response(null);
    }
}
