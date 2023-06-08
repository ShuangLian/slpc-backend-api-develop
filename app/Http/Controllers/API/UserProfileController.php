<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function updateIdentifyId(Request $request)
    {
        $request->validate([
            'identify_id' => 'required',
        ], [
            'identify_id.required' => '身分證字號為必填',
        ]);

        $user = Auth::user();

        $userProfile = UserProfile::query()
            ->where('user_id', $user['id'])
            ->firstOrFail();

        if ($userProfile['identify_id'] != null) {
            abort(400, '身分證字號已填寫，若欲修改，請洽教會修改資料');
        }

        $userProfile['identify_id'] = $request['identify_id'];
        $userProfile->save();

        $user->load('profile');

        return response()->json($user);
    }
}
