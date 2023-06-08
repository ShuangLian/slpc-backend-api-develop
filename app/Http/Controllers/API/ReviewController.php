<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $reviews = Review::query()
            ->select(['id', 'type', 'reviewed_at', 'status'])
            ->whereIn('status', [Review::ACCEPTED, Review::REJECTED])
            ->where('user_id', $user['id'])
            ->whereNull('read_at')
            ->orderBy('reviewed_at')
            ->get();

        $lastReviewedAt = $reviews->pluck('reviewed_at')->sort()->last();

        Review::query()
            ->whereIn('id', $reviews->pluck('id'))
            ->update(['read_at' => Carbon::now()]);

        return response()->json([
            'accepted_reviews' => $reviews->filter(function ($item) {
                return $item['status'] == Review::ACCEPTED;
            })->values(),
            'rejected_reviews' => $reviews->filter(function ($item) {
                return $item['status'] == Review::REJECTED;
            })->values(),
            'last_reviewed_at' => $lastReviewedAt,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $review = Review::query()
            ->where('id', $id)
            ->first();

        if ($review['user_id'] !== $user['id']) {
            abort(400, '此審查條件不屬於您');
        }

        if ($review['status'] != Review::PENDING) {
            abort(400, '該紀錄已被審查，無法取消');
        }

        $review['status'] = Review::USER_CANCELED;
        $review->save();

        return response(null);
    }
}
