<?php

namespace App\Console\Commands;

use App\Dashboard;
use Illuminate\Console\Command;

class ProcessDashboardsOnList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboards:process_list';

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
        $dashboards = Dashboard::where('to_process',true)->toSql();
        /** @var Dashboard $dashboard */
        foreach ($dashboards as $dashboard){
            $dashboard->update(['to_process' => false]);

            $dashboard->process();
        }
    }
}
