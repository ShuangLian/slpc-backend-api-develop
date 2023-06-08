<?php

namespace App\Jobs;

use App\Managers\ActivityManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateActivitiesByActivityPeriodJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $activityPreiod;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($activityPeriod)
    {
        $this->activityPreiod = $activityPeriod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ActivityManager::createActivitiesByActivityPeriod($this->activityPreiod);
    }
}
