<?php

namespace App\Utils;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\Log;

class DateTimeUtil
{
    public static function getDatePeriod($startDate, $endDate): DatePeriod
    {
        $interval = DateInterval::createFromDateString('1 day');

        return new DatePeriod($startDate, $interval, $endDate);
    }

    public static function parseRepublicEra($republicEraDateTime): Carbon
    {
        try {
            $numbers = [];
            preg_match_all('/\d*/', $republicEraDateTime, $numbers);
            $numbers = array_filter($numbers[0], fn ($value) => !empty($value));

            $year = $numbers[0] + 1911;
            $month = $numbers[2];
            $day = $numbers[4];

            $carbon = Carbon::parse($year . '-' . $month . '-' . $day);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            $carbon = Carbon::now();
        }

        return $carbon;
    }

    public static function getWeekStartDate(): string
    {
        $now = Carbon::now();

        return $now->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');
    }

    public static function getWeekEndDate(): string
    {
        $now = Carbon::now();

        return $now->endOfWeek(CarbonInterface::SATURDAY)->format('Y-m-d');
    }
}
