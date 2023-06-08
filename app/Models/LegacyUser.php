<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegacyUser extends Model
{
    use HasFactory, SoftDeletes;

    public function legacyProfile()
    {
        return $this->hasOne(LegacyUserProfile::class, 'user_id')->withTrashed();
    }

    public function legacyChurchInfo()
    {
        return $this->hasOne(LegacyUserChurchInfo::class, 'user_id')->withTrashed();
    }

    public function legacyRelatives()
    {
        return $this->hasMany(LegacyUserRelative::class, 'user_id');
    }

    public function churchRoles()
    {
        return $this->hasManyThrough(ChurchRole::class, LegacyUserTag::class, 'user_id', 'id', 'id', 'value')
            ->select(['church_roles.id', 'name', 'text_color', 'background_color', 'border_color'])
            ->where('tag_key', UserTag::TAG_CHURCH_ROLE)
            ->orderBy('church_roles.priority');
    }
}
