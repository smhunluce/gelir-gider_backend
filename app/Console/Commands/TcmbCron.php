<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TcmbCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcmb:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get exchange rates for TRY, USD, EUR from TCMB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        return 1;
    }
}
