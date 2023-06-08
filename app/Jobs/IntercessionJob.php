<?php

namespace App\Jobs;

use App\Models\Intercession;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IntercessionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Intercession::query()
            ->whereNot('status', Intercession::STATUS_DONE)
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereIn('card_type', [Intercession::GENERAL_CARD, Intercession::PRIVATE_CARD, Intercession::MINISTRY_CARD])
                        ->whereDate('apply_date', '<', Carbon::now()->subDays(Intercession::DAYS_90)->format('Y-m-d'));
                })
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('card_type', Intercession::EMERGENCY_CARD)
                            ->whereDate('apply_date', '<', Carbon::now()->subDays(Intercession::DAYS_30)->format('Y-m-d'));
                    });
            })
            ->update(['status' => Intercession::STATUS_DONE]);
    }
}
