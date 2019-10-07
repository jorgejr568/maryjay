<?php

namespace App\Jobs;

use App\Dashboard;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessDashboard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * Create a new job instance.
     *
     * @param Dashboard $dashboard
     */
    public function __construct(Dashboard $dashboard)
    {
        //
        $this->dashboard = $dashboard;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->dashboard->process();
    }
}
