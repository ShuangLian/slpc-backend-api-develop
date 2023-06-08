<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\UserChurchInfo;
use App\Models\UserProfile;
use App\Models\UserRelative;
use App\Models\UserTag;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::query()
            ->with([
                'profile',
                'relatives',
                'churchInfo',
                'ministries',
                'visitCountTag',
                'churchRoles',
                'attendChurchDayTags',
                'pendingReviewColumns',
            ])
            ->find(Auth::id());

        if ($user == null) {
            abort(404, '找不到該使用者！');
        }

        if ($user['profile'] !== null) {
            $user['profile'] = UserProfile::getAfterValueIfSpecifyColumnIsReviewing($user['profile'], $user['id']);
        }

        if ($user['churchInfo'] !== null) {
            $user['church_info'] = UserChurchInfo::getAfterValueIfSpecifyColumnIsReviewing($user['churchInfo'], $user['id']);
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
        $request->validate([
            'profile.is_married' => 'boolean|nullable',
            'profile.email' => 'email|nullable',
            'profile.gender' => ['string', Rule::in(User::MALE, User::FEMALE), 'nullable'],
            'relatives.*.relationship' => [Rule::in(UserRelative::getRelativeArray()), 'nullable'],
            'relatives.*.is_alive' => 'boolean|nullable',
            'relatives.*.is_christened' => 'boolean|nullable',
            'church_info.participation_status' => [Rule::in(UserChurchInfo::getParticipationArray()), 'nullable'],
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
            'profile.is_married.boolean' => '婚姻狀態格式不符合',
            'profile.email.email' => 'E-mail格式不符合',
            'relatives.*.is_alive.boolean' => '存歿狀態格式不符合',
            'relatives.*.is_christened.boolean' => '信仰狀態格式不符合',
        ]);

        $user = Auth::user();

        if ($user['id'] != $id) {
            abort(401, 'Unauthenticated.');
        }

        // Update UserProfile
        $userProfile = UserProfile::query()
            ->where('user_id', $user['id'])
            ->first();

        if (empty($userProfile)) {
            $userProfile = new UserProfile();
            $userProfile['user_id'] = $user['id'];
        }

        $profileColumns = [
            'gender', 'is_married', 'company_area_code', 'company_phone_number', 'home_area_code',
            'home_phone_number', 'email', 'line_id', 'job_title', 'highest_education',
            'address', 'emergency_name', 'emergency_relationship',
            'emergency_contact', 'postal_code_id'
        ];

        foreach ($profileColumns as $column) {
            if ($request->has('profile.' . $column)) {
                $userProfile[$column] = $request['profile'][$column];
            }
        }

        if ($request->has('profile.postal_code_id')) {
            UserProfile::appendCityAndRegionFromPostalCode($userProfile, $request['profile.postal_code_id']);
        }

        if ($request->has('profile.name')) {
            Review::createReview($user['id'], Review::NAME, $userProfile['name'], $request['profile']['name']);
        }

        if ($request->has(['profile.phone_number', 'profile.country_code'])) {
            $phoneWithCountryCode = $request['profile']['country_code'] . UserProfile::getPhoneNumberFromRequest($request['profile']['phone_number'], $request['profile']['country_code']);
            Review::createReview($user['id'], Review::PHONE, $userProfile['country_code'] . $userProfile['phone_number'], $phoneWithCountryCode);
        }

        if ($request->has('profile.birthday')) {
            try {
                Review::createReview($user['id'], Review::BIRTH, $userProfile['birthday'], $request['profile']['birthday']);
            } catch (\Exception $exception) {
                Log::error('Update User(user_id = ' . $user['id'] . ') birthday error with: ' . $exception->getMessage());
            }
        }

        if ($request->has('profile.identify_id')) {
            if (!empty($userProfile['identify_id'])) {
                Review::createReview($user['id'], Review::IDENTIFY_ID, $userProfile['identify_id'], $request['profile']['identify_id']);
            }

            if (empty($userProfile['identify_id'])) {
                $userProfile['identify_id'] = $request['profile']['identify_id'];
            }
        }

        if ($request->has('profile.liff_avatar_url')) {
            $userProfile['liff_avatar_url'] = $request['profile']['liff_avatar_url'];
        }

        try {
            $userProfile->save();
        } catch (QueryException $exception) {
            Log::error($exception->getMessage());
            abort(400, '此身分證號已被使用');
        }

        // Update UserChurchInfo
        $userChurchInfo = UserChurchInfo::query()
            ->where('user_id', $user['id'])
            ->first();

        if (empty($userChurchInfo)) {
            $userChurchInfo = new UserChurchInfo();
            $userChurchInfo['user_id'] = $user['id'];
        }

        $churchInfoColumns = [
            'membership_status',
            'membership_location',
            'serving_experience',
            'confirmed_church',
            'confirmed_at',
            'skill',
        ];

        foreach ($churchInfoColumns as $column) {
            if ($request->has('church_info.' . $column)) {
                $userChurchInfo[$column] = $request['church_info'][$column];
            }
        }

        $churchInfoReviewColumns = [
            'adulthood_christened_at',
            'adulthood_christened_church',
            'childhood_christened_at',
            'childhood_christened_church',
        ];

        foreach ($churchInfoReviewColumns as $column) {
            if ($request->has('church_info.' . $column)) {
                $userChurchInfo->updateOrReviewColumn($column, $request['church_info'][$column]);
            }
        }

        $userChurchInfo->save();

        // Update UserRelative
        if (!empty($request['relatives'])) {
            UserRelative::query()
                ->where('user_id', $user['id'])
                ->forceDelete();

            foreach ($request['relatives'] as $item) {
                $userRelative = new UserRelative();
                $userRelative['user_id'] = $id;
                foreach (UserRelative::getRelativeColumns() as $relative) {
                    $userRelative[$relative] = $item[$relative];
                }
                $userRelative->save();
            }
        }

        // Update User Ministries
        if (!empty($request['ministries'])) {
            UserTag::query()
                ->where('user_id', $user['id'])
                ->where('tag_key', UserTag::TAG_USER_MINISTRY)
                ->delete();

            foreach ($request['ministries'] as $item) {
                $tag = new UserTag();
                $tag['user_id'] = $user['id'];
                $tag['tag_key'] = UserTag::TAG_USER_MINISTRY;
                $tag['value'] = $item;
                $tag->save();
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
            ->with(['profile', 'relatives', 'churchInfo', 'ministries', 'attendChurchDayTags'])
            ->find(Auth::id());

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
