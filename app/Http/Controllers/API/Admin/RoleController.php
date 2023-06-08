<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionConstraint;
use App\Models\Role;
use App\Models\UserTag;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = Role::query()
            ->with('permissions')
            ->get();

        return response()->json([
            'roles' => $roles,
        ]);
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
            'title' => 'required',
        ]);

        $role = new Role();
        $role['title'] = $request['title'];
        $role->save();

        return response()->json($role);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $role = Role::query()
            ->where('id', $id)
            ->with('permissions')
            ->firstOrFail();

        return response()->json($role);
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
        $role = Role::query()
            ->where('id', $id)
            ->firstOrFail();

        if ($request->has('title')) {
            $role['title'] = $request['title'];
        }

        $role->save();

        return response()->json($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userTag = UserTag::query()
            ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID)
            ->where('value', $id)
            ->first();

        if ($userTag !== null) {
            abort(400, '本角色有帳號在使用，無法刪除');
        }

        Role::query()
            ->where('id', $id)
            ->delete();

        PermissionConstraint::query()
            ->where('role_id', $id)
            ->delete();

        return response(null);
    }
}
