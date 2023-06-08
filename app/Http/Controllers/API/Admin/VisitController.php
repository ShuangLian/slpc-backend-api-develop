<?php

namespace App\Http\Controllers\API\Admin;

use App\Exports\VisitExport;
use App\Http\Controllers\Controller;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserTag;
use App\Models\Visit;
use App\Models\Zone;
use App\Utils\DateTimeUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => Rule::in([Visit::STATUS_PENDING, Visit::STATUS_PROCESSING, Visit::STATUS_DONE]),
        ]);

        $visits = Visit::query()
            ->with(['userProfile', 'zone', 'visitReason']);

        if ($request->has('user_id')) {
            $visits->where('target_user_id', $request['user_id']);
        }

        if ($request->has('status')) {
            $visits->where('status', $request['status']);
        }

        if ($request->has('statuses')) {
            $visits->whereIn('status', $request['statuses']);
        }

        if ($request->has('filter')) {
            if ($request['filter'] == Visit::FILTER_WEEKLY) {
                $visits->where('visit_date', '>=', DateTimeUtil::getWeekStartDate())
                    ->where('visit_date', '<=', DateTimeUtil::getWeekEndDate());
            }
        }

        if ($request->has('names')) {
            $userProfiles = UserProfile::query();
            foreach ($request['names'] as $name) {
                $userProfiles->orWhere('name', 'like', '%' . $name . '%');
            }

            $visits->whereIn('target_user_id', $userProfiles->pluck('user_id'));
        }

        if ($request->has('zones')) {
            $childZoneIds = Zone::query()
                ->whereIn('parent_id', $request['zones'])
                ->pluck('id');

            $userIds = UserChurchInfo::query()
                ->whereIn('zone', [...$childZoneIds, ...$request['zones']])
                ->pluck('user_id');

            $visits->whereIn('target_user_id', $userIds);
        }

        if ($request->has('visit_reason_ids')) {
            $visits->whereIn('visit_reason_id', $request['visit_reason_ids']);
        }

        if ($request->has(['from', 'to'])) {
            $visits->where('visit_date', '>=', $request['from'])
                ->where('visit_date', '<=', $request['to']);
        }

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $visits->orderBy($request['sorted_by'], $direction);
        }

        // return response()->json($visits->paginate());
        return response()->json($visits->get());
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
            'target_user_id' => 'required|integer',
            'visit_reason_id' => 'required|integer',
            'visit_date' => 'required|string',
            'visit_title' => 'max:50',
            'visit_type' => 'max:50',
            'attend_people' => 'max:50',
            'detail_record' => 'max:1000',
            'created_by' => 'max:10',
        ], [
            'target_user_id.integer' => '使用者 id 格式不符合',
            'target_user_id.required' => '使用者 id 為必填',
            'visit_reason_id.integer' => '事由 id 格式不符合',
            'visit_reason_id.required' => '事由 id 為必填',
            'visit_date.required' => '探訪日期為必填',
            'visit_title.max' => '本次探訪標題內容過長',
            'visit_type.max' => '探訪方式內容過長',
            'attend_people.max' => '參加人員內容過長',
            'detail_record.max' => '詳細記錄內容過長',
            'created_by.max' => '填寫人員內容過長',
        ]);

        $columns = [
            'target_user_id',
            'visit_reason_id',
            'status',
            'visit_date',
            'visit_title',
            'visit_type',
            'created_by',
            'attend_people',
            'detail_record',
            'image1_url',
            'image2_url',
            'image3_url',
        ];

        $visit = new Visit();
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $visit[$column] = $request[$column];
            }
        }
        $visit['status'] = Visit::STATUS_DONE;
        $visit['completed_at'] = Carbon::now();
        $visit->save();

        UserTag::renewCountVisitTag($visit['target_user_id']);
        UserTag::renewLastVisitDateTag($visit['target_user_id']);

        return response()->json($visit);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $visit = Visit::query()
            ->where('id', $id)
            ->with(['userProfile', 'zone', 'visitReason'])
            ->firstOrFail();

        return response()->json($visit);
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
        $request->validate([
            'visit_title' => 'max:50',
            'visit_type' => 'max:50',
            'attend_people' => 'max:50',
        ], [
            'visit_title.max' => '本次探訪標題內容過長',
            'visit_type.max' => '探訪方式內容過長',
            'attend_people.max' => '參加人員內容過長',
        ]);

        $columns = [
            'visit_reason_id',
            'status',
            'visit_date',
            'visit_title',
            'visit_type',
            'created_by',
            'attend_people',
            'detail_record',
            'image1_url',
            'image2_url',
            'image3_url',
        ];

        $visit = Visit::query()
            ->where('id', $id)
            ->firstOrFail();

        foreach ($columns as $column) {
            if ($request->has($column)) {
                $visit[$column] = $request[$column];
            }
        }

        if ($request['status'] != Visit::STATUS_DONE) {
            $visit->save();
        }

        if ($request['status'] == Visit::STATUS_DONE) {
            $visit['completed_at'] = Carbon::now();
            $visit->save();

            $nextVisit = Visit::query()
                ->where('target_user_id', $visit['target_user_id'])
                ->whereNot('status', Visit::STATUS_DONE)
                ->first();

            $visit['is_next_visit_empty'] = $nextVisit == null;

            UserTag::renewLastVisitDateTag($visit['target_user_id']);
            UserTag::renewCountVisitTag($visit['target_user_id']);
        }

        return response()->json($visit);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $visit = Visit::query()
            ->where('id', $id)
            ->firstOrFail();

        $visit->forceDelete();

        UserTag::renewLastVisitDateTag($visit['target_user_id']);
        UserTag::renewCountVisitTag($visit['target_user_id']);

        return response(null);
    }

    public function summary(Request $request)
    {
        if ($request->has('user_id')) {
            $zone = UserChurchInfo::query()
                ->select('zone')
                ->where('user_id', $request['user_id'])
                ->first();

            $completedVisitCount = Visit::query()
                ->where('target_user_id', $request['user_id'])
                ->where('status', Visit::STATUS_DONE)
                ->count();

            $processingVisitCount = Visit::query()
                ->where('target_user_id', $request['user_id'])
                ->whereNot('status', Visit::STATUS_DONE)
                ->count();

            return response()->json([
                'zone' => $zone['zone'] ?? null,
                'completed_visit_count' => $completedVisitCount,
                'processing_visit_count' => $processingVisitCount,
            ]);
        }

        $visitWeeklyCount = Visit::query()
            ->where('visit_date', '>=', DateTimeUtil::getWeekStartDate())
            ->where('visit_date', '<=', DateTimeUtil::getWeekEndDate())
            ->count();

        $noVisitLogAbove6MonthsCount = UserTag::query()
            ->join('users', 'users.id', '=', 'user_tags.user_id')
            ->where('user_tags.tag_key', UserTag::TAG_LAST_VISIT_DATE)
            ->where(function ($query) {
                $query->where('user_tags.value', '<', Carbon::now()->subMonths(6)->toDateString())
                    ->orWhereNull('user_tags.value');
            })
            ->where(function ($query) {
                $query->where('users.is_matched', false)
                    ->orWhereNull('users.is_matched');
            })
            ->count();

        return response()->json([
            'visit_weekly_count' => $visitWeeklyCount,
            'no_visit_log_above_6_months_count' => $noVisitLogAbove6MonthsCount,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => 'required',
            'to' => 'required',
        ]);

        $from = $request['from'];
        $to = $request['to'];

        return (new VisitExport($from, $to))->download($from . '-' . $to . '_探訪紀錄.xlsx');
    }
}
