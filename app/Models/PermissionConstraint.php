<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionConstraint extends Model
{
    use HasFactory;

    const ACTION_READ = 'read';
    const ACTION_UPDATE = 'update';

    const PAGE_USER = 'user';
    const PAGE_EVENT = 'event';
    const PAGE_EQUIPMENT = 'equipment';
    const PAGE_VISIT = 'visit';
    const PAGE_INTERCESSION = 'intercession';
    const PAGE_DEDICATION = 'dedication';
    const PAGE_REVIEW = 'review';
    const PAGE_PERMISSION = 'permission';

    public static function create($roleId, $page, $action): self
    {
        $permissionConstraint = new self();
        $permissionConstraint['role_id'] = $roleId;
        $permissionConstraint['page'] = $page;
        $permissionConstraint['action'] = $action;
        $permissionConstraint->save();

        return $permissionConstraint;
    }
}
