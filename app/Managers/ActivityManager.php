<?php

namespace App\Managers;

use App\Models\Activity;
use App\Models\ActivityPeriod;
use App\Models\ActivityPeriodType;
use App\Utils\DateTimeUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityManager
{
    /**
     * @throws \Exception
     */
    public static function getActivityPeriodRuleByType($ruleString): array
    {
        $ruleByType = [];

        $activityPeriodType = ActivityPeriodType::getActivityPeriodTypeFromRule($ruleString);
        $ruleByType['type'] = $activityPeriodType;

        if ($activityPeriodType == ActivityPeriodType::WEEKLY) {
            $dayOfWeek = substr($ruleString, strpos($ruleString, ActivityPeriod::WEEKLY_SEARCH_PATTERN) + strlen(ActivityPeriod::WEEKLY_SEARCH_PATTERN), 1);

            if ($dayOfWeek < 0 || 6 < $dayOfWeek) {
                throw new \Exception('ActivityManager parse DayOfWeek get error value. Input String: ' . $ruleString);
            }

            $ruleByType['dayOfWeek'] = $dayOfWeek;
        }

        if ($activityPeriodType == ActivityPeriodType::MONTHLY) {
            $weekOfMonth = substr($ruleString, strpos($ruleString, ActivityPeriod::MONTHLY_SEARCH_PATTERN) + strlen(ActivityPeriod::MONTHLY_SEARCH_PATTERN), 1);
            $dayOfWeek = substr($ruleString, strpos($ruleString, ActivityPeriod::WEEKLY_SEARCH_PATTERN) + strlen(ActivityPeriod::WEEKLY_SEARCH_PATTERN));

            if ($dayOfWeek < 0 || 6 < $dayOfWeek) {
                throw new \Exception('ActivityManager parse DayOfWeek get error value. Input String: ' . $ruleString);
            }
            if ($weekOfMonth < 1 || 5 < $weekOfMonth) {
                throw new \Exception('ActivityManager parse WeekOfMonth get error value. Input String: ' . $ruleString);
            }

            $ruleByType['dayOfWeek'] = $dayOfWeek;
            $ruleByType['weekOfMonth'] = $weekOfMonth;
        }

        return $ruleByType;
    }

    /**
     * @throws \Exception
     */
    public static function createActivitiesByActivityPeriod($activityPeriod): void
    {
        $today = Carbon::now();
        $endDate = Carbon::now()->addDays(14);
        $datePeriod = DateTimeUtil::getDatePeriod($today, $endDate);

        $datesAlreadyCreated = Activity::query()
            ->where('parent_id', $activityPeriod['activity_id'])
            ->where('time', $activityPeriod['start_time'] . ' - ' . $activityPeriod['end_time'])
            ->pluck('date');

        try {
            $dates = self::getShouldCreateDatesByRule($activityPeriod, $datePeriod, $datesAlreadyCreated);
            $activityType = Activity::query()
                ->where('id', $activityPeriod['activity_id'])
                ->pluck('activity_type')
                ->first();

            foreach ($dates as $date) {
                Activity::createActivityByPeriod($activityPeriod, $date, $activityType);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), [
                'activityPeriod' => $activityPeriod,
                'datePeriod' => $datePeriod,
                'datesAlreadyCreated' => $datesAlreadyCreated,
            ]);
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    public static function getShouldCreateDatesByRule($activityPeriod, $datePeriod, $datesAlreadyCreated): array
    {
        $dates = [];
        $ruleByType = self::getActivityPeriodRuleByType($activityPeriod['rule']);

        foreach ($datePeriod as $date) {
            $date = Carbon::parse($date->format('Y-m-d'));

            // 該日期已建立
            if ($datesAlreadyCreated->contains($date->toDateString())) {
                continue;
            }

            // 該日期超出週期範圍
            if (!$date->between($activityPeriod['from_date'], $activityPeriod['to_date'])) {
                continue;
            }

            if ($ruleByType['type'] == ActivityPeriodType::DAILY) {
                $dates[] = $date->toDateString();
            }
            if ($ruleByType['type'] == ActivityPeriodType::WEEKLY) {
                if ($date->dayOfWeek == $ruleByType['dayOfWeek']) {
                    $dates[] = $date->toDateString();
                }
            }
            if ($ruleByType['type'] == ActivityPeriodType::MONTHLY) {
                if ($date->weekOfMonth == $ruleByType['weekOfMonth'] && $date->dayOfWeek == $ruleByType['dayOfWeek']) {
                    $dates[] = $date->toDateString();
                }
            }
        }

        return $dates;
    }

    /**
     * @throws \Exception
     */
    public static function getActivityPeriodRuleString(string $period, ?int $dayOfWeek, ?int $weekOfMonth): string
    {
        if (($dayOfWeek < 0 || 6 < $dayOfWeek) && !empty($dayOfWeek)) {
            throw new \Exception('DayOfWeek out of range. Range: 0~6. Input value: ' . $dayOfWeek);
        }
        if (($weekOfMonth < 1 || 5 < $weekOfMonth) && !empty($weekOfMonth)) {
            throw new \Exception('WeekOfMonth out of range. Range: 1~5. Input value: ' . $weekOfMonth);
        }

        $prefix = '/REPEATED/ON';
        if ($period == ActivityPeriodType::DAILY) {
            return $prefix . ActivityPeriod::DAILY_SEARCH_PATTERN;
        }

        if ($period == ActivityPeriodType::WEEKLY) {
            return $prefix . ActivityPeriod::WEEKLY_SEARCH_PATTERN . $dayOfWeek;
        }

        if ($period == ActivityPeriodType::MONTHLY) {
            return $prefix . ActivityPeriod::MONTHLY_SEARCH_PATTERN . $weekOfMonth . ActivityPeriod::WEEKLY_SEARCH_PATTERN . $dayOfWeek;
        }

        throw new \Exception('Get unknown ActivityPeriodType when ActivityManager trying to get ActivityPeriodRuleString. Input type: ' . $period);
    }
}
