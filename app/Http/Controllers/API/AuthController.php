<?php

namespace App\Http\Controllers\API;

use App\Gateways\LineGateway;
use App\Http\Controllers\Controller;
use App\Managers\UserManager;
use App\Models\ChurchRole;
use App\Models\LegacyUserProfile;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserTag;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'id_token' => 'required',
        ]);

        $lineGateway = new LineGateway();
        try {
            $lineUser = $lineGateway->me($request['id_token']);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            abort(400, $exception->getMessage());
        }

        $user = User::getUserFromLine($lineUser['id']);
        if ($user == null) {
            abort(404, '使用者尚未註冊！');
        }

        if ($user['deleted_at'] != null) {
            abort(400, '帳號已被停用，請聯絡雙連教會');
        }

        return $user->createUserToken('line');
    }

    public function register(Request $request)
    {
        $request->validate(
            [
            'id_token' => 'required',
            'name' => 'required',
            'country_code' => 'required|numeric',
            'phone_number' => 'required|numeric',
            'birthday' => 'required|date_format:Y-m-d',
        ],
            [
                'country_code.numeric' => '國碼格式不符合',
                'phone_number.numeric' => '手機號碼格式不符合',
                'birthday.date_format' => '生日格式不符合',
            ]
        );

        $lineGateway = new LineGateway();
        try {
            $lineUser = $lineGateway->me($request['id_token']);

            $guestRole = ChurchRole::query()
                ->where('name', User::GUEST_NAME)
                ->first();

            if ($guestRole == null) {
                abort(400, '無設定預設教會中職務');
            }

            $user = UserManager::mapUserFromLegacyTable($request, $lineUser, $guestRole);

            if (!$user) {
                $user = UserManager::addLineUIDIfUserExistInUserTable($request, $lineUser);

                if (!$user) {
                    $user = new User();
                    $user['line_uid'] = $lineUser['id'];
                    $user['role'] = User::ROLE_USER;
                    $user->save();

                    $userProfile = new UserProfile();
                    $userProfile['user_id'] = $user['id'];
                    $userProfile['name'] = $request['name'];
                    $userProfile['country_code'] = $request['country_code'];
                    $userProfile['phone_number'] = UserProfile::getPhoneNumberFromRequest($request['phone_number'], $request['country_code']);
                    $userProfile['birthday'] = $request['birthday'];
                    $userProfile->save();

                    UserTag::mapOrCreateUserTag(null, $user['id'], $guestRole['id']);
                }
            }

            UserTag::renewLastVisitDateTag($user['id']);
            UserTag::renewCountVisitTag($user['id']);

            return $user->createUserToken('line');
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            abort(400, $exception->getMessage());
        }
    }
}
