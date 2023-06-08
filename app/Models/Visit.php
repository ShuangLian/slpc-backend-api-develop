<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_DONE = 'done';

    const FILTER_WEEKLY = 'weekly';

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'target_user_id')
            ->select(['user_id', 'name', 'country_code', 'phone_number', 'birthday']);
    }

    public function zone()
    {
        return $this->hasOne(UserChurchInfo::class, 'user_id', 'target_user_id')
            ->select(['user_id', 'zone']);
    }

    public function visitReason()
    {
        return $this->hasOne(VisitReason::class, 'id', 'visit_reason_id');
    }

    public static function migrateDataToNewUser($legacyUserId, $newUserId)
    {
        Visit::query()
            ->where('target_user_id', $legacyUserId)
            ->update(['target_user_id' => $newUserId]);

        UserTag::renewCountVisitTag($newUserId);
        UserTag::renewLastVisitDateTag($newUserId);
    }
}
