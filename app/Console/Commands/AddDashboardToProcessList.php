<?php

namespace App\Console\Commands;

use App\Dashboard;
use Illuminate\Console\Command;

class AddDashboardToProcessList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboards:add_to_list';

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
        Dashboard::select('id')->update(['to_process' => true]);
    }
}
