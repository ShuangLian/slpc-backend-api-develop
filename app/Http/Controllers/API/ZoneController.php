<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $zones = Zone::query()
            ->select(['id', 'name'])
            ->where('level', 1)
            ->with('children');

        if ($request->has('filter')) {
            $zones->where(function ($query) use ($request) {
                $query->Where('church_type', $request['filter'])
                    ->orWhere('church_type', Zone::CHURCH_TYPE_COMMON);
            });
        }

        return response()->json([
            'zones' => $zones->get(),
        ]);
    }
}
