<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionConstraint;
use App\Models\User;
use App\Models\UserTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
           'username' => 'required',
           'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::query()
            ->select(['id', 'username', 'password', 'available_after', 'error_count'])
            ->where('role', User::ROLE_ADMIN)
            ->where('username', $request['username'])
            ->first();

        if ($user == null) {
            abort(404, '找不到該帳號');
        }

        $availableAfter = $user['available_after'];

        if (!Carbon::parse($availableAfter)->isPast()) {
            abort(400, "帳號鎖定中，請於 $availableAfter 後再嘗試");
        }

        if (!Hash::check($request['password'], $user['password'])) {
            $user['error_count'] = $user['error_count'] + 1;

            if ($user['error_count'] >= User::ERROR_COUNT_LIMIT) {
                $user['available_after'] = Carbon::now()->addMinutes(User::ERROR_LOCK_PERIOD)->toDateTimeString();
                $user->save();
                abort(400, '密碼錯誤次數連續錯誤達五次以上，帳號將鎖定 30 分鐘');
            }

            $user->save();
            abort(400, '帳號或密碼錯誤');
        }

        $user['error_count'] = 0;
        $user->save();

        $roleIds = UserTag::query()
            ->where('user_id', $user['id'])
            ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID)
            ->pluck('value');

        $permissions = PermissionConstraint::query()
            ->whereIn('role_id', $roleIds)
            ->select(DB::raw("DISTINCT CONCAT(page, ':', action) AS permission"))
            ->pluck('permission');

        return $user->createUserToken('admin', $permissions->toArray());
    }

    public function logout()
    {
        $user = Auth::user();

        $user->tokens()->delete();

        return response(null);
    }
}
