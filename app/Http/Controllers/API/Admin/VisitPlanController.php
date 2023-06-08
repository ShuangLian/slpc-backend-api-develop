<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitReason;
use Illuminate\Http\Request;

class VisitPlanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|integer',
            'contents.*.visit_date' => 'string|date',
        ], [
            'target_user_id.required' => '使用者 id 為必填',
            'target_user_id.integer' => '使用者 id 格式不符合',
            'contents.*.visit_date.date' => '探訪時間格式不符合',
        ]);
        $user = User::query()
            ->where('id', $request['target_user_id'])
            ->first();

        if (empty($user)) {
            abort(400, '探訪對象不存在');
        }

        $requestReasonIds = [];
        foreach ($request['contents'] as $content) {
            $requestReasonIds[] = $content['visit_reason_id'];
        }

        $requestValidReasonCount = VisitReason::query()
            ->whereIn('id', $requestReasonIds)
            ->count();

        if ($requestValidReasonCount != count(array_unique($requestReasonIds))) {
            abort(400, '探訪事由不存在');
        }

        foreach ($request['contents'] as $content) {
            $visit = new Visit();
            $visit['target_user_id'] = $request['target_user_id'];
            $visit['visit_reason_id'] = $content['visit_reason_id'];
            $visit['visit_date'] = $content['visit_date'];
            $visit['status'] = Visit::STATUS_PENDING;
            $visit->save();
        }

        return response(null);
    }
}
