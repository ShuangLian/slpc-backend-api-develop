<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Intercession;
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
        $user = Auth::user();
        $ids = [];

        $intercessions = Intercession::query()
            ->where('user_id', $user['id'])
            ->whereNot('card_type', Intercession::THANKFUL_CARD)
            ->orderByRaw('id IN (SELECT parent_id FROM intercessions WHERE parent_id IS NOT NULL)')
            ->orderByDesc('created_at');

        if ($request->has(['from', 'to'])) {
            $intercessions->where('apply_date', '>=', $request['from'])
                ->where('apply_date', '<=', $request['to']);
        }

        if ($request->has('card_type') && $request['card_type'] !== Intercession::THANKFUL_CARD) {
            $ids = Intercession::query()
                ->where('user_id', $user['id'])
                ->where('card_type', $request['card_type'])
                ->pluck('id');
        }

        if ($request->has('card_type') && $request['card_type'] === Intercession::THANKFUL_CARD) {
            $ids = Intercession::query()
                ->where('user_id', $user['id'])
                ->where('card_type', Intercession::THANKFUL_CARD)
                ->pluck('parent_id');
        }

        if ($request->has('status')) {
            $intercessions->where('status', $request['status']);
        }

        if (!empty($ids)) {
            $intercessions = $intercessions->whereIn('id', $ids);
        }

        $intercessions->with('thankful');

        return response()->json($intercessions->paginate());
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
            'card_type' => ['required', Rule::in(Intercession::GENERAL_CARD, Intercession::EMERGENCY_CARD, Intercession::PRIVATE_CARD, Intercession::MINISTRY_CARD, Intercession::THANKFUL_CARD)],
            'content' => 'max:100'
        ],
        [
            'content.max' => '代禱事項內容過長'
        ]);

        $user = Auth::user();

        try {
            $intercession = Intercession::createIntercessionByRequest($request, $user['id']);

            return response()->json($intercession);
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
        $user = Auth::user();

        $intercession = Intercession::query()
            ->where('user_id', $user['id'])
            ->where('id', $id)
            ->with('thankful')
            ->firstOrFail();

        return response()->json($intercession);
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
        $user = Auth::user();

        $intercession = Intercession::query()
            ->where('id', $id)
            ->where('user_id', $user['id'])
            ->firstOrFail();

        if ($intercession['status'] == Intercession::STATUS_DONE) {
            abort(400, '已完成的代禱卡不能刪除');
        }

        $intercession->delete();

        return response(null);
    }
}
