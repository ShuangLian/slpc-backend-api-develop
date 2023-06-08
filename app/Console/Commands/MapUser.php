<?php

namespace App\Console\Commands;

use App\Jobs\MapUserJob;
use Illuminate\Console\Command;

class MapUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new MapUserJob());
        return 0;
    }
}
