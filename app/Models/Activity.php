<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    const ACTIVITY_TYPE_EVENT = 'EVENT';
    const ACTIVITY_TYPE_EQUIPMENT = 'EQUIPMENT';
    const ACTIVITY_TYPE_SUNDAY = 'SUNDAY';

    public function activityPeriods()
    {
        return $this->hasMany(ActivityPeriod::class);
    }

    public function checkins()
    {
        return $this->hasMany(ActivityCheckin::class);
    }

    /**
     * @throws \Exception
     */
    public static function createActivityByPeriod($activityPeriod, $date, $activityType): self
    {
        if ($activityType == null) {
            throw new \Exception('Create Activity by period get null activity type');
        }

        $activity = new self();
        $activity['activity_type'] = $activityType;
        $activity['parent_id'] = $activityPeriod['activity_id'];
        $activity['date'] = $date;
        $activity['time'] = $activityPeriod['start_time'] . ' - ' . $activityPeriod['end_time'];
        $activity['type'] = $activityPeriod['type'];
        $activity['title'] = $activityPeriod['title'];
        $activity['presenter'] = $activityPeriod['presenter'];
        $activity['description'] = $activityPeriod['description'];
        $activity['registered_url'] = $activityPeriod['registered_url'];
        $activity->save();

        return $activity;
    }
}
