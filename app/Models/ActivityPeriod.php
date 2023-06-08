<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class ActivityPeriod extends Model
{
    use HasFactory;
    use SoftDeletes;

    const DAILY_REG_RULE = '/\/REPEATED\/ON\/DAILY$/';
    const WEEKLY_REG_RULE = '/\/REPEATED\/ON\/WEEK\/./';
    const MONTHLY_REG_RULE = '/\/REPEATED\/ON\/WEEK_NUMBER\/.*\/WEEK\/.*/';

    const DAILY_SEARCH_PATTERN = '/DAILY';
    const WEEKLY_SEARCH_PATTERN = '/WEEK/';
    const MONTHLY_SEARCH_PATTERN = '/WEEK_NUMBER/';

    public static function createActivityPeriodFromRequest(int $activityId, Request $request, string $activityPeriodRuleString, $periodInfo): self
    {
        $activityPeriod = new self();
        $activityPeriod['activity_id'] = $activityId;
        $activityPeriod['from_date'] = $request['from_date'];
        $activityPeriod['to_date'] = $request['to_date'];
        $activityPeriod['start_time'] = $periodInfo['start_time'];
        $activityPeriod['end_time'] = $periodInfo['end_time'];
        $activityPeriod['rule'] = $activityPeriodRuleString;
        $activityPeriod['type'] = $request['type'];
        $activityPeriod['title'] = $request['title'];
        $activityPeriod['presenter'] = $request['presenter'];
        $activityPeriod['description'] = $request['description'];
        $activityPeriod['registered_url'] = $request['registered_url'];
        $activityPeriod->save();

        return $activityPeriod;
    }
}
