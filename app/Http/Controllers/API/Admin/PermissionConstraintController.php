<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionConstraint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionConstraintController extends Controller
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
        $request->validate([
            'permission_constraints.*.role_id' => 'required|integer',
            'permission_constraints.*.permissions' => 'present|array',
            'permission_constraints.*.permissions.*.page' => ['required', Rule::in([
                PermissionConstraint::PAGE_USER,
                PermissionConstraint::PAGE_EVENT,
                PermissionConstraint::PAGE_EQUIPMENT,
                PermissionConstraint::PAGE_DEDICATION,
                PermissionConstraint::PAGE_VISIT,
                PermissionConstraint::PAGE_INTERCESSION,
                PermissionConstraint::PAGE_REVIEW,
                PermissionConstraint::PAGE_PERMISSION,
            ])],
            'permission_constraints.*.permissions.*.action' => ['required', Rule::in([PermissionConstraint::ACTION_READ, PermissionConstraint::ACTION_UPDATE])],
        ]);

        $roleIds = array_map(function ($element) {
            return $element['role_id'];
        }, $request['permission_constraints']);

        PermissionConstraint::query()
            ->whereIn('role_id', $roleIds)
            ->delete();

        foreach ($request['permission_constraints'] as $permissionConstraint) {
            // TODO Double foreach 優化
            foreach ($permissionConstraint['permissions'] as $permission) {
                $newPermissionConstraint = new PermissionConstraint();
                $newPermissionConstraint['role_id'] = $permissionConstraint['role_id'];
                $newPermissionConstraint['page'] = $permission['page'];
                $newPermissionConstraint['action'] = $permission['action'];
                $newPermissionConstraint->save();
            }
        }

        return response(null);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
