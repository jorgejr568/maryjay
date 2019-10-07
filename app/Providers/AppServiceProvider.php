<?php

namespace App\Providers;

use App\Observers\DashboardObserver;
use App\Dashboard;
use Codebird\Codebird;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Codebird::setConsumerKey(config('services.twitter.key'), config('services.twitter.secret'));
        Dashboard::observe(DashboardObserver::class);
    }
}
