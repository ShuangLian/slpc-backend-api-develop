<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountTitle;
use App\Models\Dedication;
use App\Models\UserProfile;
use App\Models\UserStatisticalTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DedicationController extends Controller
{
    /**
     * if request have filter parameter, will ignore other parameter.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $userProfile = UserProfile::query()
            ->where('user_id', $user['id'])
            ->first();

        if (empty($userProfile) || empty($userProfile['identify_id'])) {
            abort(400, '請先填寫身分證字號');
        }

        $perPageCount = 20;

        $dedications = Dedication::query()
            ->where('identify_id', $userProfile['identify_id'])
            ->orderByDesc('dedicate_date')
            ->with('accountTitle');

        if ($request->has('filter')) {
            $monthlyAccountTitleId = AccountTitle::query()
                ->where('account_title_serial_number', AccountTitle::MONTHLY_SERIAL_NUMBER)
                ->pluck('id')
                ->firstOrFail();

            if ($request['filter'] == Dedication::TYPE_MONTHLY) {
                $dedications->where('account_title_id', $monthlyAccountTitleId);
            }

            if ($request['filter'] == Dedication::TYPE_OTHERS) {
                $dedications->where('account_title_id', '!=', $monthlyAccountTitleId);
            }

            return response()->json($dedications->paginate($perPageCount));
        }

        if ($request->has(['from', 'to'])) {
            $dedications->whereYear('dedicate_date', '>=', Carbon::parse($request['from'])->year)
                ->whereYear('dedicate_date', '<=', Carbon::parse($request['to'])->year)
                ->whereMonth('dedicate_date', '>=', Carbon::parse($request['from'])->month)
                ->whereMonth('dedicate_date', '<=', Carbon::parse($request['to'])->month);
        }

        if ($request->has('account_title_ids')) {
            $dedications->whereIn('account_title_id', $request['account_title_ids']);
        }

        // return response()->json($dedications->paginate($perPageCount));
        return response()->json($dedications->get());
    }

    //todo 要分開已經歸戶的帳戶

    /**
     * if request have filter parameter, will ignore other parameter.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $user = Auth::user();

        $userProfile = UserProfile::query()
            ->where('user_id', $user['id'])
            ->first();

        // 奉獻管理 homepage
        if (!$request->hasAny(['from', 'to', 'account_title_ids', 'filter'])) {
            $count = UserStatisticalTag::query()
                ->where('account_code', $userProfile['identify_id'])
                ->where('tag_key', 'like', UserStatisticalTag::TAG_COUNT_DEDICATION . '%')
                ->sum('amount');

            $amount = UserStatisticalTag::query()
                ->where('account_code', $userProfile['identify_id'])
                ->where('tag_key', 'like', UserStatisticalTag::TAG_AMOUNT_DEDICATION . '%')
                ->sum('amount');

            return response()->json([
                'count' => $count,
                'amount' => number_format($amount),
            ]);
        }

        // 奉獻管理 月獻 or 其他 統計
        if ($request->has('filter')) {
            $monthlyAccountTitleId = AccountTitle::query()
                ->where('account_title_serial_number', AccountTitle::MONTHLY_SERIAL_NUMBER)
                ->pluck('id')
                ->firstOrFail();

            $count = UserStatisticalTag::query()
                ->where('account_code', $userProfile['identify_id']);

            $amount = UserStatisticalTag::query()
                ->where('account_code', $userProfile['identify_id']);

            if ($request['filter'] == Dedication::TYPE_MONTHLY) {
                $count->where('tag_key', UserStatisticalTag::TAG_COUNT_DEDICATION . '/' . $monthlyAccountTitleId);
                $amount->where('tag_key', UserStatisticalTag::TAG_AMOUNT_DEDICATION . '/' . $monthlyAccountTitleId);
            }

            if ($request['filter'] == Dedication::TYPE_OTHERS) {
                $count->where('tag_key', 'like', UserStatisticalTag::TAG_COUNT_DEDICATION . '%')
                    ->where('tag_key', '!=', UserStatisticalTag::TAG_COUNT_DEDICATION . '/' . $monthlyAccountTitleId);
                $amount->where('tag_key', 'like', UserStatisticalTag::TAG_AMOUNT_DEDICATION . '%')
                    ->where('tag_key', '!=', UserStatisticalTag::TAG_AMOUNT_DEDICATION . '/' . $monthlyAccountTitleId);
            }

            return response()->json([
                'count' => number_format($count->sum('amount')),
                'amount' => number_format($amount->sum('amount')),
            ]);
        }

        // 奉獻管理 filter by date or account_title_ids
        $dedications = Dedication::query()
            ->where('identify_id', $userProfile['identify_id']);

        if ($request->has(['from', 'to'])) {
            $dedications->whereYear('dedicate_date', '>=', Carbon::parse($request['from'])->year)
                ->whereYear('dedicate_date', '<=', Carbon::parse($request['to'])->year)
                ->whereMonth('dedicate_date', '>=', Carbon::parse($request['from'])->month)
                ->whereMonth('dedicate_date', '<=', Carbon::parse($request['to'])->month);
        }

        if ($request->has('account_title_ids')) {
            $dedications->whereIn('account_title_id', $request['account_title_ids']);
        }

        $count = $dedications->count();
        $amount = $dedications->sum('amount');

        return response()->json([
            'count' => $count,
            'amount' => number_format($amount),
        ]);
    }
}
