<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCheckin;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class ActivityCheckinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $activityCheckins = ActivityCheckin::query()
            ->with(['userProfile', 'activity'])
            ->orderByDesc('id');

        if ($request->has('activity_type')) {
            if ($request['activity_type'] != Activity::ACTIVITY_TYPE_EQUIPMENT) {
                $activityCheckins->whereIn('activity_type', [Activity::ACTIVITY_TYPE_EVENT, Activity::ACTIVITY_TYPE_SUNDAY]);
            } else {
                $activityCheckins->where('activity_type', $request['activity_type']);
            }
        }

        if ($request->has('user_id')) {
            $activityCheckins->where('user_id', $request['user_id']);
        }

        if ($request->has('titles')) {
            $activityIds = Activity::query()
                ->where(function ($query) use ($request) {
                    foreach ($request['titles'] as $title) {
                        $query->orWhere('title', 'like', '%' . $title . '%');
                    }
                });

            $activityCheckins->whereIn('activity_id', $activityIds->pluck('id'));
        }

        if ($request->has('activity_categories')) {
            $activityIds = Activity::query()
                ->where(function ($query) use ($request) {
                    foreach ($request['activity_categories'] as $activityCategory) {
                        $query->orWhere('type', 'like', '%' . $activityCategory . '%');
                    }
                });

            $activityCheckins->whereIn('activity_id', $activityIds->pluck('id'));
        }

        if ($request->has('activity_id')) {
            $activityCheckins->where('activity_id', $request['activity_id']);
        }

        if ($request->has('user_data')) {
            $userIds = UserProfile::query()
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request['user_data'] . '%')
                        ->orWhere('email', 'like', '%' . $request['user_data'] . '%');
                })
                ->pluck('user_id');

            $activityCheckins->whereIn('user_id', $userIds);
        }

        if ($request->has(['start_at', 'end_at'])) {
            $activityCheckins->whereBetween('created_at', [$request['start_at'], $request['end_at']]);
        }

        return response()->json($activityCheckins->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
