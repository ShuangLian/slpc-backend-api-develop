<?php

namespace App\Console;

use App\Jobs\CreateActivitiesByActivityPeriodJob;
use App\Jobs\IntercessionJob;
use App\Jobs\MapUserJob;
use App\Models\ActivityPeriod;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $today = Carbon::now();
            $activityPeriods = ActivityPeriod::query()
                ->where('to_date', '>', $today)
                ->get();

            foreach ($activityPeriods as $activityPeriod) {
                dispatch(new CreateActivitiesByActivityPeriodJob($activityPeriod));
            }
        })
            ->dailyAt('00:01')
            ->name('dailyCheckActivityCreateOrNot')
            ->onOneServer();

        $schedule->job(new IntercessionJob())
            ->dailyAt('00:01')
            ->name('dailyCheckIntercessionStatus')
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
