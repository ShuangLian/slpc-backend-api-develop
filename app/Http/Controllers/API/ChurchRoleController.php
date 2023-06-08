<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChurchRole;
use Illuminate\Http\Request;

class ChurchRoleController extends Controller
{
    public function index(Request $request)
    {
        $churchRoles = ChurchRole::query()
            ->select(['id', 'name', 'text_color', 'background_color', 'border_color'])
            ->orderBy('priority')
            ->get();

        return response()->json([
            'church_roles' => $churchRoles,
        ]);
    }
}
