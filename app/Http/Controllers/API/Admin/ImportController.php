<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PermissionConstraintImport;
use App\Models\PermissionConstraint;
use App\Models\Role;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    const ACCEPTABLE_EXTENSIONS = ['csv', 'xls', 'xlsx'];

    public function permissionConstraintImport(Request $request)
    {
        $file = $request->file('file');

        if (!collect(self::ACCEPTABLE_EXTENSIONS)->contains($file->extension())) {
            abort(422, 'Invalid file extension');
        }

        $excel = Excel::toArray(new PermissionConstraintImport(), $request->file('file'));

        PermissionConstraint::query()
            ->truncate();

        foreach ($excel[0] as $value) {
            $role = Role::query()
                ->where('title', $value[0])
                ->first();

            if (empty($role)) {
                $role = new Role();
                $role['title'] = $value[0];
                $role->save();
            }
            $roleId = $role['id'];

            if (!empty($value[1])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_USER, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[2])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_USER, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[3])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_EVENT, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[4])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_EVENT, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[5])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_EQUIPMENT, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[6])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_EQUIPMENT, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[7])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_VISIT, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[8])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_VISIT, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[9])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_INTERCESSION, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[10])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_INTERCESSION, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[11])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_DEDICATION, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[12])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_DEDICATION, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[13])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_REVIEW, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[14])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_REVIEW, PermissionConstraint::ACTION_UPDATE);
            }

            if (!empty($value[15])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_PERMISSION, PermissionConstraint::ACTION_READ);
            }

            if (!empty($value[16])) {
                PermissionConstraint::create($roleId, PermissionConstraint::PAGE_PERMISSION, PermissionConstraint::ACTION_UPDATE);
            }
        }
    }
}
