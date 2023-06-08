<?php

namespace App\Http\Controllers\API\Admin;

use App\Exports\UserExport;
use App\Http\Controllers\Controller;
use App\Managers\UserManager;
use App\Models\PostalCode;
use App\Models\ChurchRole;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserTag;
use App\Models\Zone;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::withTrashed()
            ->where('role', User::ROLE_USER)
            ->where(function ($query) {
                $query->where('is_matched', false)
                    ->orWhereNull('is_matched');
            })
            ->orderByRaw('is_legacy is true');

        if ($request->has('filter')) {
            if ($request['filter'] == User::FILTER_6_MONTH_NO_VISIT_LOG) {
                $userIds = UserTag::query()
                    ->where('tag_key', UserTag::TAG_LAST_VISIT_DATE)
                    ->where(function ($query) {
                        $query->where('value', '<', Carbon::now()->subMonths(6)->toDateString())
                            ->orWhereNull('value');
                    })
                    ->pluck('user_id');

                $users->whereIn('id', $userIds);
            }
        }

        //姓名搜尋
        if ($request->has('names')) {
            $ids = UserProfile::withTrashed();

            foreach ($request['names'] as $name) {
                $ids->orWhere('name', 'like', '%' . $name . '%');
            }
            $users->whereIn('id', $ids->pluck('user_id'));
        }

        if ($request->has('name')) {
            $userIds = UserProfile::withTrashed()
                ->where('name', 'like', '%' . $request['name'] . '%')
                ->pluck('user_id');

            $users->whereIn('id', $userIds);
        }

        //身份搜尋（教會內身份，非系統身份）
        if ($request->has('roles')) {
            $ids = UserTag::query()
                ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
                ->whereIn('value', $request['roles'])
                ->pluck('user_id');

            $users->whereIn('id', $ids);
        }

        //可參與事工搜尋
        if ($request->has('ministries')) {
            $ids = UserTag::query()
                ->whereIn('value', $request['ministries'])
                ->select('user_id')
                ->get();
            $users->whereIn('id', $ids);
        }

        //生日範圍搜尋
        if ($request->has(['from', 'to'])) {
            $from = Carbon::parse($request['from']);
            $to = Carbon::parse($request['to']);
            $ids = UserProfile::query()
                ->where('birthday', '>=', $from)
                ->where('birthday', '<=', $to)
                ->pluck('user_id');

            $users->whereIn('id', $ids);
        }

        //身份證搜尋
        if ($request->has('identify_ids')) {
            $ids = UserProfile::query();

            foreach ($request['identify_ids'] as $identifyId) {
                $ids->orWhere('identify_id', 'like', '%' . $identifyId . '%');
            }
            $users->whereIn('id', $ids->pluck('user_id'));
        }

        //會友編號搜尋
        if ($request->has('ids')) {
            $users->whereIn('id', $request['ids']);
        }

        $users->with(
            [
                'profile:id,user_id,name,gender,country_code,phone_number,birthday',
                'churchInfo:id,user_id,zone',
                'visitCountTag',
                'churchRoles',
            ]
        );

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';

            switch ($request['sorted_by']) {
                case 'birthday':
                    $users->orderBy(
                        UserProfile::withTrashed()
                            ->select('birthday')
                            ->whereColumn('user_id', 'users.id')
                            ->limit(1),
                        $direction
                    );
                    break;
                case 'id':
                    $users->orderBy('id', $direction);
                    break;
                case 'visit_count':
                    $users->orderBy(
                        UserTag::query()
                            ->select('value')
                            ->whereColumn('user_id', 'users.id')
                            ->where('tag_key', UserTag::TAG_COUNT_VISIT)
                            ->limit(1),
                        $direction
                    );
                    break;
            }
        } else {
            $users->orderByDesc('id');
        }

        // 有無包含「雙連之友」
        $isIncludeGuest = filter_var($request['is_include_custom'], FILTER_VALIDATE_BOOLEAN) ?? false;
        $guestIds = UserTag::query()
            ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
            ->where('value', function ($query) {
                $query->select('id')
                    ->from('church_roles')
                    ->where('name', User::GUEST_NAME);
            })
            ->pluck('user_id');

        // 未勾選「雙連之友」 check box，顯示不包含雙連之友的會員清單
        if (!$isIncludeGuest) {
            $users->whereNotIn('id', $guestIds);
        }

        // 勾選「雙連之友」 check box，僅顯示雙連之友會員清單
        if ($isIncludeGuest) {
            $users->whereIn('id', $guestIds);
        }

        // 會友會籍
        if ($request->has('membership_locations')) {
            $membershipLocationUserIds = UserChurchInfo::query()
                ->whereIn('membership_location', $request['membership_locations'])
                ->pluck('user_id');

            $users->whereIn('id', $membershipLocationUserIds);
        }

        // 是否註冊 line oa
        if ($request->has('is_registered_line')) {
            $isRegisteredLine = filter_var($request['is_registered_line'], FILTER_VALIDATE_BOOLEAN) ?? false;

            if ($isRegisteredLine) {
                $users->whereNotNull('line_uid');
            } else {
                $users->where(function ($query) {
                    $query->where('line_uid', '')
                        ->orWhereNull('line_uid');
                });
            }
        }

        // 牧區搜尋
        if ($request->has('zones')) {
            $childZoneIds = Zone::query()
                ->whereIn('parent_id', $request['zones'])
                ->pluck('id');

            $userIds = UserChurchInfo::query()
                ->whereIn('zone', [...$childZoneIds, ...$request['zones']])
                ->pluck('user_id');

            $users->whereIn('id', $userIds);
        }

        // return response()->json($users->paginate());
        return response()->json($users->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //todo need refactor
        $request->validate([
            'profile.name' => 'required',
            'profile.birthday' => 'required',
            'profile.country_code' => 'required',
            'profile.phone_number' => 'required|min:9',
            'profile.is_married' => 'boolean|nullable',
            'profile.email' => 'email|nullable',
            'profile.gender' => ['string', Rule::in(User::MALE, User::FEMALE), 'nullable'],
            'relatives.*.relationship' => [Rule::in(UserRelative::getRelativeArray()), 'nullable'],
            'relatives.*.is_alive' => 'boolean|nullable',
            'relatives.*.is_christened' => 'boolean|nullable',
            'church_info.membership_status' => ['string', Rule::in(UserChurchInfo::getMembershipArray()), 'nullable'],
            'church_info.participation_status' => [Rule::in(UserChurchInfo::getParticipationArray()), 'nullable'],
            'church_info.membership_location' => ['string', Rule::in(UserChurchInfo::getChurchLocationArray()), 'nullable'],
            'church_info.skill' => 'max:50',
            'church_info.attend_church_days' => 'array',
            'church_info.attend_church_days.*' => Rule::in([
                CarbonInterface::SUNDAY,
                CarbonInterface::MONDAY,
                CarbonInterface::TUESDAY,
                CarbonInterface::WEDNESDAY,
                CarbonInterface::THURSDAY,
                CarbonInterface::FRIDAY,
                CarbonInterface::SATURDAY,
            ]),
        ], [
            'profile.name.required' => '姓名為必填',
            'profile.birthday.required' => '生日為必填',
            'profile.country_code.required' => '國碼為必填',
            'profile.phone_number.required' => '手機號碼為必填',
            'profile.phone_number.min' => '手機號碼格式不符合',
            'profile.is_married.boolean' => '婚姻狀態格式不符合',
            'profile.email.email' => 'E-mail格式不符合',
            'relatives.*.is_alive.boolean' => '存歿狀態格式不符合',
            'relatives.*.is_christened.boolean' => '信仰狀態格式不符合',
            'church_info.skill.max' => '會友專長/才藝內容過長',
        ]);

        $user = new User();
        $user['role'] = User::ROLE_USER;
        $user->save();

        $userProfile = new UserProfile();
        $userProfile['user_id'] = $user['id'];

        $userChurchInfo = new UserChurchInfo();
        $userChurchInfo['user_id'] = $user['id'];

        $profiles = UserProfile::getProfileColumns();
        $relatives = UserRelative::getRelativeColumns();
        $churchInfo = UserChurchInfo::getChurchInfoColumns();

        foreach ($profiles as $item) {
            if ($request->has('profile.' . $item)) {
                $userProfile[$item] = $request['profile'][$item];
            }
        }

        if ($request->has('profile.postal_code_id')) {
            UserProfile::appendCityAndRegionFromPostalCode($userProfile, $request['profile.postal_code_id']);
        }

        if ($request->has('profile.dashboard_avatar_url')) {
            $userProfile['dashboard_avatar_url'] = $request['profile']['dashboard_avatar_url'];
        }
        $userProfile['phone_number'] = UserProfile::getPhoneNumberFromRequest($request['profile']['phone_number'], $request['profile']['country_code']);
        $userProfile['birthday'] = Carbon::parse($request['profile']['birthday'])->timezone('Asia/Taipei')->format('Y-m-d');
        try {
            $userProfile->save();
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
            abort(400, '此身分證號已被使用');
        }

        foreach ($churchInfo as $item) {
            if ($request->has('church_info.' . $item)) {
                $userChurchInfo[$item] = $request['church_info'][$item];
            }
        }
        if ($request->has('church_info.adulthood_christened_at')) {
            $userChurchInfo['adulthood_christened_at'] = Carbon::parse($request['church_info']['adulthood_christened_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        if ($request->has('church_info.childhood_christened_at')) {
            $userChurchInfo['childhood_christened_at'] = Carbon::parse($request['church_info']['childhood_christened_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        if ($request->has('church_info.confirmed_at')) {
            $userChurchInfo['confirmed_at'] = Carbon::parse($request['church_info']['confirmed_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        $userChurchInfo->save();

        if (!empty($request['relatives'])) {
            foreach ($request['relatives'] as $item) {
                $userRelative = new UserRelative();
                $userRelative['user_id'] = $user['id'];
                foreach ($relatives as $relative) {
                    $userRelative[$relative] = $item[$relative];
                }
                $userRelative->save();
            }
        }

        if (!empty($request['ministries'])) {
            foreach ($request['ministries'] as $item) {
                $tag = new UserTag();
                $tag['user_id'] = $user['id'];
                $tag['tag_key'] = UserTag::TAG_USER_MINISTRY;
                $tag['value'] = $item;
                $tag->save();
            }
        }

        if ($request->has('church_info.church_role_ids')) {
            foreach ($request['church_info.church_role_ids'] as $churchRoleId) {
                $userTag = new UserTag();
                $userTag['user_id'] = $user['id'];
                $userTag['tag_key'] = UserTag::TAG_CHURCH_ROLE;
                $userTag['value'] = $churchRoleId;
                $userTag->save();
            }
        }

        if ($request->has('church_info.attend_church_days')) {
            foreach ($request['church_info.attend_church_days'] as $day) {
                $userTag = new UserTag();
                $userTag['user_id'] = $user['id'];
                $userTag['tag_key'] = UserTag::TAG_ATTEND_CHURCH_DAY;
                $userTag['value'] = $day;
                $userTag->save();
            }
        }

        UserTag::renewLastVisitDateTag($user['id']);
        UserTag::renewCountVisitTag($user['id']);

        $user = User::query()
            ->where('id', $user['id'])
            ->with(['profile', 'relatives', 'churchInfo', 'ministries', 'churchRoles', 'attendChurchDayTags'])
            ->first();

        return response()->json($user->get());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::withTrashed()
            ->where('id', $id)
            ->with(['profile', 'relatives', 'churchInfo', 'ministries', 'churchRoles', 'attendChurchDayTags'])
            ->first();

        if ($user == null) {
            abort(404, '找不到該使用者！');
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //todo need refactor
        $request->validate(
            [
                'profile.phone_number' => 'min:9',
                'profile.is_married' => 'boolean|nullable',
                'profile.email' => 'email|nullable',
                'profile.gender' => ['string', Rule::in(User::MALE, User::FEMALE), 'nullable'],
                'relatives.*.relationship' => [Rule::in(UserRelative::getRelativeArray())],
                'relatives.*.is_alive' => 'boolean|nullable',
                'relatives.*.is_christened' => 'boolean|nullable',
                'church_info.membership_status' => ['string', Rule::in(UserChurchInfo::getMembershipArray()), 'nullable'],
                'church_info.participation_status' => [Rule::in(UserChurchInfo::getParticipationArray()), 'nullable'],
                'church_info.membership_location' => ['string', Rule::in(UserChurchInfo::getChurchLocationArray()), 'nullable'],
                'church_info.skill' => 'max:50',
                'church_info.attend_church_days' => 'array',
                'church_info.attend_church_days.*' => Rule::in([
                    CarbonInterface::SUNDAY,
                    CarbonInterface::MONDAY,
                    CarbonInterface::TUESDAY,
                    CarbonInterface::WEDNESDAY,
                    CarbonInterface::THURSDAY,
                    CarbonInterface::FRIDAY,
                    CarbonInterface::SATURDAY,
                ]),
            ],
            [
                'profile.phone_number.min' => '手機號碼格式不符合',
                'profile.is_married.boolean' => '婚姻狀態格式不符合',
                'profile.email.email' => 'E-mail格式不符合',
                'relatives.*.is_alive.boolean' => '存歿狀態格式不符合',
                'relatives.*.is_christened.boolean' => '信仰狀態格式不符合',
                'church_info.skill.max' => '會友專長/才藝內容過長',
            ]
        );

        $user = User::query()
            ->where('id', $id)
            ->first();

        if ($user == null) {
            abort(404, '找不到該使用者！');
        }

        $userProfile = UserProfile::query()
            ->where('user_id', $id)
            ->first();
        if ($userProfile == null) {
            $userProfile = new UserProfile();
            $userProfile['user_id'] = $id;
        }

        $userChurchInfo = UserChurchInfo::query()
            ->where('user_id', $id)
            ->first();
        if ($userChurchInfo == null) {
            $userChurchInfo = new UserChurchInfo();
            $userChurchInfo['user_id'] = $id;
        }

        $profiles = UserProfile::getProfileColumns();
        $relatives = UserRelative::getRelativeColumns();
        $churchInfo = UserChurchInfo::getChurchInfoColumns();

        foreach ($profiles as $item) {
            if ($request->has('profile.' . $item)) {
                $userProfile[$item] = $request['profile'][$item];
            }
        }

        if ($request->has('profile.postal_code_id')) {
            UserProfile::appendCityAndRegionFromPostalCode($userProfile, $request['profile.postal_code_id']);
        }

        if ($request->has('profile.phone_number')) {
            $userProfile['phone_number'] = UserProfile::getPhoneNumberFromRequest($request['profile']['phone_number'], $request['profile']['country_code']);
        }

        if ($request->has('profile.birthday')) {
            $userProfile['birthday'] = Carbon::parse($request['profile']['birthday'])->timezone('Asia/Taipei')->format('Y-m-d');
        }

        if ($request->has('profile.dashboard_avatar_url')) {
            $userProfile['dashboard_avatar_url'] = $request['profile']['dashboard_avatar_url'];
        }

        try {
            $userProfile->save();
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
            abort(400, '此身分證號已被使用');
        }

        foreach ($churchInfo as $item) {
            if ($request->has('church_info.' . $item)) {
                $userChurchInfo[$item] = $request['church_info'][$item];
            }
        }
        if ($request->has('church_info.adulthood_christened_at')) {
            $userChurchInfo['adulthood_christened_at'] = $request['church_info']['adulthood_christened_at'] == null ?
                $request['church_info']['adulthood_christened_at'] :
                Carbon::parse($request['church_info']['adulthood_christened_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        if ($request->has('church_info.childhood_christened_at')) {
            $userChurchInfo['childhood_christened_at'] = $request['church_info']['childhood_christened_at'] == null ?
                $request['church_info']['childhood_christened_at'] :
                Carbon::parse($request['church_info']['childhood_christened_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        if ($request->has('church_info.confirmed_at')) {
            $userChurchInfo['confirmed_at'] = $request['church_info']['confirmed_at'] == null ?
                $request['church_info']['confirmed_at'] :
                Carbon::parse($request['church_info']['confirmed_at'])->timezone('Asia/Taipei')->format('Y-m-d');
        }
        $userChurchInfo->save();

        if (!empty($request['relatives'])) {
            UserRelative::query()
                ->where('user_id', $id)
                ->forceDelete();

            foreach ($request['relatives'] as $item) {
                $userRelative = new UserRelative();
                $userRelative['user_id'] = $id;
                foreach ($relatives as $relative) {
                    $userRelative[$relative] = $item[$relative];
                }
                $userRelative->save();
            }
        }

        if (!empty($request['ministries'])) {
            UserTag::query()
                ->where('user_id', $id)
                ->where('tag_key', UserTag::TAG_USER_MINISTRY)
                ->delete();

            foreach ($request['ministries'] as $item) {
                $tag = new UserTag();
                $tag['user_id'] = $id;
                $tag['tag_key'] = UserTag::TAG_USER_MINISTRY;
                $tag['value'] = $item;
                $tag->save();
            }
        }

        if ($request->has('church_info.church_role_ids')) {
            UserTag::query()
                ->where('user_id', $user['id'])
                ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
                ->delete();
            foreach ($request['church_info.church_role_ids'] as $churchRoleId) {
                $userTag = new UserTag();
                $userTag['user_id'] = $user['id'];
                $userTag['tag_key'] = UserTag::TAG_CHURCH_ROLE;
                $userTag['value'] = $churchRoleId;
                $userTag->save();
            }
        }

        if ($request->has('church_info.attend_church_days')) {
            UserTag::query()
                ->where('user_id', $user['id'])
                ->where('tag_key', UserTag::TAG_ATTEND_CHURCH_DAY)
                ->delete();

            foreach ($request['church_info.attend_church_days'] as $day) {
                $userTag = new UserTag();
                $userTag['user_id'] = $user['id'];
                $userTag['tag_key'] = UserTag::TAG_ATTEND_CHURCH_DAY;
                $userTag['value'] = $day;
                $userTag->save();
            }
        }

        $user = User::query()
            ->where('id', $id)
            ->with(['profile', 'relatives', 'churchInfo', 'ministries', 'churchRoles', 'attendChurchDayTags'])
            ->first();

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        UserManager::deleteUser($id, $request['force'] ?? false);

        return response(null);
    }

    public function restore($id)
    {
        UserManager::restoreUser($id);

        return response(null);
    }

    public function exportUserNotRegistered()
    {
        return (new UserExport())->download('legacy_users.xlsx');
    }

    public function addZoneIdFromPostalCodeId(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer'
        ], [
            'user_id.integer' => '使用者 id 格式不符合'
        ]);

        $userId = $request['user_id'];
        $userProfile = UserProfile::query()
            ->where('user_id', $userId)
            ->first();

        $postalCodeId = $userProfile['postal_code_id'] ?? null;

        if ($postalCodeId == null) {
            abort(400, '請自行選擇牧區');
        }

        $postalCode = PostalCode::query()
            ->where('id', $postalCodeId)
            ->first();

        if ($postalCode == null) {
            abort(400, '郵遞區號填寫錯誤');
        }

        $userChurchInfo = UserChurchInfo::query()
            ->where('user_id', $userId)
            ->first();

        if ($userChurchInfo == null) {
            $userChurchInfo = new UserChurchInfo();
            $userChurchInfo['user_id'] = $userId;
        }

        $userChurchInfo['zone'] = $postalCode['zone_id'];
        $userChurchInfo->save();

        $zone = Zone::query()
            ->where('id', $postalCode['zone_id'])
            ->with('parent')
            ->first();

        if ($zone == null) {
            abort(400, '請自行選擇牧區');
        }
        return response()->json($zone);
    }
}
