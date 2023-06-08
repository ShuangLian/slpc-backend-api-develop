<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CountryCode;

class CountryCodeController extends Controller
{
    public function index()
    {
        $countryCodes = CountryCode::query()
            ->orderBy('sort')
            ->get();

        return response()->json(['codes' => $countryCodes]);
    }
}
