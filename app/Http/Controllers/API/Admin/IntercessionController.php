<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intercession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IntercessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $orderByRaw =
            'CASE card_type
	            WHEN "THANKFUL" then 1
	            WHEN "PRIVATE" then 2
	            WHEN "EMERGENCY" then 3
	            WHEN "GENERAL" then 4
	            WHEN "MINISTRY" then 5
            END';

        $intercessions = Intercession::query()
            ->orderByRaw($orderByRaw);

        if ($request->has('status')) {
            if ($request['status'] == Intercession::STATUS_DELETED) {
                $intercessions = Intercession::onlyTrashed()
                    ->whereNot('status', Intercession::STATUS_DONE)
                    ->paginate();

                return response()->json($intercessions);
            }

            $intercessions->where('status', $request['status']);
        }

        if ($request->has('user_id')) {
            $intercessions->where('user_id', $request['user_id']);
        }

        if ($request->has('statuses')) {
            $intercessions->whereIn('status', $request['statuses']);
        }

        if ($request->has('card_type')) {
            $intercessions->whereIn('card_type', $request['card_type']);
        }

        if ($request->has('apply_names')) {
            $intercessions->where(function ($query) use ($request) {
                foreach ($request['apply_names'] as $applyName) {
                    $query->orWhere('apply_name', 'like', '%' . $applyName . '%');
                }
            });
        }

        if ($request->has(['from', 'to'])) {
            $intercessions->whereDate('apply_date', '>=', $request['from'])
                ->whereDate('apply_date', '<=', $request['to']);
        }

        if ($request->has('is_printed')) {
            $intercessions->where('is_printed', $request['is_printed']);
        }

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $intercessions->orderBy($request['sorted_by'], $direction);
        } else {
            $intercessions->orderBy('apply_date');
        }

        // return response()->json($intercessions->paginate());
        return response()->json($intercessions->get());
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
            'card_type' => [Rule::in(Intercession::GENERAL_CARD, Intercession::EMERGENCY_CARD, Intercession::PRIVATE_CARD, Intercession::MINISTRY_CARD)],
            'content' => 'max:100'
        ], 
        [
            'content.max' => '代禱事項內容過長'
        ]);

        $admin = Auth::user();

        try {
            $intercession = Intercession::createIntercessionByAdmin($request, $admin['id']);

            return response()->json($intercession);
        } catch (\PDOException $exception) {
            Log::error($exception->getMessage());
            abort(400, '請稍後再試');
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
        $intercession = Intercession::withTrashed()
            ->where('id', $id)
            ->with('parent')
            ->firstOrFail();

        return response()->json($intercession);
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
            'status' => Rule::in(Intercession::STATUS_PENDING, Intercession::STATUS_PROCESSING, Intercession::STATUS_DONE),
            'content' => 'max:100'
        ], 
        [
            'content.max' => '代禱事項內容過長'
        ]);

        $intercession = Intercession::query()
            ->where('id', $id)
            ->firstOrFail();

        if ($intercession['card_type'] == Intercession::THANKFUL_CARD && $request['status'] == Intercession::STATUS_PROCESSING) {
            abort(400, '感謝卡不需要代禱');
        }

        $columns = ['apply_date', 'target_name', 'is_target_user', 'is_target_christened', 'target_age', 'relative', 'content', 'ministry', 'is_public', 'status'];
        foreach ($columns as $column) {
            if ($request->has($column)) {
                $intercession[$column] = $request[$column];
            }
        }

        $intercession->save();

        return response()->json($intercession);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $intercession = Intercession::withTrashed()
            ->where('id', $id)
            ->firstOrFail();

        if ($request['force'] ?? false) {
            $intercession->forceDelete();

            return response(null);
        }

        $createdUser = User::query()
            ->where('id', $intercession['created_by_user_id'])
            ->first();

        if (!empty($createdUser) && $createdUser['role'] == User::ROLE_USER) {
            abort(400, '僅後台新增的代禱卡可以刪除！');
        }

        $intercession->delete();

        return response(null);
    }

    public function updatePrintedStatus(Request $request)
    {
        $request->validate([
            'intercession_ids' => 'required|array',
        ]);

        Intercession::query()
            ->whereIn('id', $request['intercession_ids'])
            ->update(['is_printed' => true]);

        return response(null);
    }
}
