<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitReason;

class VisitReasonController extends Controller
{
    public function index()
    {
        $visitReasons = VisitReason::query()
            ->select(['id', 'reason'])
            ->get();

        return response()->json([
            'visit_reasons' => $visitReasons,
        ]);
    }
}
