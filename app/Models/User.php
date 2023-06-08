<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    const MALE = 'male';
    const FEMALE = 'female';

    const GUEST_NAME = '雙連之友';

    const ERROR_COUNT_LIMIT = 5;
    const ERROR_LOCK_PERIOD = 30;

    const FILTER_6_MONTH_NO_VISIT_LOG = 'last-visit-before-6-months';

    protected $hidden = ['password'];
    protected $casts = [
        'is_legacy' => 'boolean',
        'is_matched' => 'boolean',
        'line_uid' => 'boolean',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class)->withTrashed();
    }

    public function relatives()
    {
        return $this->hasMany(UserRelative::class)->withTrashed();
    }

    public function churchInfo()
    {
        return $this->hasOne(UserChurchInfo::class)->withTrashed();
    }

    public function ministries()
    {
        return $this->hasManyThrough(Ministry::class, UserTag::class, 'user_id', 'id', 'id', 'value')
            ->where('tag_key', UserTag::TAG_USER_MINISTRY)
            ->select(['ministries.id', 'name']);
    }

    public function adminNicknameTag()
    {
        return $this->hasOne(UserTag::class)
            ->select(['id', 'user_id', 'value'])
            ->where('tag_key', UserTag::TAG_ADMIN_NICKNAME);
    }

    public function visitCountTag()
    {
        return $this->hasOne(UserTag::class)
            ->select(['id', 'user_id', 'value'])
            ->where('tag_key', UserTag::TAG_COUNT_VISIT);
    }

    public function attendChurchDayTags()
    {
        return $this->hasMany(UserTag::class)
            ->select(['id', 'user_id', 'value'])
            ->where('tag_key', UserTag::TAG_ATTEND_CHURCH_DAY);
    }

    public function permissionRoles()
    {
        $roleIds = UserTag::query()
            ->where('user_id', $this['id'])
            ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID)
            ->pluck('value');

        return $this->hasManyThrough(Role::class, UserTag::class, 'user_id', 'id', 'id', 'value')
            ->select(['roles.id', 'title'])
            ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID);
    }

    public function churchRoles()
    {
        return $this->hasManyThrough(ChurchRole::class, UserTag::class, 'user_id', 'id', 'id', 'value')
            ->select(['church_roles.id', 'name', 'text_color', 'background_color', 'border_color'])
            ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
            ->orderBy('church_roles.priority');
    }

    public function pendingReviewColumns()
    {
        return $this->hasMany(Review::class, 'user_id', 'id')
            ->select(['id', 'user_id', 'type'])
            ->where('status', Review::PENDING);
    }

    public function getPermissionsAttribute()
    {
        $roleIds = UserTag::query()
            ->where('user_id', $this['id'])
            ->where('tag_key', UserTag::TAG_ADMIN_PERMISSION_ROLE_ID)
            ->pluck('value');

        return PermissionConstraint::query()
            ->whereIn('role_id', $roleIds)
            ->get(['page', 'action']);
    }

    public static function getUserFromLine($lineUID): ?self
    {
        return self::withTrashed()
            ->where('line_uid', $lineUID)
            ->first();
    }

    public function createUserToken($name, $abilities = ['*']): self
    {
        $this->tokens()->delete();
        $this['token'] = $this->createToken($name, $abilities)->plainTextToken;

        return $this;
    }

    public function scopeOnlyNewUsers($query)
    {
        return $query->where('is_legacy', false);
    }
}
