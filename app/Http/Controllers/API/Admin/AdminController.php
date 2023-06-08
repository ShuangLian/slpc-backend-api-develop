<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $admins = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->with(['adminNicknameTag', 'permissionRoles']);

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $admins->orderBy($request['sorted_by'], $direction);
        }

        $admins = $admins->paginate();

        foreach ($admins as $admin) {
            $admin->append('permissions');
        }

        return response()->json($admins);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
            'role_ids' => 'array',
            'role_ids.*' => 'integer',
            'nickname' => 'required',
        ], [
            'username.required' => '使用者帳號為必填',
            'password.required' => '密碼為必填',
            'nickname.required' => '暱稱為必填',
            'password.min' => '密碼長度最少需要 12 碼',
        ]);

        $admin = User::query()
            ->where('username', $request['username'])
            ->first();

        if (!empty($admin)) {
            abort(400, '該帳號已註冊過了！');
        }

        $admin = new User();
        $admin['username'] = $request['username'];
        $admin['password'] = Hash::make($request['password']);
        $admin['role'] = User::ROLE_ADMIN;
        $admin->save();

        if (!empty($request['role_ids'])) {
            foreach ($request['role_ids'] as $roleId) {
                $tag = new UserTag();
                $tag['user_id'] = $admin['id'];
                $tag['tag_key'] = UserTag::TAG_ADMIN_PERMISSION_ROLE_ID;
                $tag['value'] = $roleId;
                $tag->save();
            }
        }

        $nicknameTag = UserTag::query()
            ->where('user_id', $admin['id'])
            ->where('tag_key', UserTag::TAG_ADMIN_NICKNAME)
            ->first();

        if ($nicknameTag == null) {
            $nicknameTag = new UserTag();
            $nicknameTag['user_id'] = $admin['id'];
            $nicknameTag['tag_key'] = UserTag::TAG_ADMIN_NICKNAME;
        }

        $nicknameTag['value'] = $request['nickname'];
        $nicknameTag->save();

        $admin->load(['adminNicknameTag', 'permissionRoles']);
        $admin->append('permissions');

        return response()->json($admin);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $admin = User::query()
            ->where('id', $id)
            ->with(['adminNicknameTag', 'permissionRoles'])
            ->first();

        if (empty($admin)) {
            abort(400, '找不到使用者');
        }

        if ($admin['role'] == User::ROLE_USER) {
            abort(400, '該使用者非管理員');
        }

        $admin->append('permissions');

        return response()->json($admin);
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
        $request->validate([
            'password' => Password::min(12)->mixedCase()->numbers()->symbols(),
            'role_ids' => 'array',
            'role_ids.*' => 'integer',
        ], [
            'password.min' => '密碼長度最少需要 12 碼',
        ]);

        $admin = User::query()
            ->where('id', $id)
            ->first();

        if (empty($admin)) {
            abort(400, '找不到該使用者');
        }

        if ($admin['role'] == User::ROLE_USER) {
            abort(400, '該使用者不是管理員');
        }

        if ($request->has('password')) {
            $admin['password'] = Hash::make($request['password']);
        }

        $admin->save();

        if ($request->has('role_ids')) {
            UserTag::query()
                ->where('user_id', $id)
                ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID)
                ->delete();

            foreach ($request['role_ids'] as $roleId) {
                $tag = new UserTag();
                $tag['user_id'] = $id;
                $tag['tag_key'] = UserTag::TAG_ADMIN_PERMISSION_ROLE_ID;
                $tag['value'] = $roleId;
                $tag->save();
            }
        }

        if ($request->has('nickname')) {
            $nicknameTag = UserTag::query()
                ->where('user_id', $admin['id'])
                ->where('tag_key', UserTag::TAG_ADMIN_NICKNAME)
                ->first();

            if ($nicknameTag == null) {
                $nicknameTag = new UserTag();
                $nicknameTag['user_id'] = $admin['id'];
                $nicknameTag['tag_key'] = UserTag::TAG_ADMIN_NICKNAME;
            }

            $nicknameTag['value'] = $request['nickname'];
            $nicknameTag->save();
        }

        $admin->load(['adminNicknameTag', 'permissionRoles']);
        $admin->append('permissions');

        return response()->json($admin);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        User::query()
            ->where('id', $id)
            ->delete();

        UserTag::query()
            ->where('user_id', $id)
            ->delete();
    }
}
