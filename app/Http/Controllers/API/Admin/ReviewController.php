<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userProfile = UserProfile::query()
            ->select(['user_id', 'name', 'country_code', 'phone_number', 'birthday'])
            ->where('user_id', $request['user_id'])
            ->first();

        $reviews = Review::query()
            ->where('status', Review::PENDING);

        if ($request->has('user_id')) {
            $reviews->where('user_id', $request['user_id']);
        }

        return response()->json([
            'profile' => $userProfile,
            'reviews' => $reviews->get(),
        ]);
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
        $review = Review::query()
            ->where('id', $id)
            ->first();

        if ($review == null) {
            abort(404, '找不到審查紀錄');
        }

        if ($review['status'] != Review::PENDING) {
            abort(400, '審查紀錄已被修改');
        }

        $admin = Auth::user();

        $status = $request['status'];

        if ($status == Review::ACCEPTED) {
            $review->accept($admin['id']);
        }

        if ($status == Review::REJECTED) {
            $review->reject($admin['id']);
        }

        return response(null);
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

    public function homepage()
    {
        $reviewGroupByUserIds = Review::query()
            ->selectRaw('MAX(created_at) as last_created_at, user_id')
            ->where('status', Review::PENDING)
            ->groupBy('user_id')
            ->get();

        $userIdByMaxCreatedAt = [];
        foreach ($reviewGroupByUserIds as $reviewGroupByUserId) {
            $userIdByMaxCreatedAt[$reviewGroupByUserId['user_id']] = $reviewGroupByUserId['last_created_at'];
        }

        $reviews = Review::query()
            ->select('user_id')
            ->distinct()
            ->where('status', Review::PENDING)
            ->with('userProfile')
            ->paginate();

        foreach ($reviews as $review) {
            $review->append('pending_review_columns');
            $review['last_created_at'] = $userIdByMaxCreatedAt[$review['user_id']];
        }

        return response()->json($reviews);
    }
}
