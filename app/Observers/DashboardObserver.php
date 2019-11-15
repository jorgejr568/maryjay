<?php

namespace App\Observers;

use App\Jobs\ProcessDashboard;
use App\Dashboard;
use Illuminate\Support\Facades\DB;

class DashboardObserver
{
    /**
     * Handle the search dashboard "created" event.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return void
     */
    public function created(Dashboard $searchDashboard)
    {
        $searchDashboard->update(['to_process' => true]);
    }

    /**
     * Handle the search dashboard "updated" event.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return void
     */
    public function updated(Dashboard $searchDashboard)
    {
        $searchDashboard->update(['to_process' => true]);
    }

    /**
     * Handle the search dashboard "deleted" event.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return void
     */
    public function deleted(Dashboard $searchDashboard)
    {
        DB::table('dashboard_tweets')->where('dashboard_id',$searchDashboard->id)->delete();

        unlink(
            storage_path('app/dashboards/'.$searchDashboard->id.".json")
        );
    }

    /**
     * Handle the search dashboard "restored" event.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return void
     */
    public function restored(Dashboard $searchDashboard)
    {
        ProcessDashboard::dispatch($searchDashboard);
    }

    /**
     * Handle the search dashboard "force deleted" event.
     *
     * @param  \App\Dashboard  $searchDashboard
     * @return void
     */
    public function forceDeleted(Dashboard $searchDashboard)
    {
        unlink(
            storage_path('app/dashboards/'.$searchDashboard->id.".json")
        );
    }
}
