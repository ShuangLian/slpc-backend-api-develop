<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPeriodType extends Model
{
    use HasFactory;

    const DAILY = 'DAILY';
    const WEEKLY = 'WEEKLY';
    const MONTHLY = 'MONTHLY';

    /**
     * 根據 rule 來判斷週期是以下哪一種
     * 1. 在期間內每天都舉辦活動
     * 2. 在期間內，每週固定禮拜幾舉辦活動
     * 3. 在期間內，每月的第幾週的禮拜幾舉辦活動.
     * @param $ruleString
     * @return string
     * @throws \Exception
     */
    public static function getActivityPeriodTypeFromRule($ruleString): string
    {
        if (preg_match(ActivityPeriod::DAILY_REG_RULE, $ruleString)) {
            return self::DAILY;
        }

        if (preg_match(ActivityPeriod::MONTHLY_REG_RULE, $ruleString)) {
            return self::MONTHLY;
        }

        if (preg_match(ActivityPeriod::WEEKLY_REG_RULE, $ruleString)) {
            return self::WEEKLY;
        }

        throw new \Exception('Get Unknown Type when trying to get Activity Period Type. Input rule: ' . $ruleString);
    }
}
