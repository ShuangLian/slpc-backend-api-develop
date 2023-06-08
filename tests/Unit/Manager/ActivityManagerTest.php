<?php

namespace Tests\Unit\Manager;

use App\Managers\ActivityManager;
use App\Models\ActivityPeriod;
use App\Models\ActivityPeriodType;
use App\Utils\DateTimeUtil;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ActivityManagerTest extends TestCase
{
    public function testGetCorrectConfig()
    {
        // #1
        $s = '/REPEATED/ON/WEEK/1';

        $config = ActivityManager::getActivityPeriodRuleByType($s);

        $this->assertEquals(ActivityPeriodType::WEEKLY, $config['type']);
        $this->assertEquals(1, $config['dayOfWeek']);

        // #2
        $s = '/REPEATED/ON/WEEK_NUMBER/1/WEEK/4';

        $config = ActivityManager::getActivityPeriodRuleByType($s);

        $this->assertEquals(ActivityPeriodType::MONTHLY, $config['type']);
        $this->assertEquals(1, $config['weekOfMonth']);
        $this->assertEquals(4, $config['dayOfWeek']);

        // #3
        $s = '/REPEATED/ON/DAILY';

        $config = ActivityManager::getActivityPeriodRuleByType($s);

        $this->assertEquals(ActivityPeriodType::DAILY, $config['type']);

        // #4
        $invalidString = '/REPEATED/ON/WEEK_NUMBER/1/WEEKS/4';

        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('Get Unknown Type when trying to get Activity Period Type. Input rule: ' . $invalidString, $exception->getMessage());
        }

        // #5
        $invalidString = '/ERROR/INPUT/44/STRING';

        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('Get Unknown Type when trying to get Activity Period Type. Input rule: ' . $invalidString, $exception->getMessage());
        }

        // #6
        $invalidString = '/REPEATED/ON/DAILY/1';

        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('Get Unknown Type when trying to get Activity Period Type. Input rule: ' . $invalidString, $exception->getMessage());
        }

        // #7
        $invalidString = '/REPEATED/ON/WEEK/9';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse DayOfWeek get error value. Input String: ' . $invalidString, $exception->getMessage());
        }

        // #8
        $invalidString = '/REPEATED/ON/WEEK_NUMBER/8/WEEK/4';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse WeekOfMonth get error value. Input String: ' . $invalidString, $exception->getMessage());
        }

        // #9
        $invalidString = '/REPEATED/ON/WEEK_NUMBER/4/WEEK/7';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse DayOfWeek get error value. Input String: ' . $invalidString, $exception->getMessage());
        }

        // #10
        $invalidString = '/REPEATED/ON/WEEK/error_value';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse DayOfWeek get error value. Input String: ' . $invalidString, $exception->getMessage());
        }

        // #11
        $invalidString = '/REPEATED/ON/WEEK_NUMBER/error_value/WEEK/4';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse WeekOfMonth get error value. Input String: ' . $invalidString, $exception->getMessage());
        }

        // #12
        $invalidString = '/REPEATED/ON/WEEK_NUMBER/4/WEEK/error_value';
        try {
            $config = ActivityManager::getActivityPeriodRuleByType($invalidString);
        } catch (\Exception $exception) {
            $this->assertEquals('ActivityManager parse DayOfWeek get error value. Input String: ' . $invalidString, $exception->getMessage());
        }
    }

    public function testIsCreateDates()
    {
        // #1
        $startDate = Carbon::parse('2022-05-16');
        $endDate = Carbon::parse('2022-05-29');
        $datePeriod = DateTimeUtil::getDatePeriod($startDate, $endDate);
        $activityPeriod = new ActivityPeriod();
        $activityPeriod['rule'] = '/REPEATED/ON/WEEK/1';
        $activityPeriod['from_date'] = Carbon::parse('2022-05-01');
        $activityPeriod['to_date'] = Carbon::parse('2022-05-31');

        $dates = ActivityManager::getShouldCreateDatesByRule($activityPeriod, $datePeriod, collect());
        $this->assertEquals('2022-05-16', $dates[0]);
        $this->assertEquals('2022-05-23', $dates[1]);

        // #2
        $startDate = Carbon::parse('2022-05-16');
        $endDate = Carbon::parse('2022-05-29');
        $datePeriod = DateTimeUtil::getDatePeriod($startDate, $endDate);
        $activityPeriod = new ActivityPeriod();
        $activityPeriod['rule'] = '/REPEATED/ON/WEEK/1';
        $activityPeriod['from_date'] = Carbon::now()->startOfMonth()->toDateString();
        $activityPeriod['to_date'] = Carbon::now()->endOfMonth()->toDateString();

        $dates = ActivityManager::getShouldCreateDatesByRule($activityPeriod, $datePeriod, Collection::make(['2022-05-16', '2022-05-23']));
        $this->assertEmpty($dates);

        // #3
        try {
            $dates = ActivityManager::getShouldCreateDatesByRule(null, null, collect());
        } catch (\Exception $exception) {
            $this->assertEquals('Trying to access array offset on value of type null', $exception->getMessage());
        }
    }

    public function testGetActivityPeriodRuleString()
    {
        // #1 Daily Type
        $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString(ActivityPeriodType::DAILY, null, null);
        $this->assertEquals('/REPEATED/ON/DAILY', $activityPeriodRuleString);

        // #2 Weekly Type
        $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString(ActivityPeriodType::WEEKLY, 3, null);
        $this->assertEquals('/REPEATED/ON/WEEK/3', $activityPeriodRuleString);

        // #3 Monthly Type
        $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString(ActivityPeriodType::MONTHLY, 3, 2);
        $this->assertEquals('/REPEATED/ON/WEEK_NUMBER/2/WEEK/3', $activityPeriodRuleString);

        // #4 DayOfWeek Out Range ERROR
        try {
            $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString(ActivityPeriodType::WEEKLY, 8, null);
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'DayOfWeek out of range. Range: 0~6. Input value: 8');
        }

        // #5 WeekOfMonth Out Range ERROR
        try {
            $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString(ActivityPeriodType::MONTHLY, 2, 7);
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'WeekOfMonth out of range. Range: 1~5. Input value: 7');
        }

        // #6 Get unknown type ERROR
        try {
            $unknownType = 'type';
            $activityPeriodRuleString = ActivityManager::getActivityPeriodRuleString($unknownType, 2, 2);
        } catch (\Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Get unknown ActivityPeriodType when ActivityManager trying to get ActivityPeriodRuleString. Input type: ' . $unknownType);
        }
    }
}
