<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTitle;

class AccountTitleController extends Controller
{
    public function index()
    {
        $accountTitles = AccountTitle::query()
            ->get();

        return response()->json(['account_titles' => $accountTitles]);
    }
}
