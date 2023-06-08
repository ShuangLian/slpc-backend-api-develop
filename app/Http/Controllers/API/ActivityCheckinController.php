<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityCheckinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'month' => 'integer|between:1,12',
        ]);

        $user = Auth::user();

        $activityCheckins = ActivityCheckin::query()
            ->where('user_id', $user['id'])
            ->with('activity');

        if ($request->has('year')) {
            $activityCheckins->whereYear('created_at', $request['year']);
        }

        if ($request->has('month')) {
            $activityCheckins->whereMonth('created_at', $request['month']);
        }

        $activityCheckins->orderByDesc('id');

        return response()->json([
            'activity_checkins' => $activityCheckins->get(),
        ]);
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
            'activity_id' => 'required|numeric',
        ]);

        $activity = Activity::query()
            ->where('id', $request['activity_id'])
            ->first();

        if ($activity == null) {
            abort(404, '找不到該活動');
        }

        if ($activity['date'] == null) {
            abort(400, '只有系列活動的子活動可以簽到！');
        }

        $user = Auth::user();

        $activityCheckin = ActivityCheckin::query()
            ->where('activity_id', $request['activity_id'])
            ->where('user_id', $user['id'])
            ->with('activity')
            ->first();

        if ($activityCheckin != null) {
            return response()->json($activityCheckin);
        }

        $activityCheckin = new ActivityCheckin();
        $activityCheckin['activity_id'] = $request['activity_id'];
        $activityCheckin['user_id'] = $user['id'];
        $activityCheckin['activity_type'] = $activity['activity_type'];
        $activityCheckin->save();

        $activityCheckin->load('activity');

        return response()->json($activityCheckin);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
