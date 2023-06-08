<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ministry;

class MinistryController extends Controller
{
    public function index()
    {
        $ministries = Ministry::query()
            ->select(['id', 'name'])
            ->where('level', 1)
            ->with('children')
            ->get();

        return response()->json([
            'ministries' => $ministries,
        ]);
    }
}
