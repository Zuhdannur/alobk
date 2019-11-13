<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class ExpiredScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateschedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
//        DB::table('schedule')
//            ->where('type_schedule','!=','daring')
//            ->where('expired', 0)
//            ->where('canceled', 0)
//            ->where('pending', 1)
//            ->where('finish', 0)
//            ->where('active', 0)
//            ->where('start', 0)
//            ->whereDate('time', '>', Carbon::now())
//            ->update(['expired' => 1]);
        DB::table('schedule')->update(['expired' => 1]);
    }
}
